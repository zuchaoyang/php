<?php
/**
 * 短信相关数据层
 * @author 杨益(yangyi@wmw.cn)
 * @copyright wmw.cn
 * @package Libraries
 * @since 2011-8-16
 */
class dUnicomInterface extends dBase {
	
    public function sendMessage($url,$postdata) {
        
        return $this->requestByPost($url, $postdata, 10);
    }

}