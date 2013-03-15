<?php
class dBusinessphone extends dBase {

	protected $_tablename = null;
	protected $_fields = array ();
	protected $_pk = null;
	protected $_index_list = array();
	
	protected $_flag = true;
	
	public function switchToBusinessPhone() {
	    $this->_tablename = 'wmw_business_phone';
	    $this->_fields = array(
	    	'account_phone_id1', 
			'account_phone_id2', 
			'seq_type',
			'dbcreatetime', 
			'dbupdatetime' 
	    );
	    $this->_pk = 'account_phone_id1';
	    $this->_index_list = array(
	        'account_phone_id1'
	    );
	}
	
	public function switchToPhoneInfo() {
	    $this->_tablename = 'wmw_phone_info';
	    $this->_fields = array(
	    	'phone_id', 
			'business_enable_time', 
			'business_enable', 
			'phone_status', 
			'flag', 
			'dbcreatetime', 
			'dbupdatetime', 
			'phone_type'
	    );
	    $this->_pk = 'phone_id';
	    $this->_index_list = array(
	        'phone_id'
	    );
	}
	
	public function switchToBusinessPhoneLog() {
		$this->_tablename = 'wmw_business_phone_log';
	    $this->_fields = array(
	    	'wbp_log_id', 
			'wbp_log_bnum', 
			'wbp_log_phone', 
			'wbp_log_begtime', 
			'wbp_log_name', 
			'wbp_log_type', 
			'wbp_log_flag', 
			'wbp_log_opername', 
			'wbp_log_opertime' 
	    );
	    $this->_pk = 'wbp_log_id';
	    $this->_index_list = array(
	        'wbp_log_id'
	    );
	}
	
	public function switchToBusinessPhoneErrorLog() {
		$this->_tablename = 'wmw_business_phone_error_log';
	    $this->_fields = array(
	    	'wbp_log_id', 
			'wbp_log_bnum', 
			'wbp_log_phone', 
			'wbp_log_begtime', 
			'wbp_log_error_content', 
			'wbp_log_error_flag', 
			'wbp_log_error_type', 
			'client_ip' 
	    );
	    $this->_pk = 'wbp_log_id';
	    $this->_index_list = array(
	        'wbp_log_id'
	    );
	}
	
	public function addWwwBussinessPhoneErrorLog($datas, $is_return_id = false) {
	    $this->switchToBusinessPhoneErrorLog();
	    
	    return $this->add($datas, $is_return_id);
	}

	//添加联通日志信息
	public function addWmwBusinessPhoneLog($datas, $is_return_id = false) {
	    $this->switchToBusinessPhoneLog();
	    
	    return $this->add($datas, $is_return_id);
	}

	/**
	 * 通过主键获取手机业务信息的代码调整
	 * @param $account_phone_ids
	 * @author anlicheng
	 */
	public function getbusinessphonebyalias_id($account_phone_ids) {
		if (empty ( $account_phone_ids )) {
			return false;
		}
		
		$businessphone_list = $this->getBusinessPhone($account_phone_ids);
		$new_businessphone_list = $phonenum_list = $phoneinfo_list = array ();
		if (! empty ( $businessphone_list )) {
			foreach ( $businessphone_list as $key1=>$businessphone ) {
				$id1 = $businessphone ['account_phone_id1'];
				$id2 = $businessphone ['account_phone_id2'];
				//分离手机号码信息
				if (preg_match ( "/^1[1-9]{1}[0-9]{9}$/", $id1 )) {
					$phone_id = $id1;
					$uid = $id2;
				} else {
					$phone_id = $id2;
					$uid = $id1;
				}
				$phonenum_list [] = $phone_id;

				$businessphone ['uid'] = $uid;
				$businessphone ['phone_id'] = $phone_id;

				$new_businessphone_list [$id1] = $businessphone;
			}
		}

		
		//根据手机号码获取手机信息
		if (! empty ( $phonenum_list )) {
			$phonenum_list = array_unique ( $phonenum_list );
			$phoneinfo_list = $this->getphoneinfobyphonenum ( $phonenum_list );
		}

		//数据整合将相关的数据合并
		if (! empty ( $new_businessphone_list ) && ! empty ( $phoneinfo_list )) {
			foreach ( $new_businessphone_list as $key => $businessphone ) {
				$phone_id = $businessphone ['phone_id'];
				if (isset ( $phoneinfo_list [$phone_id] )) {
					$businessphone = array_merge ( $businessphone, $phoneinfo_list [$phone_id] );
					$businessphone ['type'] = $businessphone ['seqtype'] = $businessphone ['seq_type'];

					unset ( $new_businessphone_list[$key]['dbcreatetime'], $new_businessphone_list[$key]['dbupdatetime'] );
				}
				$new_businessphone_list [$key] = $businessphone;
			}
		}

		return !empty ( $new_businessphone_list ) ? $new_businessphone_list : false;
	}

