<?php
//站内信  暂不使用
namespace app\api\controller;

use think\Controller;
use app\api\model\SiteMessage as SiteMessageModel;
use think\Db;

class SiteMessage extends Controller
{
    protected $allow_origin = array(  
       
    );

    public function recentList(){
        $origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';
        if(in_array($origin, $this->allow_origin)){  
            header('Access-Control-Allow-Origin:'.$origin);       
        }
        header('Access-Control-Allow-Origin:*');          
        $siteMessageModel = new SiteMessageModel();
        $uid = input('param.uid');
        return json($siteMessageModel->recentList($uid), 200);
    }

    public function read(){
        $origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';
        if(in_array($origin, $this->allow_origin)){  
            header('Access-Control-Allow-Origin:'.$origin);       
        }
        header('Access-Control-Allow-Origin:*');          
        $id = input('param.id');
        $siteMessageModel = new SiteMessageModel();
        return json($siteMessageModel->read($id), 200);
    }

    public function del(){
        $origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';
        if(in_array($origin, $this->allow_origin)){  
            header('Access-Control-Allow-Origin:'.$origin);       
        }
        header('Access-Control-Allow-Origin:*');  
        $id = input('param.id');
        $siteMessageModel = new SiteMessageModel();
        return json($siteMessageModel->del($id), 200);
    }

    //向所有用户发送站内信
    public function sendAll(){
        $origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';
        if(in_array($origin, $this->allow_origin)){  
            header('Access-Control-Allow-Origin:'.$origin);       
        }
        header('Access-Control-Allow-Origin:*');          
        $siteMessageModel = new SiteMessageModel();
        $uidList = input('param.uidList');
        $title = input('param.title');
        $content = input('param.content');
        $siteMessageModel->sendAll($title, $content, $uidList);
    }


}