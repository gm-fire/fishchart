<?php
//上传文件处理
namespace app\push\controller;

use think\Controller;
use app\push\model\SiteMessage as SiteMessageModel;
use think\Db;

class File extends Controller
{
    function fileUpload()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $fileUploadPath = ROOT_PATH . 'public' . DS . 'uploads';
            $info = $file->move($fileUploadPath);
            if($info){
                // 成功上传后 获取上传信息
                // 输出 jpg
                //echo $info->getExtension();
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                //echo $info->getSaveName();
                // 输出 42a79759f284b767dfcb2a0197904287.jpg
                //echo $info->getFilename();
                echo '/uploads/'.$info->getSaveName();die;
            }else{
                // 上传失败获取错误信息
                echo $file->getError();die;
            }
        }
    }
}