<?php
class AreaAction extends Controller{
    
    //设置默认选中的城市信息
    protected $_defalut = array(
        'provinceid' => 11,
        'cityid' => 8,
        'countyid' => 0
    );
    
    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        require_once('./Config/area.php');
        require_once('Libraries/common.php');
    }
    
    /**
     * 转换页面提交的请求参数
     * @param   $area_id 默认的当前数据拼凑的id
     * @param   $level 1表示修改省级别的数据，2标示市级别的数据，3表示county级别的数据
     * @return  包含了页面要显示的全部的Json串
     */
    public function getAreaList() {
        global $CONF_PROVINCE, $CONF_CITY, $CONF_COUNTY;
        //处理并获取对应的各级id的映射关系，如果各级的数据的依赖关系正确
        $area_id = $this->objInput->getInt('area_id');
        $is_init = $this->objInput->getStr('init');
        
        $is_init = !empty($is_init) ? true : false;
        
        $level = $this->generateLevel($area_id);
        //初始化请求的时候数据要特殊处理
        if($is_init) {
            $level = 0;
        }
        $level = $level && in_array($level , array(0 , 1 , 2)) ? $level : 0;
        
        list($provinceid , $cityid , $countyid) = $this->getDefalut($area_id);
        if(empty($provinceid) || empty($cityid)) {
            $this->jsonReturn(null, '系统繁忙!', -1);
        }
        
        //页面数据的组装
        $province_arr = $city_arr = $county_arr = array();
        switch($level){
        case 0 :
            //case的break是有意省略的
            $province_arr = & $CONF_PROVINCE;
            foreach((array)$province_arr as $key=>$name){
                $province_arr[$key] = array(
                    'value' => encodeAreaId($key , 0 , 0) , 
                    'innerHtml' => $name , 
                    'selected' => ($key == $provinceid) ? 'selected' : '',
                );
            }
        case 1 :
            //case的break是有意省略的
            $city_arr = & $CONF_CITY[$provinceid];
            if(empty($city_arr)) {
                break;
            }
            foreach((array)$city_arr as $key=>$name){
                $city_arr[$key] = array(
                    'value' => encodeAreaId($provinceid , $key , 0) , 
                    'innerHtml' => $name , 
                    'selected' => ($key == $cityid) ? 'selected' : '',
                );
            }
        case 2:
            $county_arr = $CONF_COUNTY[$provinceid][$cityid];
            if(empty($county_arr)) {
                break;
            }
            $county_arr = is_array($county_arr) ? $county_arr : array(0 => $county_arr);
            foreach($county_arr as $key=>$name){
                $county_arr[$key] = array(
                    'value' => encodeAreaId($provinceid , $cityid , $key), 
                    'innerHtml' => $name , 
                    'selected' => ($key == $countyid) ? 'selected' : '',
                );
            }
        }
        
        //数据错误情况分析
        if($level == 0 && ( empty($province_arr) || empty($city_arr) ) || $level == 1 && empty($city_arr)) {
            $this->jsonReturn(null, '系统繁忙!', -1);
        }
        
        $datas = array(
        	'province' => $province_arr,
            'city' => $city_arr,
            'county' => $county_arr,
        );
        
        $this->jsonReturn($datas, null, 1);
    }
  
    /**
     * 返回level
     * @param   $area_id 要解析的地区的当前编码信息
     * @return int
     */
    private function generateLevel($area_id) {
        if(!$area_id) return 0;
        list($provinceid , $cityid , $countyid) = decodeAreaId($area_id);
        $provinceid = intval($provinceid);
        $cityid = intval($cityid);
        if($provinceid == false) {
            return 0;
        }
        if($cityid == false) {
           return 1;
        }
        if($countyid == false) {
           return 2;
        }
        return 0;
    }
    
    /**
     * 获取各级菜单的默认选中项 , 该函数处理了级联是的数据正确性和获取对应的默认值处理
     * @param $area_id
     * @return 处理后的各级id信息
     */
    public function getDefalut($area_id){
        global $CONF_PROVINCE, $CONF_CITY, $CONF_COUNTY;
        
        list($provinceid , $cityid , $countyid) = decodeAreaId($area_id);
        //处理各级的相互依赖关系
        if(empty($provinceid)){
            unset($cityid , $countyid);
        } elseif(empty($cityid)){
            unset($countyid);
        }
        //如果对应的解析到的provinceid为空则全部都是用默认值
        if(empty($provinceid) || !isset($CONF_PROVINCE[$provinceid])){
            $provinceid = intval($this->_defalut['provinceid']);
            $cityid = intval($this->_defalut['cityid']);
            $countyid = intval($this->_defalut['countyid']);
        } 
        //获取关联的城市的id值
        if($provinceid && (empty($cityid) || !isset($CONF_CITY[$provinceid][$cityid]))){
            unset($countyid);                    //对应存在的地区的id是错误的
            if(!empty($CONF_CITY[$provinceid]) && is_array($CONF_CITY[$provinceid])){
                foreach($CONF_CITY[$provinceid] as $key=>$city){
                    if(is_numeric($key)){
                        $cityid = intval($key);
                        break;
                    }
                }
            }
        }
        //获取关联的地区的id值
        if($provinceid && $cityid && (empty($countyid) || !isset($CONF_COUNTY[$provinceid][$cityid]))){
            $defaultcountyarr = $CONF_COUNTY[$provinceid][$cityid];
            if(!empty($defaultcountyarr) && is_array($defaultcountyarr)){
                foreach($defaultcountyarr as $key=>$county){
                    if(is_numeric($key)){
                        $countyid = intval($key);
                        break;
                    }
                }
            } else {
                $countyid = 0;
            }
        }
    
        return array($provinceid , $cityid , $countyid);
    }
    
    /**
     * json返回数据
     * @param  $data
     * @param  $message
     * @param  $code
     */
    protected function jsonReturn($data = null, $message, $code = 1) {
        $json_datas = array(
            'error' => array(
                'code' => $code,
                'message' => !empty($message) ? $message : '',
            ),
            'data' => !is_null($data) ? $data : array(),
        );
        
        exit(json_encode($json_datas));
    }
}