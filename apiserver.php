#!/usr/bin/env php
<?php
//php apiserver.php  运行
define('APP_PATH', __DIR__ . '/application/');
define('BIND_MODULE','api/Worker');


define('SERVER_SOCKET','websocket://127.0.0.1:2346'); //服务器
// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';