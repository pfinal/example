<?php

/**
 * 注册类自动装载函数 PSR-4规范 简单版本
 * @author 邹义良
 */
spl_autoload_register(function ($class) {

    // 兼容 PHP 5.3.0 - 5.3.2 https://bugs.php.net/50731
    if ('\\' == $class[0]) {
        $class = substr($class, 1);
    }

    // 将类名转换为路径
    $path = strtr($class, '\\', DIRECTORY_SEPARATOR);

    // 拼接完整文件名
    $file = __DIR__ . DIRECTORY_SEPARATOR . $path . '.php';

    // 检测文件是否存在
    if (file_exists($file)) {
        include $file;
    }
});
