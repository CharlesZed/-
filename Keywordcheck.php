<?php
/**
 * 
 * Auth LZC
 * Desc 敏感词检测
 * Date 2017-12-29 17:05:42
 */
namespace Act;
use Act\Frontend;
class Keywordcheck extends Frontend{
	public function doGet(){

    }
    /**
     * Desc 对象 转 数组
     * @param object $obj 对象
     * @return array
     */
     function object_to_array($obj) {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)$this->object_to_array($v);
            }
        }

        return $obj;
    }
	public function doPost(){
		#do something
        $str = post('str',0);
        $url = 'http://www.hoapi.com/index.php/Home/Api/check';
        $data = ['str'=>$str,'token'=>'5b645ae4da8b9b5ad9750ff789f8ca2e'];
        $return = curl($url,'post',$data);
        $return = json_decode($return);
        $return = $this->object_to_array($return);
        if ($return['code'] == 1){
            //检测到含有敏感字符
            $returnWord = array_column($return['data']['error'],'word');
            $str = '';
            foreach ($returnWord as $k =>$v){
                $str .=$v ;
                if ($k >0){
                    $str .=' ';
                }
            }
            $data = ("检测到您含有敏感字符如下：".$str) ; //array_column($return['data']['error'],'word')
            return $this->jsonp(['success'=>1,'extensions'=>'', 'data'=>$data]);
        }elseif($return['code'] == '0'){
            //没有敏感字符
            $data = ("没有敏感字符") ;
            return $this->jsonp(['success'=>1,'extensions'=>'', 'data'=>$data]);
        }elseif ($return['code'] == 400){
            //查询过快
            return $this->jsonp(['error'=>1, 'msg'=>'网络繁忙，请重试']);
        }else{
            //未知错误
            return $this->jsonp(['fault'=>1, 'msg'=>'发生未知错误，请联系客服']);
        }
    }


    /**
     * Desc 简单的 php 防注入、防跨站 函数
     * @return String
     */

    function fn_safe($str_string) {
        //直接剔除
        $_arr_dangerChars = array(
            "|", ";", "$", "@", "+", "\t", "\r", "\n", ",", "(", ")", PHP_EOL //特殊字符
        );

        //正则剔除
        $_arr_dangerRegs = array(
            //-------- 跨站 --------

            //html 标签
            "/<(script|frame|iframe|bgsound|link|object|applet|embed|blink|style|layer|ilayer|base|meta)\s+\S*>/i",

            //html 属性
            "/on(afterprint|beforeprint|beforeunload|error|haschange|load|message|offline|online|pagehide|pageshow|popstate|redo|resize|storage|undo|unload|blur|change|contextmenu|focus|formchange|forminput|input|invalid|reset|select|submit|keydown|keypress|keyup|click|dblclick|drag|dragend|dragenter|dragleave|dragover|dragstart|drop|mousedown|mousemove|mouseout|mouseover|mouseup|mousewheel|scroll|abort|canplay|canplaythrough|durationchange|emptied|ended|error|loadeddata|loadedmetadata|loadstart|pause|play|playing|progress|ratechange|readystatechange|seeked|seeking|stalled|suspend|timeupdate|volumechange|waiting)\s*=\s*(\"|')?\S*(\"|')?/i",

            //html 属性包含脚本
            "/\w+\s*=\s*(\"|')?(java|vb)script:\S*(\"|')?/i",

            //js 对象
            "/(document|location)\s*\.\s*\S*/i",

            //js 函数
            "/(eval|alert|prompt|msgbox)\s*\(.*\)/i",

            //css
            "/expression\s*:\s*\S*/i",

            //-------- sql 注入 --------

            //显示 数据库 | 表 | 索引 | 字段
            "/show\s+(databases|tables|index|columns)/i",

            //创建 数据库 | 表 | 索引 | 视图 | 存储过程 | 存储过程
            "/create\s+(database|table|(unique\s+)?index|view|procedure|proc)/i",

            //更新 数据库 | 表
            "/alter\s+(database|table)/i",

            //丢弃 数据库 | 表 | 索引 | 视图 | 字段
            "/drop\s+(database|table|index|view|column)/i",

            //备份 数据库 | 日志
            "/backup\s+(database|log)/i",

            //初始化 表
            "/truncate\s+table/i",

            //替换 视图
            "/replace\s+view/i",

            //创建 | 更改 字段
            "/(add|change)\s+column/i",

            //选择 | 更新 | 删除 记录
            "/(select|update|delete)\s+\S*\s+from/i",

            //插入 记录 | 选择到文件
            "/insert\s+into/i",

            //sql 函数
            "/load_file\s*\(.*\)/i",

            //sql 其他
            "/(outfile|infile)\s+(\"|')?\S*(\"|')/i",
        );

        $_str_return = $str_string;
        //$_str_return = urlencode($_str_return);

        foreach ($_arr_dangerChars as $_key=>$_value) {
            $_str_return = str_ireplace($_value, "", $_str_return);
        }

        foreach ($_arr_dangerRegs as $_key=>$_value) {
            $_str_return = preg_replace($_value, "", $_str_return);
        }

        $_str_return = htmlentities($_str_return, ENT_QUOTES, "UTF-8", true);

        return $_str_return;
    }

}