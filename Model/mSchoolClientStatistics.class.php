<?php
class mSchoolClientStatistics extends mBase{

     /*
     * 初始化用户统计数据
     */
    protected $_dSchoolClientStatistics = null; 
    
    public function __construct() {
        $this->_dSchoolClientStatistics = ClsFactory::Create('Data.dSchoolClientStatistics');
    }
    
    public function addSchoolClientStatistics($data){
        if(empty($data)) {
            return false;
        }
        
        return $this->_dSchoolClientStatistics->addSchoolClientStatistics($data);
    }

    // 通过地区码和学校姓名得到用户统计信息  
    public function getStatisticsnum($school_name, $area_id) {
        if(empty($area_id) && empty($school_name)) {
            return false;
        }

        $area_id = strval($area_id);
        if(strlen($area_id) < 9) {
        	$area_id = str_pad($area_id, 9, '0', STR_PAD_LEFT);
        }
        $province_id = substr($area_id, 0, 3);
        $city_id     = substr($area_id, 3, 3);
        $district_id = substr($area_id, 6, 3);

        if(intval($province_id) <= 0) {
        	unset($province_id, $city_id, $district_id);
        } elseif(intval($city_id) <= 0) {
        	unset($district_id);
        }

        $wheresql = array();
        if(intval($district_id) > 0) {
        	$area_id = intval($area_id);
        	$wheresql[] = "area_id='$area_id'";
        } elseif(intval($city_id) > 0) {
        	$min_area_id = intval($province_id . $city_id . '000');
        	$max_area_id = intval($province_id . $city_id . '999');
        	$wheresql[] = "area_id >= '$min_area_id' and area_id <= '$max_area_id'";
        } elseif(intval($province_id) > 0) {
        	$min_area_id = intval($province_id . '000000');
        	$max_area_id = intval($province_id . '999999');
        	$wheresql[] = "area_id >= '$min_area_id' and area_id <= '$max_area_id'";
        }
        if(!empty($school_name)) {
        	$school_name = addslashes(str_replace(array('%', '_'), "", $school_name));
        	$wheresql[] = "school_name like '$school_name%'";
        }
         
        return $this->_dSchoolClientStatistics->getInfo($wheresql);
    }
    
    //查询学校表所有的信息
    public function getSchoolInfos(){
    	return $this->_dSchoolClientStatistics->getInfo();
    }    
    
    //通过学校id查询学校统计信息
    public function getSchoolClientStatisticsById($schoolIds) {
    	if (empty($schoolIds)) {
    		return false;
    	}
    	
    	return $this->_dSchoolClientStatistics->getSchoolClientStatisticsById($schoolIds);
    }
 	/*
     * 清空表
     */
    function cleartable(){
        $this->_dSchoolClientStatistics->cleartable();
    }
}