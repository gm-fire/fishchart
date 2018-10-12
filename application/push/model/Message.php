<?php
namespace app\push\model;
use think\Model;
use think\Db;
use think\Request;

//主要用于消息保存和离线消息的处理
class Message extends Model
{
    //读一个月内的未读信息
    public function getUnreadMessage($fromUserId, $toUserId, $groupId=null, $time = 1){
        $timeAfter = date('Y-m-d', strtotime('-'.$time.' month')); 
        if($groupId == null){//单聊消息
            $messageList = Db::name('chart')->where('from_user_id', $fromUserId)
                ->where('to_user_id', $toUserId)
                ->where('send_time', 'gt', $timeAfter)                        
                ->where('is_receive', 0)    //读取未发送
                ->order('id desc')
                ->select();
            Db::name('chart')->where('from_user_id', $fromUserId)//变更未发送的信息状态
                        ->where('to_user_id', $toUserId)
                        ->where('is_receive', 0)   
                        ->setField('is_receive', 1);
            
        }else{  //群组消息
            $messageList = Db::name('chart')->where('group_id', $groupId)
                ->where('to_user_id', $toUserId)
                ->where('send_time', 'gt', $timeAfter)                        
                ->where('is_receive', 0)    //读取未发送
                ->order('id desc')
                ->select();
            
            Db::name('chart')->where('group_id', $groupId)  //变更未发送的信息状态
                        ->where('to_user_id', $toUserId)
                        ->where('is_receive', 0)
                        ->setField('is_receive', 1);
        }
        return $messageList;
    }



    //读取历史消息,默认最近50条
    public function getHistoryMessage($fromUserId, $toUserId, $groupId=null, $firstMessageId = 0, $limit = 50){
        
        if($groupId == null){//单聊消息
            if($firstMessageId == 0){ //第一次通信，获取最大id
                $firstMessageId = Db::name('chart')->where($groupId, null)
                ->where(function($query) use($fromUserId, $toUserId)  {
                    $query->where(['from_user_id'=>$fromUserId, 'to_user_id'=> $toUserId]);
                })->whereOr(function($query) use($fromUserId, $toUserId) {
                    $query->where(['from_user_id'=>$toUserId, 'to_user_id'=> $fromUserId]);
                })->max('id');
                $firstMessageId += 1;//比最后一次通信id大        
               // echo "firstMessageId:$firstMessageId\r\n"   ;  
                       
            }

            $messageList =  Db::name('chart')
                            ->where(function($query) use($fromUserId, $toUserId, $firstMessageId)  {
                                $query->where(['from_user_id'=>$fromUserId, 'to_user_id'=> $toUserId])
                                    ->where('id','lt', $firstMessageId)->where('group_id', null);
                            })->whereOr(function($query) use($fromUserId, $toUserId, $firstMessageId) {
                                $query->where(['from_user_id'=>$toUserId, 'to_user_id'=> $fromUserId])
                                    ->where('id','lt', $firstMessageId)->where('group_id', null);
                            })
                        ->order('id desc')
                        ->limit($limit)
                        ->select();
            echo Db::name('chart')->getLastSql();
        }else{  //群组消息
            //dump('读取群组历史消息');

            if($firstMessageId == 0){ //第一次通信，获取最大id
                $firstMessageId = Db::name('chart')->where(function($query) use($groupId, $toUserId)  {
                    $query->where(['group_id'=>$groupId, 'from_user_id'=> $toUserId, 'to_user_id'=> $toUserId]);
                })->whereOr(function($query) use($groupId, $toUserId) {
                    $query->where(['group_id'=>$groupId, 'to_user_id'=>$toUserId]);
                })->max('id');
                $firstMessageId += 1;//比最后一次通信id大        
                echo "firstMessageId:$firstMessageId\r\n"   ;                        
            }
            echo Db::name('chart')->getLastSql();

            $messageList = Db::name('chart')
                ->where(function($query) use($groupId, $toUserId, $firstMessageId)  {
                    $query->where(['group_id'=>$groupId, 'to_user_id'=> $toUserId])
                        ->where('id','lt', $firstMessageId);
                })->whereOr(function($query) use($groupId, $toUserId, $firstMessageId) {
                    $query->where(['group_id'=>$groupId, 'from_user_id'=> $toUserId, 'to_user_id'=> $toUserId])
                        ->where('id','lt', $firstMessageId);
                })
            ->order('id desc')
            ->limit($limit)
            ->select();
            echo Db::name('chart')->getLastSql();
        }
        return $messageList;
    }

   
    //保存消息
    public function saveMessage($fromUserId, $toUserId, $message, $type="text", $groupId = null, $isRead = 0){
        $data['from_user_id'] = $fromUserId;
        $data['to_user_id'] = $toUserId;
        $data['content'] = $message;
        $data['send_time'] = date('Y-m-d H:i:s');
        $data['is_receive'] = $isRead;
        $data['type'] = $type;
        if($groupId != null){
            $data['group_id'] = $groupId;
        }
        $res = Db::name('chart')->insertGetId($data);
        return $res;
    }

