<?php
namespace app\imdemo\model;
use think\Model;
use think\Db;


class User extends Model
{
    //注册
    public function register()
    {
        $username = input('post.username');
        $password = input('post.password');
        if(Db::name('user')->where('username', $username)->find()){
            return 'haveUsername';
        }
        return Db::name('user')->insertGetId(['username'=>$username, 'password'=>$password]);
    }

    //登录
    public function login()
    {
        $username = input('post.username');
        $password = input('post.password');
        $user = Db::name('user')->where('username', $username)->find();
        if($user){
            return $user.id;
        }else{
            return false;
        }
    }
}