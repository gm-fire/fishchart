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
        $userList = $messageModel->getRecentConnectUserList($uid); //读取用户1的最近联系人列表
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