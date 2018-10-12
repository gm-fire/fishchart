<?php
namespace app\push\controller;

use think\Controller;
use app\push\model\Message as MessageModel;
use think\Db;

class Message extends Controller
{
    public function index(){
        return $this->fetch();
    }

    //最近联系人列表
    public function recentList(){
        $messageModel = new MessageModel();
        $uid = input('param.uid');
        $userList = $messageModel->getRecentConnectUserList($uid); //读取用户的最近联系人列表
        $this->assign('userList', $userList);
        return $this->fetch();
    }

    //聊天
    public function chart(){
        return $this->fetch();
    }

    //群组聊天
    public function groupChart(){
        return $this->fetch();
    }

    //获取用户列表未读消息数量
    public function userUnreadCount(){
        $messageModel = new MessageModel();
        $uid = input('param.uid');
        $userlist = explode('-', input('param.userlist'));
        $res = $messageModel->unReadCountList($uid, $userlist);
        echo json_encode($res);
    }

    //
    public function groupUnReadCountList(){
        $messageModel = new MessageModel();
        $uid = input('param.uid');
        $gid = input('param.gid');
        $userlist = explode('-', input('param.userlist'));
        echo $messageModel->groupUnReadCount($uid, $gid, $userlist);
    }

    //just test
    public function test(){
        $uid = 1;
        $fromUserId = 1;
        $toUserId = 2;
        // $nowdate  =  date('Y-m-d');
        // $oneMonthBeforedate = date('Y-m-d', strtotime('-1 month'));
        $messageList = Db::name('chart')
                ->where(function($query) use($fromUserId,$toUserId)  {
                    $query->where(['from_user_id'=>$fromUserId, 'to_user_id'=> $toUserId]);
                })->whereOr(function($query) use($fromUserId,$toUserId) {
                    $query->where(['from_user_id'=>$toUserId, 'to_user_id'=> $fromUserId]);
                })->order('send_time')
                ->page(1, 1)
                ->select();
        dump(Db::name('chart')->getLastSql());
        dump($messageList);

    }
}