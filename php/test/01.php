<?php

echo 1.2%1;exit;
//口袋中的钱
$money = 2000;

if ($money >= 2000) {

    echo '乘飞机回家 ^_^';

} else if ($money >= 1000) {

    echo '坐火车回家 @_@';

} else {

    echo '骑自行车回家 #_#!!! ~~';

}


$goods = [
    [
        'image' => 'images/1.jpg', 'price' => 24.8, 'sales' => 844,
        'name' => '韩版高档水晶猫眼石花朵手镯 韩国锆石手链手环手饰品生日礼物女',
    ],
    [

        'image' => 'images/2.jpg', 'price' => 500, 'sales' => 1972,
        'name' => '笔记本电脑富士通13寸15寸宽屏双核游戏本手提上网本超级本包邮',
    ],
    [
        'image' => 'images/3.jpg', 'price' => 24.8, 'sales' => 844,
        'name' => '女童旗袍夏季 儿童民族风唐装公主连衣裙子 小孩女孩大童演出服装',
    ],
    [
        'image' => 'images/4.jpg', 'price' => 49, 'sales' => 1035,
        'name' => '韩版高档水晶猫眼石花朵手镯 韩国锆石手链手环手饰品生日礼物女',
    ],
    [

        'image' => 'images/5.jpg', 'price' => 49, 'sales' => 6639,
        'name' => '磁力片积木磁性拼装建构片益智儿童玩具秒杀',
    ],
    [
        'image' => 'images/6.jpg', 'price' => 29.99, 'sales' => 2240,
        'name' => '夏季男士精品polo衫青年修身翻领短袖t恤潮流韩版休闲男装保罗衫',
    ],
    [
        'image' => 'images/7.jpg', 'price' => 29, 'sales' => 800,
        'name' => '春夏季女士韩版长袖纯棉卫衣两件套春秋款学生显瘦休闲运动套装潮',
    ],
    [
        'image' => 'images/6.jpg', 'price' => 460, 'sales' => 41,
        'name' => '特价甩卖原装二手中文平板松下KX-MB778CN一体机',
    ],

];


$news = [
    '习近平主持召开规划建设北京城市副中心会议' => 'http://news.qq.com/a/20160527/060333.htm',
    '习近平的中印大国相处之道' => 'http://news.qq.com/a/20160527/041974.htm',
    '过去一年，哪些“中国制造”被总理寄予厚望' => 'http://news.qq.com/a/20160527/068125.htm',
];

echo '<ul>';
foreach ($news as $title => $url) {
    echo '<li><a href="' . $url . '">' . $title . '</a></li>';
}
echo '</ul>';


exit;
$score = [90, 88, 100];

for ($i = 1; $i <= 3; $i++) {
    //echo $i;
    echo $score[$i - 1];
}

foreach ($score as $key => $value) {
    //echo $key;
    echo $value;
}

$score = ['Jack' => 90, 'Mary' => 88, 'Lily' => 100];

foreach ($score as $key => $value) {
    echo $key;
    echo $value;
}

exit;

// 输出10个*
echo '**********';

// 如何输出100个?
$num = 1;

while ($num <= 100) {

    echo '*';

    //$num = $num + 1;

    $num++;
}


exit;
$score = ['Jack' => 90, 'Mary' => 88, 'Lily' => 100];
var_dump($score);

echo $score['Jack'];         //90
echo $score['Mary'];         //88


$score['Jack'] = 91;
var_dump($score);

$score['Tom'] = 99;          //加入一个元素
var_dump($score);

unset($score['Lily']);       //下标为2的元素(100)将会被删除
var_dump($score);

echo count($score);     //计算数组中的单元个数

exit;
$score = ['Jack' => 90, 'Mary' => 88, 'Lily' => 100];


exit;
//语文成绩
$chinese = 80;

if ($chinese > 60) {
    echo '语文成绩合格';
}

if ($chinese > 60) {

    echo '语文成绩合格';

} else {

    echo '语文成绩不合格';

}


//口袋中的钱
$money = 2000;

if ($money >= 2000) {

    echo '飞机 ^_^';

} else if ($money >= 1000) {

    echo '火车回家 @_@';

} else {

    echo '骑自行车回家 #_#!!! ~~';

}


$bool = 3 > 2 && 5 < 6;
var_dump($bool);            //true

$a = 80;
$b = 59;

$bool = $a < 60 || $b < 60;
var_dump($bool);            //true

$bool = true;
var_dump(!$bool);           //false


