<?php
/**
 * Author: liyi
 * Datetime: 16/7/28, 上午9:56
 */

$url = 'http://www.genshuixue.com/livezone';

$web_raw_content = file_get_contents($url);

//抓取<tr>标签的所有内容
$preg_tr = "/<tr>.*<\/tr>/";
preg_match($preg_tr, $web_raw_content, $arr_tr);
$table_content = $arr_tr[0];

$preg_td = "/<td>.*?<\/td>/";
preg_match_all($preg_td, $table_content, $arr_td, PREG_PATTERN_ORDER);


foreach($arr_td[0] as $str_td_item) {
    $preg_alink = "/<a.*?<\/a>/";
    preg_match_all($preg_alink, $str_td_item, $arr_alink, PREG_PATTERN_ORDER);
    print_r($arr_alink[0]);
}

die;
$arr_res = json_decode($res, true);


$tmp = $arr_res['data']['category'];

$res = [];

foreach ($tmp as $key => $value) {
    $sub_res = [
        'title' => [
            'url' => $value['url'],
            'text' => $key,
        ],
    ];
    foreach ($value['sub_class'] as $sub_item) {
        $sub = [
            'url' => $sub_item['url'],
            'text' => $sub_item['name'],
        ];
        $sub_res['list'][]= $sub;
    }
    $res[] = $sub_res;
}

$str_res = var_export($res, true) . ';';

echo file_put_contents('result.php', "<?php \nreturn ");

file_put_contents('result.php', $str_res, FILE_APPEND|LOCK_EX);
