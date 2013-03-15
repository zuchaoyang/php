<?php
/**
 * 校园导图 展现地图
 * @author    lnczx <lnczx0915@gmail.com>
 */
include_once(CONFIGE_DIR.'/area.php');

class SchoolMapsAction extends SnsController {

    public $_isLoginCheck = false;
    
    public $_school_top_num = 5;
    
    const LENGTH = 35;
    /**
     * 
     * 首页展现地图信息
     */
    public function index(){
        global $CONF_PROVINCE, $CONF_CITY;
        $school_area_count = $this->get_school_area_count();
        $school_area_top = $this->get_school_area_top();
        $this->assign('school_area_count', json_encode($school_area_count));
        $this->assign('school_area_top', json_encode($school_area_top));
              
        $this->assign('provinces', $CONF_PROVINCE);
        $this->assign('citys', $CONF_CITY);
        
        //判断IE6
        $is_ie6 = false;
        if (strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 6.0")) {
            $is_ie6 = true;
        }
        $this->assign('is_ie6', $is_ie6);
        
        $this->display('./school_map/maps');
    }
    
    /**
     * 
     * 学校列表页面
     */
    
    public function get_school_list(){
       
        $area_id = $this->objInput->getInt("area_id");
        if (empty($area_id)) {
           $this->redirect('/Sns/school_maps');
           exit;
        }
        
        $city_id = $this->objInput->getInt("city_id");
        
        global $CONF_PROVINCE, $CONF_CITY;
        
        $area_name = $city_name = '';
        if (!empty($CONF_PROVINCE[$area_id])) {
           $area_name = $CONF_PROVINCE[$area_id];
        }
        if (!empty($city_id) && !empty($CONF_CITY[$area_id][$city_id])) {
           $city_name = $CONF_CITY[$area_id][$city_id]; 
        } 
        
        //右边学校列表 分页处理
        
        $length = self::LENGTH;
        $curpage = '';
        $page = $this->objInput->getInt('page');
        $page = max($page,1);
        $prepage = $page-1;
        $prepage = max($prepage,1);
        $offset = ($page-1)*$length;       
        
        
        $schoolInfos = array();
        $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
        $schoolInfos = $mSchoolInfo->getSchoolInfoByAreaId($area_id, $city_id, 1, $offset, $length + 1);        
        
        
    	if (count($schoolInfos) <= $length){
	    	$endpage = $page;
	        $curpage = 'end';
	    } else {
	    	$endpage = $page + 1;
	    	array_pop($schoolInfos);
	    }
        
        //左边地区列表
        $this->assign('provinces', $CONF_PROVINCE);
        $this->assign('citys', $CONF_CITY);            
        //右侧导航
        $this->assign('area_id', $area_id);
        $this->assign('city_id', $city_id);        
        $this->assign('area_name', $area_name);
        $this->assign('city_name', $city_name);
        
        //右边学校列表
        
        $datas = array();
        foreach ($schoolInfos as $school_id => $schoolInfo) {
            
            $url = $schoolInfo['school_url_old'];
            if (!empty($schoolInfo['school_url_new']) && $schoolInfo['school_url_new'] != '无') {
                $url = $schoolInfo['school_url_new'];
            }            
            
            $datas[$school_id] = array('name' => $schoolInfo['school_name'], 'url' => 'http://' .$url);
        }

        $this->assign('schoolInfo', $datas);
	    $this->assign('curpage',$curpage);
	    $this->assign('page',$page);
	    $this->assign('prepage',$prepage);
	    $this->assign('endpage',$endpage);        
              
        $this->display('./school_map/list');
    }    
    
    
    /**
     * 
     * 获取学校地区分布个数
     * 格式如下：
     * 
     *  array(
     *    11 => 50,
     *    23 => 149,
     *    ...
     *  )
     */    
    
    public function get_school_area_count() {
        global $CONF_PROVINCE;

    	$mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
        $allSchoolInfo = $mSchoolInfo->getSchoolInfo();
        
//        print_r($allSchoolInfo);
        // Step1 将area_id 转换为只有省市的区段，比如 41200873  -> 41000000
        $area_info = array();
        foreach($allSchoolInfo as $key=>$schoolinfo){
            if ($schoolinfo['school_status'] == 1 && $schoolinfo['is_pub'] == 1) {
                $area_info[] = substr($schoolinfo['area_id'], 0, 2);
            }
        }
//        print_r($area_info);
        //Step2 利用array_count_values统计 和 asort 排序
        $result = array_count_values($area_info);

        foreach($CONF_PROVINCE as $area_id=>$name) {
            if (empty($result[$area_id])) {
                $result[$area_id] = 0;
            }
        }
     
        ksort($result);
        return !empty($result) ? $result : array();
    }
    
    /**
     * 
     * 获取前5个学校名称
     */

    public function get_school_area_top() {
        global $CONF_PROVINCE;
//        echo '<pre>';
        $provinces = $CONF_PROVINCE;
//        print_r($provinces);
        
    	$mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
        $allSchoolInfo = $mSchoolInfo->getSchoolInfoOrderByIdDesc(' school_status = 1 and is_pub = 1','school_id desc');
        
        $area_top_info = array();
        foreach($allSchoolInfo as $key=>$schoolinfo){
            $province = substr($schoolinfo['area_id'], 0, 2);
            $url = '';
            if (count($area_top_info[$province]) < $this->_school_top_num) {
                $url = $schoolinfo['school_url_old'];
                if (!empty($schoolinfo['school_url_new']) && $schoolinfo['school_url_new'] != '无') {
                    $url = $schoolinfo['school_url_new'];
                }
                $area_top_info[$province][] = array('name' => $schoolinfo['school_name'], 'url' => 'http://' .$url);
            }
        }
//        print_r($area_top_info);
        $result = $area_top_info;        
        
        return !empty($result) ? $result : array();

    }
}
