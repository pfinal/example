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

$dbConfig = array(
    'dsn' => 'mysql:host=localhost;dbname=test',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8',
    'tablePrefix' => '',
);

// 实例化对象
$db = new Database\Connection($dbConfig);

// 新增用户
$sql = 'INSERT INTO `user` (username, password, created_at, updated_at) VALUES (:username, :password, :created_at, :updated_at)';
$user = array(
    'username' => 'jack',
    'password' => md5('111111'),
    'created_at' => time(),
    'updated_at' => time()
);
if ($db->execute($sql, $user) == 1) {
    echo 'success, id:' . $db->getLastInsertId();
} else {
    echo 'error';
}


//搜索以"j"开头的用户
$search = 'j';
$sql = 'SELECT * FROM `user` WHERE `username` LIKE ? LIMIT 2';
$users = $db->query($sql, array($search . '%'));
var_dump($users);


//统计查询
$sql = 'SELECT COUNT(*) FROM `user`';
$count = $db->queryScalar($sql);
var_dump($count);

