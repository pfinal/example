<?php

// src目录中，使用PSR-4命名规范的类，使用时将被自动装载
// PSR-4: 命名空间与目录对应，文件名采用"类名.php"格式，目录和文件名严格区分大小写
require_once './src/autuload.php';

var_dump(new Bar);
var_dump(new Demo\Foo);
var_dump(new Demo\Test\Baz);

use Demo\Test\Baz;

var_dump(new Baz);
