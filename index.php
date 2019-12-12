<?php

// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');
define('APP_VERSION', '7.4.24');
define('APP_VERSION_TIME', '2017.5.13');
define('DATA_PATH', __DIR__ . '/data/');



//定义Public路径
define('SCRIPT_DIR', rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/\\'));

define('SERVER_NAME', $_SERVER['SERVER_NAME']);


// 检测PHP环境
if (version_compare(PHP_VERSION, '5.4.0', '<')) die('require PHP > 5.4.0 !');



// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';
