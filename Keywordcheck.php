<?php
/**
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
}
