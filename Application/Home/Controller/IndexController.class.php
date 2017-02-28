<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        
    }
//    public function index(){
/**
模拟随机选取3万条热数据，取出后存储在memcached,声明周期为5分钟，同时
，调整ab参数，尽量在1分钟内完成缓存创建
**/
//	$id = 269999 + mt_rand(0,30000);
//	$sql = 'select class_id,caption from class where class_id='.$id;
//	$mem = new \Memcache();
//	$mem->pconnect('localhost');
//
//	if(($com = $mem->get($sql)) === false){
//		$rs = M()->query($sql);
//		$com = $rs[0];
//		$mem->add($sql,$com,false,50);
//	}
//	print_r($com);
//    }
};
