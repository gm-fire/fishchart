<?php
namespace app\api\controller;

use think\Controller;
use app\api\model\Message as MessageModel;
use think\Db;

class Message extends Controller
{
    //最近联系人列表
    //uid-用户id
    public function recentList(){
        $messageModel = new MessageModel();
        $uid = input('param.uid');
        $userUnReadList = $messageModel->getHaveUnreadUserList($uid); //读取用户1的最近有未读消息的联系人列表
        $userUnReadList = array();
        foreach($userUnReadList as $userUnread){
            $userUnReadList[] = $userUnread['from_user_id'];
        }
        $userRecentList = $messageModel->getRecentConnectUserList($uid, 1, $userUnReadList); //读取用户1的最近联系人列表
        $userList = array_merge($userUnReadList, $userRecentList);//合并数组

        
        for($i=0; $i< count($userList); $i++){  //追加未读信息数据
            $userList[$i]['unreadNum'] = $messageModel->unReadFromOneUser($userList[$i]['from_user_id'], $uid);
        }
        return json($userList, 200);
    }

    //未读消息数量
    //uid-用户id
    public function unReadCount(){
        $messageModel = new MessageModel();
        $uid = input('param.uid');
        return json($messageModel->unReadCount($uid), 200);
    }
}