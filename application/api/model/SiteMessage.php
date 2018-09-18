<?php
namespace app\api\model;
use think\Model;
use think\Db;

//主要用于消息保存和离线消息的处理
class SiteMessage extends Model
{
    //$user_id:用户id
    //$is_read:已读状态
    //$page:分页
    public function recentList($user_id, $is_read = [0, 1], $page=15){
        $messageList = Db::name('site_message')
                        ->where('user_id', $user_id)
                        ->where('state', 0) //正常
                        ->where('is_read', 'in', $is_read)
                        ->order('send_time')
                        ->paginate($page);
        //dump(Db::name('site_message')->getLastSql());
        return $messageList;
    }

    //发送站内信
    //$userIdList 用户列表
    public function sendAll($title, $content, $userIdList=[], $adminId = 0){
        $data = [];
        for($i = 0 ; $i < count($userIdList); $i++){
            $data[$i]['title'] =   $title;      
            $data[$i]['content'] = $content;
            $data[$i]['send_time'] = date('Y-m-d H:i:s');
            $data[$i]['admin_id'] = $adminId;
            $data[$i]['state'] = 0;
            $data[$i]['user_id'] = $uid;
        }
        Db::name('site_message')->insertAll($data);
    }

    //读取并设置站内信已读状态
    public function read($id, $is_read=1){
        Db::name('site_message')->where('id',$id)->setField('is_read', $is_read);
        return Db::name('site_message')->where('id',$id)->find();
    }

    //设置站内信删除状态
    public function del($id, $state=1){
        return Db::name('site_message')->where('id',$id)->setField('state', $state);
    }
}