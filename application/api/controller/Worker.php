<?php
namespace app\api\controller;

use think\worker\Server;
use Workerman\Lib\Timer;

use app\push\model\Message;

define('HEARTBEAT_TIME', 55); //心跳55秒

class Worker extends Server
{
protected $socket = SERVER_SOCKET;
protected $count = 1;   // ====这里进程数必须必须必须设置为1===
protected $uidConnections = array(); //新增加一个属性，用来保存uid到connection的映射(uid是用户id或者客户端唯一标识)

//初始化
public function _initialize(){

}

/**
* 收到信息
* @param $connection
* @param $data
*/
public function onMessage($connection, $dataJ)
{
    $data = json_decode($dataJ, true);
    $code = $data['code'];  //消息类型：bind-登录，绑定连接，msg-发送消息, read-已读消息, close-下线释放连接
    var_dump($data);
    $connection->lastMessageTime = time();// 给connection临时设置一个lastMessageTime属性，用来记录上次收到消息的时间
    $firstMessageId = isset($data['firstMessageId']) ? $data['firstMessageId'] : 1;   //最早一条消息的id
    $fromUserId = isset($data['fromUser']) ? $data['fromUser'] : null;  //发送者
    $toUserId = isset($data['toUser']) ? $data['toUser'] : null;    //接收者
    $groupId = isset($data['groupId']) ? $data['groupId'] : null;   //群

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
            $message = $data['msg'];
            $type = $data['type'];
            $this->sendMessageByUid($fromUserId, $toUserId, $message, $type);
            break;
        case 'group': //群聊信息
            $uidList = $data['uidList'];
            $message = $data['msg'];
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
            $msgid = $data['msgid'];//信息id
            $this->receiveMessage($msgid);
            break ;
        case 'beat': //心跳,保持连接用
            break;
        default:
            return $connection->send(json_encode(['message' => '未知类型消息']));
        }
}

// 向所有验证的用户推送数据(暂未使用)
function broadcast($message)
{
    foreach($this->uidConnections as $connection)
    {   
        $data['msgid'] =  0;
        $data['message'] = $message;
        $connection->send(json_encode($data));
    }
}

//针对uid推送数据
//type:信息类型 :text-文本消息（默认）,pic-图片，voice-语音,info-透传信息（不显示的系统消息，如对方删除好友）,sys-系统透传消息
function sendMessageByUid($fromUserId, $uid, $message, $type="text", $fromGroupId = null)
{
    $messageModel = new Message();
    $msgid = $messageModel->saveMessage($fromUserId, $uid, $message, $type, $fromGroupId);//向数据库保存聊天记录    
    if($uid > 0 && isset($this->uidConnections[$uid]))  //如果接收用户当前在线
    {
        $connection = $this->uidConnections[$uid];
        $data['msgid'] =  $msgid;
        $data['message'] = $message;
        $data['type'] =  $type;
        $data['fromGroupId'] = $fromGroupId;
        $connection->send(json_encode($data));
    }
}

// 发送群组消息，针对UidList循环发送
function sendMessageByUidList($fromUserId, $uidList, $message, $type="text",  $fromGroupId = null)
{
    //dump($uidList);
    $uidList = explode(',', $uidList);
    foreach($uidList as $uid){
        $this->sendMessageByUid($fromUserId, $uid, $message, $type,  $fromGroupId);
    }
}

//修改数据接收状态
function receiveMessage($msgid){
    $messageModel = new Message();
    $messageModel->receiveMessage($msgid);
}

//获取未读历史消息
function getUnreadMessage($fromUserId, $toUserId , $groupId = null, $time = 1){
    $messageModel = new Message();
    return $messageModel->getUnreadMessage($toUserId, $fromUserId, $groupId, $time);
}

//获取当前用户历史消息
function getHistoryMessage($fromUserId, $toUserId , $groupId = null, $firstMessageId = 1){
    $messageModel = new Message();
    //echo $firstMessageId;
    return $messageModel->getHistoryMessage($toUserId, $fromUserId, $groupId, $firstMessageId);
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
    $connection->send(json_encode(['code'=>'info', 'message' => "连接建立"]));
    //$connection->send('{"message":"[连接建立]"}');
}
/**
* 当连接断开时触发的回调函数
* @param $connection
*/
public function onClose($connection)
{
    $connection->send(json_encode(['code'=>'info', 'message' => "服务器断开"]));
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