#!/usr/bin/env php
<?php
//启动：
//php wmserver.php start 运行
//php wmserver.php start -d 运行服务模式
define('APP_PATH', __DIR__ . '/application/');
define('BIND_MODULE','push/Worker');

define('SERVER_SOCKET','websocket://127.0.0.1:2346'); //服务器
//define('SERVER_SOCKET','websocket://0.0.0.0:2346'); //服务器

// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';