    //修改已读消息
    public function receiveMessage($id, $isRead = 1){
        return Db::name('chart')->where('id', $id)->setField('is_receive', $isRead);//变更信息状态
    }

    //获取有未读消息的用户列表
    //$uid:用户id
    //$time:时间限制，如一个月内
    public function getHaveUnreadUserList($uid, $time=1){
        $timeAfter = date('Y-m-d', strtotime('-'.$time.' month'));
        $unreadMessageList = Db::name('chart')->field('from_user_id')
                            ->where('to_user_id', $uid)
                            ->where('is_receive', 0)
                            ->where('send_time', 'gt', $timeAfter)
                            ->group('from_user_id')->order('send_time desc')->select();
        return $unreadMessageList ;
    }

    //获取最近联系人列表
    //$uid:用户id
    //$time:时间限制，如一个月内
    //$uidNotIn 不在此列表内的uid
    public function getRecentConnectUserList($uid, $time=1, $uidNotIn=[]){
        $timeAfter = date('Y-m-d', strtotime('-'.$time.' month'));
        $recentConnectUserList = Db::name('chart')->field('from_user_id')
                            ->where('to_user_id', $uid)
                            ->where('from_user_id', 'not in', $uidNotIn)
                            ->where('send_time', 'gt', $timeAfter)
                            ->group('from_user_id')->order('send_time desc')->select();
        echo Db::name('chart')->getLastSql();
        return $recentConnectUserList ;
    }

    //当前联系人总共有多少条未读信息
    public function unReadCount($uid, $time=1){
        $timeAfter = date('Y-m-d', strtotime('-'.$time.' month'));
        $unReadCount = Db::name('chart')->field('id')
                            ->where('to_user_id', $uid)
                            //->where('send_time', 'gt', $timeAfter)
                            ->where('is_receive', 0)//读取未发送
                            ->count();
        return $unReadCount ;
    }

    //当前联系人列表未读消息数
    //$uid:当前用户
    // $userList 好友id列表 ‘-’分隔
    //返回[[用户id=>未读数量]]
    public function unReadCountList($uid, $userList){
        //$userList = explode('-',$userList);
        $unReadCountList = [];//[[uid,count]]
        foreach( $userList as $fuid){
            $unReadCountList[$fuid] = Db::name('chart')->field('id')
                ->where('to_user_id', $uid)
                ->where('from_user_id', $fuid)
                //->where('send_time', 'gt', $timeAfter)
                ->where('is_receive', 0)//读取未发送
                ->count();
        }
        return $unReadCountList;
    }

    //当前群组列表未读消息数
    //$uid:当前用户
    // $userList 好友id列表 ‘-’分隔
    //返回[[用户id=>未读数量]]
    public function groupUnReadCount($uid, $groupIdList=[]){
        $unReadCountList = [];//[[uid,count]]
        foreach( $groupIdList as $gid){
            $unReadCountList[$gid] = Db::name('chart')->field('id')
            ->where('to_user_id', $uid)
            ->where('group_id', $gid)
            //->where('send_time', 'gt', $timeAfter)
            ->where('is_receive', 0)//读取未发送
            ->count();
        }
        return $unReadCountList;
    }

    //当前联系人列表群未读消息数  废弃
    //$uid:当前用户
    // $userGroupList 群id列表  [[群id=>[用户id1,用户id2,.....]]]
    //返回[[用户id=>未读数量]]
    public function groupUnReadCountList($uid, $userGroupList){
        $userGroupList = explode('-',input('param.userGroupList'));
        $groupUnReadCountList = [];//[[uid,count]]
        foreach( $userGroupList as $gid=>$uGroup){
            $tempCount = 0;
            $uGroup =  explode('#',input('param.userGroupList'));
            foreach($uGroup as $fuid){
                $tempCount  += Db::name('chart')->field('id')
                ->where('to_user_id', $uid)
                ->where('from_user_id', $fuid)
                ->where('is_receive', 0)//读取未发送
                ->count();
            }
            $groupUnReadCountList[$gid] = $tempCount;
        }
        return $groupUnReadCountList;
    }
}