	//通过手机号获得信息
	public function getBusinessPhone($ids) {
	    $this->switchToBusinessPhone();
	    return $this->getInfoByPk($ids);
	}

	function getphoneinfobyphonenum($phone_ids) {
		if (empty ( $phone_ids )) {
			return false;
		}
		
		//过滤掉不合法的手机号信息
		$phone_ids = (array)$phone_ids;
		foreach($phone_ids as $key=>$phone_id) {
		    if(!preg_match ( "/^1[1-9]{1}[0-9]{9}$/", $phone_id )) {
		        unset($phone_ids[$key]);
		    }
		}
		
		$this->switchToPhoneInfo();
		return $this->getInfoByPk($phone_ids);
	}

	public function addbusinessphone($datas) {
	    $this->switchToBusinessPhone();
	    
	    return $this->addBat($datas);
	}

	//todo  批量添加
	function addphoneinfo($dataarr) {
	    $this->switchToPhoneInfo();
	    
	    return $this->addBat($dataarr);
	}
    
	public function modifyphoneinfo($dataarr, $phonenum) {
	    if (!preg_match ( "/^1[1-9]{1}[0-9]{9}$/", $phonenum)) {
			return false;
		}
		
	    $this->switchToPhoneInfo();

	    return $this->modify($dataarr, $phonenum);
	    
	}

	//解析一维或二维数组得到keystr
	private function getkeystr($data) {
		if (empty ( $data ) || !is_array ( $data )){
			return false;
		}
			
		foreach ( $data as $keys ) {
			if (! empty ( $keys ) && is_array ( $keys )) {
				$key = array_keys ( $keys );
				break;
			} else {
				$key = array_keys ( $data );
				break;
			}
		}
		
		return implode ( ',', $key );
	}
	
	//todolist 非规范代码
	public function modifyBusinessPhone($datas, $account_phone_id1) {
	    $this->switchToBusinessPhone();
	    
	    return $this->modify($datas, $account_phone_id1);
	}

	//账号转换成手机号码
	public function changeuidtophonenum($uids) {
		if (empty ( $uids )) {
			return false;
		}
		
		$phonenum_uids = $this->getBusinessPhone($uids);
		if (!empty($phonenum_uids)) {
    		foreach ( $phonenum_uids as $phonenum_uidinfo ) {
    			$phonenums [$phonenum_uidinfo ['account_phone_id1']] = $phonenum_uidinfo ['account_phone_id2'];
    		}
		}
		
		return !empty ( $phonenums ) ? $phonenums : false;
	}

	//解析一维或二维数组得到valuestr
	private function joinstr($data) {
		if (empty ( $data ) || !is_array ( $data ))
			return false;
		foreach ( $data as $value ) {
			if (empty ( $value ))
				continue;
			$valuestr .= '(';
			if (! empty ( $value ) && is_array ( $value )) {
				$valuestr .= implode ( ',', array_values ( $value ) );
				$key = array_keys ( $value );
			} else {
				$key = array_keys ( $data );
				break;
			}
			$valuestr .= '),';
		}
		$valuestr = substr ( $valuestr, 0, - 1 );
		$keystr = '(' . $this->getkeystr ( $data ) . ')';
		
		return $keystr . ' values ' . $valuestr;
	}

