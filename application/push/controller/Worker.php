<?php
namespace app\push\controller;

use think\worker\Server;
use Workerman\Lib\Timer;
use app\push\model\Message;

define('HEARTBEAT_TIME', 55); //心跳55秒

class Worker extends Server
{
protected $count = 1;   // ====这里进程数必须必须必须设置为1===
protected $uidConnections = array(); //新增加一个属性，用来保存uid到connection的映射(uid是用户id或者客户端唯一标识)

/**构造函数
 */
public function __construct(){
    $this->socket = SERVER_SOCKET;
    parent::__construct();
    //$this->protocol = "wss";  //这三项为了兼容微信小程序，一般不需要使用
    //$this->host = "0.0.0.0";
    //$this->port = "443";
}

/**
 * 收到信息，请求类型分发，自定义类型请修改code
 * @param  $connection 连接
 * @param  $dataJ json格式的字符串，前端可使用JSON.stringify()转换
 */
public function onMessage($connection, $dataJ)
{
    $data = json_decode($dataJ, true);
    $code = $data['code'];  //消息类型：bind-登录，绑定连接，msg-发送消息, read-已读消息, close-下线释放连接
    echo"onMessage():\r\n"; var_dump($data);
    $connection->lastMessageTime = time();// 给connection临时设置一个lastMessageTime属性，用来记录上次收到消息的时间
    $firstMessageId = isset($data['firstMessageId']) ? $data['firstMessageId'] : 0;   //最早一条消息的id
    $fromUserId = isset($data['fromUser']) ? $data['fromUser'] : null;  //发送者
    $toUserId = isset($data['toUser']) ? $data['toUser'] : null;    //接收者
    $groupId = isset($data['groupId']) ? $data['groupId'] : null;   //群
    $uidList = isset($data['uidList']) ? $data['uidList'] : null;   //群聊用户id列表;
    $groupIdList = isset($data['groupIdList']) ? $data['groupIdList'] : null;   //群id列表
    $msgid = isset($data['msgid']) ? $data['msgid'] : 0;   //消息id 已读消息接口用到

    // 判断当前客户端是否已经验证,即是否设置了uid
    switch($code){
        case 'bind':
            if(!isset($connection->uid)){
                /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
                * 实现针对特定uid推送数据
                */
                $connection->uid = $fromUserId;
                $this->uidConnections[$connection->uid] = $connection;
                $connection->send(json_encode(['code'=>'msg', 'type'=>'info', 'content' => '用户： '.$connection->uid.'连接建立']));
                $historyMessageList = $this->getHistoryMessage($fromUserId, $toUserId, $groupId, $firstMessageId);   //发送历史消息
                $data['code'] = 'msgList';
                $data['messageList'] = $historyMessageList;
                $connection->send(json_encode($data));
            }
            break;
        case 'msg': //单聊信息
            $message = $data['content'];
            $type = $data['type'];
            $this->sendMessageByUid($fromUserId, $toUserId, $message, $type);
            break;
        case 'group': //群聊信息
            $message = $data['content'];
            $type = $data['type'];
            $groupId = $data['groupId'];
            $this->sendMessageByUidList($fromUserId, $uidList, $message, $type, $groupId);
            break;
        case 'moreHistory': //读取更多历史消息
            $historyMessageList = $this->getHistoryMessage($fromUserId, $toUserId, $groupId, $firstMessageId); 
            $data['code'] = 'msgList';
            $data['messageList'] = $historyMessageList;
            $connection->send(json_encode($data));
            break;
        case 'read':    //读取信息反馈
            //$msgid ;//信息id
            $this->receiveMessage($msgid);
            break ;
        case 'unreadcount'://读取用户未读消息
            //$connection = $this->uidConnections[$fromUserId];
            $messageModel = new Message();
            if(isset($data['type'])){   //people / group
                $type = $data['type'];
                if($type == 'people'){
                    $res = $messageModel->unReadCountList($fromUserId,  $uidList);
                    return $connection->send(json_encode(['code'=>'info','type'=>'peopleUnreadCount','content' => $res]));
                }
                if($type == 'group'){
                    $res = $messageModel->groupUnReadCount($fromUserId,  $groupIdList);
                    return $connection->send(json_encode(['code'=>'info','type'=>'groupUnreadCount','content' => $res]));
                }
                return $connection->send(json_encode(['message' => '未读统计类型错误，仅限people / group']));
            }
            
            break ;
        case 'beat': //心跳,保持连接用
            break;
        default:
            return $connection->send(json_encode(['message' => '未知类型消息']));
        }
}

/** 向所有验证的用户推送数据(暂未使用)
 * @param $message 消息
 */
function broadcast($message)
{
    foreach($this->uidConnections as $connection)
    {   
        $data['msgid'] =  0;
        $data['message'] = $message;
        $connection->send(json_encode($data));
    }
}

/** 针对uid推送数据
 * @param  $fromUserId:发送者id
 * @param  $uid:接受者id
 * @param  $message:发送的信息，json格式字符串
 * @param  $type:发送的信息类型，text-文本消息（默认）,pic-图片，voice-语音,info-透传信息（不显示的系统消息，如对方删除好友）,sys-系统透传消息
 * @param  $fromGroupId:若是群聊消息，则需要传群id
 */
function sendMessageByUid($fromUserId, $uid, $message, $type="text", $fromGroupId = null)
{
    $messageModel = new Message();
    $msgid = $messageModel->saveMessage($fromUserId, $uid, $message, $type, $fromGroupId);//向数据库保存聊天记录    
    if($uid > 0 && isset($this->uidConnections[$uid]) && $fromUserId != $uid)  //如果接收用户当前在线
    {
        $connection = $this->uidConnections[$uid];
        $data['code'] =  'msg';
        $data['msgid'] =  $msgid;
        $data['content'] = $message;
        $data['type'] =  $type;
        $data['from_user_id'] = $fromUserId;
        $data['from_group_id'] = $fromGroupId;
        $connection->send(json_encode($data));
    }
}

/** 发送群组消息，针对UidList循环发送
 * @param  $fromUserId:发送者id
 * @param  $uidList:群聊中所有其他用户的id列表字串, 如："1,2,3,4,5"
 * @param  $message:发送的信息，json格式字符串
 * @param  $type:发送的信息类型，text-文本消息（默认）,pic-图片，voice-语音,info-透传信息（不显示的系统消息，如对方删除好友）,sys-系统透传消息
 * @param  $fromGroupId:若是群聊消息，则需要传群id
 */
function sendMessageByUidList($fromUserId, $uidList, $message, $type="text",  $fromGroupId = null)
{
    $uidList = explode(',', $uidList);
    foreach($uidList as $uid){
        //if($uid != $fromUserId){
            $this->sendMessageByUid($fromUserId, $uid, $message, $type,  $fromGroupId);
        //}
    }
}

/** 修改数据接收状态
 * @param  $msgid:消息id
 */
function receiveMessage($msgid){
    $messageModel = new Message();
    $messageModel->receiveMessage($msgid);
}

/** 获取未读历史消息
 * @param  $fromUserId:发送者id
 * @param  $toUserId:接收者id
 * @param  $groupId:群id,如果是群聊则需要传此参数
 * @param  $time:时间限制，默认最近一个月
 */
function getUnreadMessage($fromUserId, $toUserId , $groupId = null, $time = 1){
    $messageModel = new Message();
    return $messageModel->getUnreadMessage($toUserId, $fromUserId, $groupId, $time);
}

/** 获取当前用户历史消息
 * @param  $fromUserId:发送者id
 * @param  $toUserId:接收者id
 * @param  $groupId:群id,如果是群聊则需要传此参数
 * @param  $firstMessageId 最近一条消息的id 
 */
function getHistoryMessage($fromUserId, $toUserId , $groupId = null, $firstMessageId = 1){
    $messageModel = new Message();
    return $messageModel->getHistoryMessage($fromUserId, $toUserId, $groupId, $firstMessageId);
}

//设置当前分页（弃用）
public function setPage($page, $connection){
    $connection->send(json_encode(['code'=>'setPage', 'page'=>$page]));
}


/**
* 当连接建立时触发的回调函数
* @param $connection
*/
public function onConnect($connection)
{
    $connection->send(json_encode(['type'=>'msg', 'code'=>'info', 'content' => "连接建立"]));
}
/**
* 当连接断开时触发的回调函数
* @param $connection
*/
public function onClose($connection)
{
    $connection->send(json_encode(['type'=>'msg', 'code'=>'info', 'content' => "服务器断开"]));
    if(isset($connection->uid))
    {
        // 连接断开时删除映射
        unset($this->uidConnections[$connection->uid]);
    }
}
/**
* 当客户端的连接上发生错误时触发
* @param $connection
* @param $code
* @param $msg
*/
public function onError($connection, $code, $msg)
{
    echo "error $code $msg\n";
}

/**
* 每个进程启动
* @param $worker
*/
public function onWorkerStart($worker)
{
    Timer::add(1, function()use($worker){
        $time_now = time();
        foreach($worker->connections as $connection) {
            // 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
            if (empty($connection->lastMessageTime)) {
                $connection->lastMessageTime = $time_now;
                continue;
            }
            // 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
            if ($time_now - $connection->lastMessageTime > HEARTBEAT_TIME) {
                $connection->close();
            }
        }
    });
}



//
}