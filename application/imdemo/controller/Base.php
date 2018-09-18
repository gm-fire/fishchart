<?php
namespace app\imdemo\controller;
use \think\Controller;

class Base extends Controller
{
    protected $request = null;
    protected $allowActionList = ['User/register', 'User/login', 'User/logout', 'Index/index'];
    public function __initialize()
    {
        $request = Request::instance();
        
        $controller = $request->controller();
        $action = $request->action();
        $pathStr = "$controller/$action";
        if(!in_array($pathStr, $allowActionList )){
            if(session('userid')){

            }else{
                $this->error('登录过期', url('imdemo/User/login'));
            }
        }
    }

    
}