	public function batchmangebusinessphone($dataarr) {
		$uids = array ();
		if (empty ( $dataarr ) && ! is_array ( $dataarr )) {
			return false;
		}
		
		foreach ( $dataarr ['START'] as $val ) {
			$uidall [] = $val ['business_num'];
		}
		
		$i = 0;
		$a = 1;
		$phoneinfostart = array();
		
		foreach ( $dataarr ['START'] as $key => $data ) {
			$businessinfo = $this->getbusinessphonebyalias_id ( $data ['business_num'] );
			$businessinfos = $this->getbusinessphonebyalias_id ( $data ['mphone_num'] );
			//调整opening_time的时间数据格式
			$this->startTrans ();
			if (! empty ( $businessinfos ) || ! empty ( $businessinfo )) {
				$phoneinfo = array (
					'phone_id' => $data ['mphone_num'],
					'business_enable_time' => strtotime ( $data ['opening_time'] . '000000' ),
					'phone_status' => 1, 
					'business_enable' => 1,
					'dbupdatetime' => date ( 'Y-m-d H:i:s' ),
					'phone_type' => ! empty ( $data ['phone_type'] ) ? $data ['phone_type'] : 2,
				);
				
				$result_1 = $this->modifyphoneinfo ( $phoneinfo, $businessinfo[$data['business_num']]['phone_id'] );

				$result_2 = $this->delBusinessPhone ( $data ['business_num'] );
				
				$businessinfostart [$i++] = array (
    				'account_phone_id1' => $data ['business_num'], 
    				'account_phone_id2' => $data ['mphone_num'], 
    				'dbupdatetime' =>  date ( "Y-m-d H:i:s", strtotime ( $data ['opening_time'] . '000000' ) ), 
    				'seq_type' => 0,
				);
				$businessinfostart [$i++] = array (
    				'account_phone_id1' => $data ['mphone_num'], 
    				'account_phone_id2' => $data ['business_num'], 
    				'dbupdatetime' =>  date ( "Y-m-d H:i:s", strtotime ( $data ['opening_time'] . '000000' ) ), 
    				'seq_type' => 1,
				);
				
				if (!$result_1 || !$result_2) {
					$this->_flag = false;
					break;
				} 
				$uids[] = $data ['business_num'];
				
			} else{
			    
				$businessinfostart [$i++] = array (
    				'account_phone_id1' => $data ['business_num'], 
    				'account_phone_id2' => $data ['mphone_num'], 
    				'dbcreatetime' => date ( "Y-m-d H:i:s", strtotime ( $data ['opening_time'] . '000000' ) ),
					'dbupdatetime' => date ( "Y-m-d H:i:s", strtotime ( $data ['opening_time'] . '000000' ) ), 
    				'seq_type' => 0,
				);
				$businessinfostart [$i++] = array (
    				'account_phone_id1' => $data ['mphone_num'], 
    				'account_phone_id2' => $data ['business_num'], 
    				'dbcreatetime' => date ( "Y-m-d H:i:s", strtotime ( $data ['opening_time'] . '000000' ) ),
					'dbupdatetime' => date ( "Y-m-d H:i:s", strtotime ( $data ['opening_time'] . '000000' ) ), 
    				'seq_type' => 1,
				);

				$phoneinfostart[] = array (
					'phone_id' => $data ['mphone_num'],
					'business_enable_time' => strtotime ( $data ['opening_time'] . '000000' ),
					'phone_status' => 1,
					'dbcreatetime' => date ( 'Y-m-d H:i:s' ),
					'business_enable' => 1, 
					'flag' => 0, 
					'phone_type' => ! empty ( $data ['phone_type'] ) ? $data ['phone_type'] : 1
				);
				$dataloginfo = array (
					'wbp_log_bnum' => $data ['business_num'],
					'wbp_log_phone' => $data ['mphone_num'],
					'wbp_log_begtime' => date ( "Y-m-d H:i:s", strtotime ( $data ['opening_time'] . '000000' ) ),
					'wbp_log_name' => $data ['mphone_user_name'],
					'wbp_log_type' => 1, 
					'wbp_log_flag' => $data ['wbp_log_flag'],
					'wbp_log_opername' => $data ['client_account'], 
					'wbp_log_opertime' => date ( 'Y-m-d H:i:s' )
				);
				$this->addWmwBusinessPhoneLog ( $dataloginfo );
				$uids [] = $data ['business_num'];
			}
		}

	    //取消手机业务
	    foreach ($dataarr['CANCEL'] as $data) {
	       if ($data['notify_type'] == 'CANCEL') {
                $phoneinfo = array(
                    'phone_id'=>$data['mphone_num'],
                    'dateline'=>$data['opening_time'],
                    'phone_status'=>1,
                    'business_enable'=>2,
                    'phone_type'=>$data['phone_type']
                );
    	    }
	        $errordata=array(
                'wbp_log_bnum' => $data['business_num'],
                'wbp_log_phone' => $data['mphone_num'],
	            'wbp_log_begtime' => date("Y-m-d H:i:s"),
                'wbp_log_error_content' => $dataarr['XMLCONTENT'],
	            'wbp_log_error_type' => 2,
                'client_ip' => $_SERVER["REMOTE_ADDR"]
            );
            $this->addWwwBussinessPhoneErrorLog($errordata);
    	    error_log(date("Y-m-d H:i:s")."\tCANCEL-----用户ID".$data['business_num']."=======手机号码：".$data['mphone_num']."canceltime:".$data['opening_time']."取消手机业务\n",3,WEB_ROOT_DIR."/Logs/PhoneError/".date('y_m_d').'.log');
	    }
	    
		$result1 = $this->addbusinessphone( $businessinfostart );
		
		if(!empty($phoneinfostart)){
			$result2 = $this->addphoneinfo( $phoneinfostart );
		}
		
		$uid_faile = array_diff ((array)$uidall, (array)$uids );
		$uid = array (
    		'success' => $uids,
    		'faile' => $uid_faile 
		);

		if ($this->_flag && $result1) {
			$this->commit ();
			return $uid;
		}
		
		$this->rollback ();
		return false;
	}

