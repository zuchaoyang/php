<?php
class mCount extends mBase{
    protected $_dCount;
    public function __construct() {
    	$this->_dCount = ClsFactory::Create('Data.dCount');
    }
    //wmsstart----------------------------------------------------------------
	/**
	 * 20121106
	 * 根据手机绑定类型得到所有用户的手机数
	 */
	public function phoneCountOrderbyPhoneType() {
	    $sql = 'SELECT COUNT(phone_id) count,phone_type FROM wmw_phone_info GROUP BY phone_type';
	    return $this->_dCount->query($sql);
	}
	
	/*
     * 20121106
     * SELECT COUNT(client_account),client_type from wmw_client_account GROUP BY client_type
     * 根据帐号类型得到所有帐号数
     */
    public function getClientAccountOrderbyClientType() {
        $sql = 'SELECT COUNT(client_account) count,client_type from wmw_client_account GROUP BY client_type';
        return $this->_dCount->query($sql);
    }
    
    /**
     * 统计所选区域的用户统计数
     */
    public function getClientCountByArea($area_id,$school_name) {
        if(empty($area_id)) {
            return false;
        }
        $where_area = $this->getArealist($area_id);

        $sql = 'SELECT d.school_name,d.school_id,d.area_id,COUNT(d.client_account) count,d.client_type 
		FROM (SELECT DISTINCT a.school_name,a.school_id,a.area_id,c.client_account,c.client_type from wmw_school_info 

a,wmw_class_info b,wmw_client_class c
		where ';
        
        if(!empty($school_name)) {
        	$school_name = addslashes(str_replace(array('%', '_'), "", $school_name));
        	$sql .= "a.school_name like '$school_name%' AND ";
        }
        
        $sql .= $where_area.' AND a.school_id=b.school_id 
				AND b.class_code=c.class_code ) d 
				GROUP BY d.school_id,d.client_type';

        $rs['people'] = $this->_dCount->query($sql);
        $count_arr = array();
        foreach($rs['people'] as $key=>$val) {
            $count_arr['people']['people_total_count'] += $val['count'];
           
            if($val['client_type'] == 0) {
                $type = 'student_count';
            }else if($val['client_type'] == 1) {
                $type = 'teacher_count';
            }else if($val['client_type'] == 2) {
                $type = 'parents_count';
            }
            $count_arr['people'][$type] = $val['count'];
            $school_ids[$val['school_id']] = $val['school_id'];
        }
        $chunk_list = array_chunk($school_ids, 500, true);
        unset($school_ids);
        $a = array();
        foreach($chunk_list as $key1=>$val1) {
            $school_ids_str = implode(',', $val1);
            $sql = 'SELECT a.school_id,COUNT(a.client_account) count,e.phone_type
    				FROM 
    				( select distinct c.client_account,c.client_type,b.school_id 
    				from wmw_class_info b,wmw_client_class c 
    				where b.class_code=c.class_code and b.school_id in ('.$school_ids_str.')) a,
    				wmw_business_phone d,wmw_phone_info e
    				where a.client_account=d.account_phone_id1 
    				AND d.account_phone_id2=e.phone_id 
    				GROUP BY a.school_id,e.phone_type';
    		unset($chunk_list[$key1]);
            $rs1 =  $this->_dCount->query($sql);
    		$a = array_merge($a,$rs1);
        }

        $rs['phone'] = $a;
        unset($a);
        
        return $rs;
    }
    
    
    //通过分页统计当前页的统计数
    public function getCountByAreaSchooName($school_name, $area_id, $offset, $length) {
        if(empty($area_id) && empty($school_name)) {
            return false;
        }
        
        $where_area = $this->getArealist($area_id);
        $count_arr = $this->getPeopleCountByArea($school_name,$where_area, $offset, $length);
        foreach($count_arr['people'] as $school_id=>$peopleval) {
            if(!empty($count_arr['phone'][$school_id])) {
                $count_arr['people'][$school_id] = array_merge($peopleval, $count_arr['phone'][$school_id]);
            }else{
                $count_arr['people'][$school_id] = array_merge($peopleval, array(
                						"total_phone_count" => 0,
    									"old_phone_count" => 0,
    									"new_phone_count" => 0
                                        )
                );
            }
            unset($count_arr['phone'][$school_id]);
        }
        unset($count_arr['phone']);
        return $count_arr['people'];
        
    }
    // 通过地区码和学校姓名得到用户统计信息  
    public function getPeopleCountByArea($school_name, $where_area, $offset, $length) {
        if(empty($where_area) && empty($school_name)) {
            return false;
        }
        
        $school_sql = 'SELECT school_id,school_name,area_id FROM wmw_school_info a WHERE school_status=1 AND ' . $where_area;
        
        if(!empty($school_name)) {
        	$school_name = addslashes(str_replace(array('%', '_'), "", $school_name));
        	$school_sql .= " AND a.school_name like '$school_name%' ";
        }
        
        $school_sql .= "limit $offset,$length";
   
        $rs_school = $this->_dCount->query($school_sql);

        $school_ids = array();
        $count_arr = array();
        
        foreach($rs_school as $key=>$val) {
        	$school_ids[$val['school_id']] = $val['school_id'];
        	$count_arr['people'][$val['school_id']]['school_name'] = $val['school_name'];
            $count_arr['people'][$val['school_id']]['area_id'] = $val['area_id'];
        }
        unset($rs_school);
        
        $school_id_str = implode(',', $school_ids);

        $sql = 'SELECT d.school_id,COUNT(d.client_account) count,d.client_type FROM
        		(SELECT DISTINCT b.school_id,c.client_account,c.client_type 
				FROM wmw_class_info b,wmw_client_class c
				where ';

        $sql .= 'b.school_id in (' . $school_id_str . ') AND b.class_code=c.class_code) d GROUP BY d.school_id,d.client_type';
        
        $rs = $this->_dCount->query($sql);

        foreach($rs as $key=>$val) {
            $count_arr['people'][$val['school_id']]['people_total_count'] += $val['count'];
           
            if($val['client_type'] == 0) {
                $type = 'student_count';
            }else if($val['client_type'] == 1) {
                $type = 'teacher_count';
            }else if($val['client_type'] == 2) {
                $type = 'parents_count';
            }
            $count_arr['people'][$val['school_id']][$type] = $val['count'];
            unset($rs[$key]);
        }
      
        $count_arr['phone'] = $this->getPhoneBdingCountByArea($school_ids);
        
        return $count_arr;
    }
    //手机绑定用户统计
    public function getPhoneBdingCountByArea($school_ids) {
        if(empty($school_ids) && empty($school_ids)) {
            return false;
        }
        if(!is_array($school_ids)) {
            $school_ids = (array)$school_ids;
        }
        $school_ids_str = implode(',', $school_ids);
//        $sql = 'SELECT b.school_id,COUNT(c.client_account) count,e.phone_type
//				FROM wmw_class_info b,wmw_client_class c,wmw_business_phone d,wmw_phone_info e
//				where b.school_id in ('.$school_ids_str.')
//				AND b.class_code=c.class_code AND c.client_account=d.account_phone_id1 
//				AND d.account_phone_id2=e.phone_id 
//				GROUP BY b.school_id,e.phone_type';
        
        $sql = 'SELECT a.school_id,COUNT(a.client_account) count,e.phone_type
				FROM ( select distinct c.client_account,c.client_type,b.school_id from wmw_class_info 

b,wmw_client_class c where b.class_code=c.class_code and b.school_id in ('.$school_ids_str.')) a,
				wmw_business_phone d,wmw_phone_info e
				where a.client_account=d.account_phone_id1 
				AND d.account_phone_id2=e.phone_id 
				GROUP BY a.school_id,e.phone_type';
        $rs =  $this->_dCount->query($sql);
        $phones_count = array();
        foreach($rs as $key=>$val) {
            $phones_count[$val['school_id']]['total_phone_count'] += $val['count'];
            if($val['phone_type'] == 1) {
                $phones_count[$val['school_id']]['old_phone_count'] = $val['count'];
            }
            if($val['phone_type'] == 2) {
                $phones_count[$val['school_id']]['new_phone_count'] = $val['count'];
            }
            unset($rs[$key]);
        }
        
        return $phones_count;
        
    }
    //wmsend----------------------------------------------------------------------
    //bmsstart--------------------------------------------------------------------
    public function getCountBySchoolId($school_id) {
        if(empty($school_id) || is_array($school_id)) {
            return false;
        }
        $sql =  'select a.client_type, count(*) count from 
                ( select distinct b.client_account,b.client_type from wmw_client_class b,wmw_class_info c where 

b.class_code=c.class_code and c.school_id='.$school_id.') a
                group by a.client_type';
        
        $rs = $this->_dCount->query($sql);
        
        $count_arr = array(
        	'people'=>array('student_count'=>0,
                            'teacher_count'=>0,
                            'parents_count'=>0,
                            'people_total_count'=>0),
            
        );
        foreach($rs as $key=>$val) {
            $count_arr['people']['people_total_count'] += $val['count'];
            if($val['client_type'] == 0) {
                $type = 'student_count';
            }else if($val['client_type'] == 1) {
                $type = 'teacher_count';
            }else if($val['client_type'] == 2) {
                $type = 'parents_count';
            }
            $count_arr['people'][$type] = $val['count'];
            unset($rs[$key]);
        }
        
        $phonearr = $this->getPhoneCountBySchoolId($school_id);
        
        $count_arr['phone'] = $phonearr;
        return $count_arr;
    }
    
    public function getPhoneCountBySchoolId($school_id) {
        if(empty($school_id) || is_array($school_id)) {
            return false;
        }
        $sql = 'select a.client_type,count(*) count  from 
        ( select distinct b.client_account,b.client_type from wmw_client_class b,wmw_class_info c where 

b.class_code=c.class_code and c.school_id='.$school_id.') a,
        wmw_business_phone d
        where a.client_account=d.account_phone_id1 and d.seq_type=0 
        group by a.client_type';
        $rs = $this->_dCount->query($sql);
        $phones_count = array(
                           	't_phone_count'=>0,
                            'f_phone_count'=>0,
                            'total_phone_count'=>0
                        );
        foreach($rs as $key=>$val) {
            $phones_count['total_phone_count'] += $val['count'];
            if($val['client_type'] == 1) {
                $phones_count['t_phone_count'] = $val['count'];
            }
            if($val['client_type'] == 2) {
                $phones_count['f_phone_count'] = $val['count'];
            }
            unset($rs[$key]);
        }
        
        return $phones_count;
    }
    //bmsend--------------------------------------------------------
    private function getArealist($area_id) {
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
        $wheresql = '';
        if(intval($district_id) > 0) {
        	$area_id = intval($area_id);
        	$wheresql = "a.area_id='$area_id'";
        } elseif(intval($city_id) > 0) {
        	$min_area_id = intval($province_id . $city_id . '000');
        	$max_area_id = intval($province_id . $city_id . '999');
        	$wheresql = "a.area_id >= '$min_area_id' and a.area_id <= '$max_area_id'";
        } elseif(intval($province_id) > 0) {
        	$min_area_id = intval($province_id . '000000');
        	$max_area_id = intval($province_id . '999999');
        	$wheresql = "a.area_id >= '$min_area_id' AND a.area_id <= '$max_area_id'";
        }
        
        return $wheresql;
    }
}