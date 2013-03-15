<?php
		
class Smssendapi extends ApiController {
    
    protected $phone_list = array();
    protected $content = '';
    private $content_list = array();
    protected $operationStrategy = 0;
    
    //受限制的运营策略集合(单条)
    protected $oneToOne_Strategy_list = array(
                   OPERATION_STRATEGY_GD,
                   OPERATION_STRATEGY_LN,
                   OPERATION_STRATEGY_CQ
               );
    //黑龙江特殊运营策略（40条手机记录一组）
    protected $oneToHLJ_Strategy_list = array(
                   OPERATION_STRATEGY_HLJ
               );
    
    //短信内容字符个数最大为70
    protected $content_num_max = 65;
    /**
     * 群发手机号数量最大由79改为30 2012-2-15 
     * 由30改为10 江苏电信最大支持10个手机号群发 2012-3-30
     */ 
    protected $mphone_num_max = 10;
    
    public function send($phone_list, $content, $operationStrategy) {
        //初始化变量信息
        $this->phone_list = (array)$phone_list;
        $this->content = $content;
        $this->operationStrategy = $operationStrategy;
        
        //过滤手机号
        $this->filterPhoneList();

        //格式化短信内容
        $this->formatContent();

        //格式要发送的短信数据
        $dataarr = $this->formatData();
        
        return $this->initDatabaseBat(& $dataarr);
    }
    /**
     * 根据运营策略 初始化群发手机号数量
     *  
     * 群发手机号数量最大由79改为30 2012-2-15 
     * 由30改为10 江苏电信最大支持10个手机号群发 2012-3-30
     * 黑龙江支持50条短信群发
     */ 
     private function formatPhoneNum() {
         if(in_array($this->operationStrategy, $this->oneToHLJ_Strategy_list)) {
             return 40;
         }else{
             return 10;
         }
     }
    /**
     * 格式化短信内容
     */
    private function formatContent() {
        if(empty($this->content)) {
            return false;
        }
        
         //html实体替换
        $this->content = htmlspecialchars_decode($this->content);
        // 转换成utf-8编码 
        $this->strtoutf8();
        //将内容进行分组
        $this->groupContent();
        
        return true;
    }
    
    /**
     * 批量插入数据库
     * @param $dataarr
     */
    private function initDatabaseBat($dataarr) {
        if(empty($dataarr)) {
            return false;
        }
        $mSmsSend = ClsFactory::Create('Model.mSmsSend');
   
        $chunk_list = array_chunk($dataarr, 500, true);
      
        foreach($chunk_list as $key => $sms_list) {
            $mSmsSend->addSmsSendBat($sms_list);
            unset($chunk_list[$key]);
        }
        
        return true;
    }
    
    /**
     * 格式化数据,默认是按照多条的格式封装数据
     */
    private function formatData() {
        if($this->allowSendToMultiPhonesByOperation()) {
            return $this->formatDataForSendMulti();
        }
        
        return $this->formatDataForSendOneByOne();
    }
    
    /**
     * 根据运营策略判断是否允许一次发送多个手机号
     */
    private function allowSendToMultiPhonesByOperation() {
        return in_array($this->operationStrategy, $this->oneToOne_Strategy_list) ? false : true;
    }
	
    
    /**
     * 按照一条一条发格式数据
     */
    private function formatDataForSendOneByOne() {
        if(empty($this->phone_list)) {
            return false;
        }
        
        $dataarr = array();
        foreach($this->phone_list as $onePhone) {
            foreach((array)$this->content_list as $content) {
                $dataarr[] = array(
                    'sms_send_mphone'     =>$onePhone, 
            		'sms_send_content' => $content,
                    'sms_send_mphone_num' => 1, 
                    'sms_send_type'       => 0,        
                    'db_createtime'       => date("Y-m-d H:i:s"),
            		'sms_send_bussiness_type' => $this->operationStrategy
                );
            }
        }
        
        return $dataarr;
    }
    
    /**
     * 格式化数据按照一次可以发多条的格式
     */
    private function formatDataForSendMulti() {
        if(empty($this->phone_list)) {
            return false;
        }
        //一次发送短信条数初始化
        $this->mphone_num_max = $this->formatPhoneNum();
        
        $chunk_phone_arr = array_chunk($this->phone_list, $this->mphone_num_max);
        foreach((array)$this->content_list as $content) {
            foreach($chunk_phone_arr as $key => $phone_list) {
                $dataarr[] = array(
                    'sms_send_mphone'     => implode(" ", $phone_list),
                	'sms_send_content' => $content,
                    'sms_send_mphone_num' => count($phone_list),  
                    'sms_send_type'       => 0,        //发送类型：0表示等待处理
                    'db_createtime'       => date("Y-m-d H:i:s"),
                    'sms_send_bussiness_type' => $this->operationStrategy,
                );
            }
        }
        unset($chunk_phone_arr);
        
        return $dataarr;
    }
    //计算utf-8的字符长度
    private function abslength($str)
    {
        if(empty($str)){
            return 0;
        }
        if(function_exists('mb_strlen')){
            return mb_strlen($str,'utf-8');
        }
        else {
            preg_match_all("/./u", $str, $ar);
            return count($ar[0]);
        }
    }

 
//    /*
//        utf-8编码下截取中文字符串,参数可以参照substr函数
//        @param $str 要进行截取的字符串
//        @param $start 要进行截取的开始位置，负数为反向截取
//        @param $end 要进行截取的长度
//    */
//    private function utf8_substr($str,$start=0) {
//        if(empty($str)){
//            return false;
//        }
//        if (function_exists('mb_substr')){
//            if(func_num_args() >= 3) {
//                $end = func_get_arg(2);
//                return mb_substr($str,$start,$end,'utf-8');
//            }
//            else {
//                mb_internal_encoding("UTF-8");
//                return mb_substr($str,$start);
//            }       
//     
//        }
//        else {
//            $null = "";
//            preg_match_all("/./u", $str, $ar);
//            if(func_num_args() >= 3) {
//                $end = func_get_arg(2);
//                return join($null, array_slice($ar[0],$start,$end));
//            }
//            else {
//                return join($null, array_slice($ar[0],$start));
//            }
//        }
//    }
    //内容分组
    private function groupContent() {
    	$this->content_list = array();
    	
        $content_len = $this->abslength($this->content);
        if($content_len > $this->content_num_max) {
            $split_count = ceil($content_len / $this->content_num_max);//将超出限定长度的内容分成多条发送 ，ceil舍余进一
            for($i = 0; $i < $split_count; $i++) {
                $start = $i * $this->content_num_max;
                $j = $i+1;
                $this->content_list[] = "($j/$split_count)".mb_substr($this->content , $start , $this->content_num_max , 'utf-8');
            }
        } else {
            $this->content_list[] = $this->content;
        }
        return true;
    }
    
    /**
     * 过滤手机号列表
     */
    private function filterPhoneList() {
        if(empty($this->phone_list)) {
            return false;
        }
        
        foreach($this->phone_list as $key=>$val) {
            if(empty($val) || strlen($val) != 11) {
                unset($this->phone_list[$key]);
            }
        }
        return true;
    }
    
    /**
     * 字符编码
     */
    private function strtoutf8() {
        $content = $this->content;
        if(!is_utf8($this->content)) { // 转换成utf-8编码 
            $this->content = iconv("gbk" , "utf-8" , $content);
        }
        if(!is_utf8($this->content)) {
            $this->content = iconv("gb2312" , "utf-8" , $content);
        }
        if(!is_utf8($this->content)) {
            $this->content = $content;
        }
    }

}
