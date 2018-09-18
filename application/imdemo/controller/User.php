<?php
namespace app\imdemo\controller;
use app\imdemo\User as UserModel;

class User extends Base
{

    public function register()
    {
        if($this->request->isPost()){
            $userModel = new UserModel();
            $flag = $userModel->login();
            if($flag == 'haveUsername'){
                $this->error('用户已存在');
            }else{
                session('userid', $flag);
                $this->success('注册成功', 'User/chart');
            }
        }else{
            return $this->fatch();
        }
    }

    public function login()
    {
        if($this->request->isPost()){
            $userModel = new UserModel();
            $flag = $userModel->login();
            if($flag == 'haveUsername'){
                $this->error('用户已存在');
            }else{
                session('userid', $flag);
                $this->success('登录成功', 'User/chart');
            }
        }else{
            return $this->fatch();
        }
    }

    public function logout()
    {
        if($this->request->isPost()){
            session('userid', null);
        }else{
            return $this->fatch();
        }
    }

    //聊天
    public function chart()
    {
        return $this->fatch();
    }

    //群聊
    public function groupChart()
    {
        return $this->fatch();
    }

    //添加好友
    public function addFriend()
    {

    }

    //添加好友入群
    public function addFriendInGroup()
    {

    }

    //新建群聊
    public function addGroup()
    {

    }

    //同意入群
    public function inGroup()
    {

    }

    //退群
    public function outGroup()
    {

    }


}
