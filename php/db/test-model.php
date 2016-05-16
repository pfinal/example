<?php

/*
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL DEFAULT '',
  `password` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` INT UNSIGNED DEFAULT 0 NOT NULL,
  `updated_at` INT UNSIGNED DEFAULT 0 NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=UTF8;
*/

require_once './Database/Connection.php';
require_once './Database/Model.php';


$dbConfig = array(
    'dsn' => 'mysql:host=localhost;dbname=test',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8',
    'tablePrefix' => '',
);

$model = new \Database\Model('user');


$model->delete();
for ($i = 1; $i <= 10; $i++) {
    $data = array(
        'id' => $i,
        'username' => 'jack' . $i,
        'password' => md5('111111'),
        'created_at' => time(),
        'updated_at' => time()
    );
    var_dump($model->insertGetId($data));
}
$model->delete(10);
var_dump($model->count());
var_dump($model->avg('id'));


//var_dump($model->where(['id'=>8])->delete());exit;

//$list = $model->where('id>?', array(1))->where(array('username' => 'jack'))->limit('1,3')->select();
//var_dump($list);

$list = $model->where('id>?', [4])->where(array('username' => array('jack1', 'jack2')), 'OR')->limit('1,3')->select();
//var_dump($list);

$list = $model->where(array('id' => array(1, 2, 3, 4, 5)))->limit('1,3')->select();

//var_dump($list);
$model->where(7)->update(array(
    'username' => 'jack-new',
    'updated_at' => time()
));


var_dump($model->orderBy('id desc')->find());
var_dump($model->find(3));


//执行原生sql
$sql = 'SELECT * FROM {{%user}} WHERE id=?';
$list = $model->query($sql, array(2));
var_dump($list);


var_dump($model->getConnection()->getQueryLog());
