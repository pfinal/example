<?php

//M model
//V view
//C controller
//单入口文件

include_once __DIR__ . '/autoload.php';

//$controller = new \Controller\UserController();
//$controller->index();

//$controller = new \Controller\UserController();
//$controller->create();

//$controller = new \Controller\ArticleController();
//$controller->index();

//$controller = new \Controller\ArticleController();
//$controller->create();

//类名不同
//方法名不同

//$c = 'Controller\UserController';
//$a = 'index';

$c = isset($_GET['c']) ? $_GET['c'] : 'User';
$c = 'Controller\\' . $c . 'Controller';
$a = isset($_GET['a']) ?: 'Index';

$controller = new $c();
$controller->$a();

// 明白MVC目录结构
// 单入口文件 其它PHP文件，不允许直接访问。找目录相对于入口文件去找 include './View/User/index.php';
// 明白URL与类和方法的对应关系
