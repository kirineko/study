<?php
/**
 * 抓取live站点类目信息
 * @author liyi
 * @version 1.0.0
 */

use Myf\Mvc\Task;

class GetHtmlTask extends Task {

    /**
     * 主方法: 抓取live页面的条目内容,并以特定格式打印输出到文件
     * 实现: 1. 抓取网页条目内容; 2. 以特定方式写入文件
     *
     * 方法调用:
     *    function _getWebContent($url): Array, 抓取网页内容并以数组形式返回
     *    function _writeToFile($arr, $file), 将数组内容写入到文件
     */
    public function mainAction() {
        //待抓取网站
        $url = 'http://www.genshuixue.com/livezone';

        //抓取网页条目
        $arr = $this->_getWebContent($url);

        //写入文件
        $file = 'result.php';
        $res = $this->_writeToFile($arr, $file);
        if ($res) {
            echo 'SUCC! 数组已经写入根目录下的: result.php 文件。';
        } else {
            echo 'FAIL! 写入异常!';
        }
    }

    /**
     * 使用正则表达式匹配抓取网页中特定内容
     * 1. preg_match : 定位到<tr>标签,排除<tr>标签以外的内容
     * 2. preg_match_all: 以懒惰模式获取所有<td>标签内容并存入数组
     * 3. preg_match_all: 以懒惰模式获取所有<a>标签内容并存入数组
     * 4. a标签的链接地址以及a标签的元素内容就是要抓取的最终内容
     * 5. 对抓取的内容组织成数组并返回
     *
     * @param $url 抓取页面的url
     *
     * @return $result 规定格式的数组
     */
    private function _getWebContent($url) {

        $strContent = file_get_contents($url);

        //1. 获取<tr>标签内的所有内容
        preg_match("/<tr>.*<\/tr>/", $strContent, $arrTrContent);
        $trContent = $arrTrContent[0];

        //2. 获取<td>标签内的所有内容,正则表达式中?代表懒惰模式
        preg_match_all("/<td>.*?<\/td>/", $trContent, $arrTdContent, PREG_PATTERN_ORDER);

        //3. 循环获取所有<a>标签并写入数组
        foreach ($arrTdContent[0] as $strItem) {
            preg_match_all("/<a(.*?)href=\"(.*?)\"(.*?)>(.*?)<\/a>/i", $strItem, $arrAlink, PREG_PATTERN_ORDER);
            $arrHref [] = $arrAlink[2];
            $arrTitle [] = $arrAlink[4];
        }

        //4. 按规定样式重新组织数组
        $result = $this->_formatArray($arrHref, $arrTitle);

        return $result;
    }

    /**
     * 按固定格式组织数组
     *
     * @param $arrHref 全部的URL数组
     * @param $arrTitle 全部的Title数组
     * @return array $result 规定格式的数组
     */
    private function _formatArray($arrHref, $arrTitle) {
        //将title数组中首元素取出,作为栏目标题
        foreach ($arrTitle as &$title) {
            $text [] = $title[0];
            unset($title[0]);
        }

        //将href数组中首元素取出,作为栏目url
        foreach ($arrHref as &$href) {
            $url [] = $href[0];
            unset($href[0]);
        }

        //解除引用
        unset($title);
        unset($href);

        //重新组织title项
        $title = array_combine($text, $url);
        foreach ($title as $text => $url) {
            $resTitle [] = [
                'url' => $url,
                'text' => $text,
            ];
        }

        //对arrTitle数组和arrHref数组剩余元素重新数组,构成list项目
        foreach ($arrTitle as $key => $subArrTitle) {
            $textList = $subArrTitle;
            $hrefList = $arrHref[$key];
            $lists = array_combine($textList, $hrefList);
            $subList = [];
            foreach ($lists as $text => $url) {
                $subList [] = [
                    'url' => $url,
                    'text' => $text,
                ];
            }
            $resList [] = $subList;
        }

        //按最终格式返回result数组
        $result = [];
        foreach ($resTitle as $key => $title) {
            $result [] = [
                'title' => $title,
                'list' => $resList[$key],
            ];
        }

        return $result;
    }

    /**
     * @param $arr 待写入的数组
     * @param $file 待写入的文件路径
     *
     * @return boolean true|false
     */
    private function _writeToFile($arr, $file) {

        $strResult = var_export($arr, true) . ';';

        try {
            $res = file_put_contents($file, "<?php \nreturn ");
            if (!$res) {
                return false;
            }
            return file_put_contents($file, $strResult, FILE_APPEND|LOCK_EX);
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }
}