	//删除手机账号绑定信息
	//todolist 特殊业务处理
	public function delBusinessPhone($phone_del) {
		if (empty ( $phone_del )) {
			return false;
		}
		
		$this->switchToBusinessPhone();
		$phone_del = is_array ( $phone_del ) ? $phone_del : array ($phone_del );
		$phoneStr = implode ( ',', $phone_del );
		$result = $this->execute ( "delete from $this->_tablename where account_phone_id1 in({$phoneStr}) or account_phone_id2 in({$phoneStr})" );
		
		return $result;
	}
	
	//todolist 是否有业务涉及到批量删除
	public function delPhoneInfo($phone_id) {
	    $this->switchToPhoneInfo();
	    
	    return $this->delete($phone_id);
	}
	
	protected function delPhoneInfoBat($phone_ids) {
	    if(empty($phone_ids)) {
	       return false; 
	    }
	    $this->switchToPhoneInfo();
	    
	    $phone_ids = (array)$phone_ids;
	    
	    $total_effect_rows = 0;
	    foreach($phone_ids as $phone_id) {
	        $effect_row = $this->delPhoneInfo($phone_id);
	        $effect_row && $total_effect_rows++;
	    }
	    
	    return $total_effect_rows;
	}

	/*
	 *ams绑定手机号（表单提交过来的数据包含添加修改和删除等操作，且为批量，采用事务处理，防止修改失败时数据丢失。）
	 */
	public function bindingTransaction($phone_del, $phone_add, $accounPone_add) {	
		if (empty ( $phone_del ) && empty ( $phone_add ) && empty ( $accounPone_add )) {
			return false;
		} elseif ((empty ( $phone_add ) && ! empty ( $accounPone_add )) || ((! empty ( $phone_add ) && empty ( $accounPone_add )))) { //两张表操作必须同步
			return false;
		} elseif (empty ( $phone_del ) && ! empty ( $phone_add ) && ! empty ( $accounPone_add )) { //添加数据，无删除数据
			$this->startTrans (); //事务开始
			$delBPResult = $delPIResult = true;
			$exitPhoneInfo = $this->getbusinessphonebyalias_id ( $phone_add ); //检查将要添加的数据在库中是否已存在
			
			if (! empty ( $exitPhoneInfo )) {
				echo "绑定失败!<br/>";
				foreach ( $exitPhoneInfo as $phone => $phoneInfo ) {
					echo "手机号【{$phone}】已被账号【{$phoneInfo['uid']}】绑定！<br/>";
				}
				// echo "请<a href='#3'>返回</a>重新输入<br/>";
				exit;
			} else {					
				$addResult = $this->batchmangebusinessphone ( $accounPone_add );
			}
			
		} elseif (! empty ( $phone_del ) && empty ( $phone_add ) && empty ( $accounPone_add )) { // 删除数据 , 无添加数据
			$this->startTrans (); //事务开始
			$addResult = true;
			$delBPResult = $this->delBusinessPhone ( $phone_del );
			$delPIResult = $this->delPhoneInfoBat ( $phone_del );
		} elseif (! empty ( $phone_del ) && ! empty ( $phone_add ) && ! empty ( $accounPone_add )) {
			$this->startTrans (); //事务开始
			$delBPResult = $this->delBusinessPhone ( $phone_del );
			$delPIResult = $this->delPhoneInfoBat ( $phone_del );
			$exitPhoneInfo = $this->getbusinessphonebyalias_id ( $phone_add );
			
			if (! empty ( $exitPhoneInfo )) {
				echo "绑定失败!<br/>";
				foreach ( $exitPhoneInfo as $phone => $phoneInfo ) {
					echo "手机号【{$phone}】已被账号【{$phoneInfo['uid']}】绑定！<br/>";
				}
				//echo "请<a href='#3'>返回</a>重新输入<br/>";
				exit;
			} else {
				$addResult = $this->batchmangebusinessphone ( $accounPone_add );
			}
		}

		if ($delBPResult && $delPIResult && $addResult) {
			$this->commit (); //执行事务
			return true;
		}
		
		$this->rollback (); //回滚事务
		return false;
	}

	//wms 对手机绑定用户的统计
	//todolist 特殊业务
	public function phoneusernum($type){
		if(empty($type)){
			return false;
		}
		$this->switchToPhoneInfo();
		
	    $type_arr = array('1', '2');
	    $type = in_array($type, $type_arr) ? $type : array_shift($type_arr);
	    $sql = "select count(*) user_num from $this->_tablename where phone_type = $type";
	    if($type==1){
	    	$sql .= " or phone_type = ''";
	    }
	    $rs = $this->query($sql);
	    
	    return !empty($rs) ? array_shift($rs) : false;
	}

}

