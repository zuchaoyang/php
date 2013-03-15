<?php
class mUnicomInterface extends mBase {
	//联通HTTP地址
	private $_url =  'http://125.211.218.24:5555/XMLReceiver';
	//post关键词
	private $uniWord = 'xmldata';
	//xml标签
	private $_UniBssArr = array(
		'OrigDomain',
	    'HomeDomain',
	    'BIPCode',
	    'BIPVer',
	    'ActivityCode',
	    'ActionCode',
	    'ActionRelation',
	    'Routing',
	    'ProcID',
	    'TransIDO',
	    'TransIDH',
	    'ProcessTime',
	    'Response',
	    'SPReserve',
	    'TestFlag',
	    'MsgSender',
	    'MsgReceiver',
	    'SvcContVer',
	    'SvcCont'
	);
	
	private $_SvcContArr = array(
		'RespCode',
    	'RespDesc'

	);
	
	//发送post数据根方法
    public function sendMessage($url, $postdata) {
    	if (empty($postdata) && !isset($postdata)) {
    		return false;
    	}
    	
    	$postDataArr = array(
    		$this->uniWord => $postdata
    	);
        $dSms = ClsFactory::Create('Data.dUnicomInterface');
        $getXmlStr = $dSms->sendMessage($url, $postDataArr);
        
        return $getXmlStr ? $getXmlStr : false;
    }
    
    //给联通要发送的xml数据（组建）
	public function buildRequestXml($phoneNum) {
		if (empty($phoneNum) || !isset($phoneNum)) {
			return false;
		}

		//手机号
		//20位随机数
		$randTransIDO = date('s',time()).rand(1000,9999).rand(1000,9999).time();
		//请求的发送时间
		$ProcessTime = date('YmdHis',time());
		//当前日期
		$CutOffDay = date('Ymd',time());
		$xml = "<?xml version='1.0' encoding='UTF-8'?><UniBSS></UniBSS>";
		$xmlr = "<?xml version='1.0' encoding='UTF-8'?><UserStateReq></UserStateReq>";
		$xmlrequest = new SimpleXMLElement($xml);
		$xmlrequest->addChild('OrigDomain','ECIP');
		$xmlrequest->addChild('HomeDomain','UCRM');
		$xmlrequest->addChild('BIPCode','BIPWO001');
		$xmlrequest->addChild('BIPVer','0100');
		$xmlrequest->addChild('ActivityCode','T0000001');
		$xmlrequest->addChild('ActionCode','0');
		$xmlrequest->addChild('ActionRelation','0');
		$xmlrequest->addChild('Routing','');
		$xmlrequest->Routing->addChild('RouteType','01');
		$xmlrequest->Routing->addChild('RouteValue',$phoneNum);
		$xmlrequest->addChild('ProcID','yh000522010110500000480002430');
		$xmlrequest->addChild('TransIDO',$randTransIDO);
		$xmlrequest->addChild('TransIDH','');
		$xmlrequest->addChild('ProcessTime',$ProcessTime);
		$xmlrequest->addChild('SPReserve','');
		$xmlrequest->SPReserve->addChild('TransIDC','ECIP0002yh000522010110500000480048253');
		$xmlrequest->SPReserve->addChild('CutOffDay',$CutOffDay);
		$xmlrequest->SPReserve->addChild('OSNDUNS','0002');
		$xmlrequest->SPReserve->addChild('HSNDUNS','3400');
		$xmlrequest->SPReserve->addChild('ConvID','ECIPpsns2002yh00052201011050000048004825320101105001207430');
		$xmlrequest->addChild('TestFlag','1');
		$xmlrequest->addChild('MsgSender','3400');
		$xmlrequest->addChild('MsgReceiver','3401');
		$xmlrequest->addChild('SvcContVer','0100');
		$xmlresult = new SimpleXMLElement($xmlr);
		$xmlresult->addChild('UserNumber',$phoneNum);
		$SvcCont = strval('<![CDATA['.$xmlresult->asXML().']]>');
		$xmlrequest->addChild('SvcCont',$SvcCont);
		$str = html_entity_decode($xmlrequest->asXML(),ENT_NOQUOTES);
		return $str;
	}
	
	//给联通发送数据并得到联通返回的值的方法
	public function sendToUni($phoneNum) {
		$xml = $this->buildRequestXml($phoneNum);
		$getUniXml = $this->sendMessage($this->_url,$xml);
		
		return $getUniXml;
	}
	
    //解析接收到联通的xml数据要给我们返回的数据
    public function getPhoneStatus($phoneNum) {
    	$flag = preg_match("/^1[1-9][0-9]{9}$/",$phoneNum);
		if (!$flag) {
			return false;
		}
		
    	//解析xml
    	$xmlstr = $this->sendToUni($phoneNum);
    	if (empty($xmlstr)) {
    		return false;
    	}
    	
    	try{
			$xml3 = new SimpleXMLElement(html_entity_decode($xmlstr));
			if (!is_object($xml3->SvcCont)) {
				return false;
			}
			$xml4 = new SimpleXMLElement($xml3->SvcCont);
    	}
		catch(Exception $e) {
			return false;
		}
		
     	//接收到数据的一系列验证（格式，请求成功与失败，手机号是否一致）
        $result = 1;
		if ($xml3->Response->RspCode != '0000') {
		    $result = -1;
            error_log(date("Y-m-d H:i:s")."\t请求服务器失败。\n",3,WEB_ROOT_DIR."\\Logs\\PhoneError\\".date('y-m-d').".log");
		} else {
		    if ($xml3->Routing->RouteValue != $phoneNum) {
                $result = -2;
                error_log(date("Y-m-d H:i:s")."\t请求手机号吗与返回手机号码不一致。\n",3,WEB_ROOT_DIR."\\Logs\\PhoneError\\".date('y-m-d').".log");
    		} else {
                $getResult1 = $this->object_to_array($xml3);
        		$rs1 = $this->checked_fields($getResult1,$this->_UniBssArr);
        		$getResult = $this->object_to_array($xml4);
        		$rs2 = $this->checked_fields($getResult,$this->_SvcContArr);
        		if (!$rs1 || !$rs2) {
                    $result = -3;
                    error_log(date("Y-m-d H:i:s")."\tXML格式不正确。\n",3,WEB_ROOT_DIR."\\Logs\\PhoneError\\".date('y-m-d').".log");
        		}
    		}
		}


		return ($result>0) ? $getResult['RespCode'] : false;
    }

	//取得xml并解析里面的内容
	function getXmlVal($xmlcontent) {
		$checked_fields = array (
			'mphone_num',
			'business_num',
			'mphone_user_name',
			'opening_time',
			'notify_type'
		);
		
		if (empty ( $xmlcontent )) {
			return false;
		}
		
		$flag = true;
		try {
			$xmlcontent = htmlspecialchars_decode($xmlcontent,ENT_QUOTES);
			$xml = new SimpleXMLElement ($xmlcontent);
			if ($xml->getName () != 'data' || $xml->children ()->getName () != 'item') {
				$flag = false;
			}
		} catch ( Exception $e ) {
			echo $e->getMessage();
			$flag = false;
		}
		
		if ($flag) {
		    $info = array();
			foreach ( $xml->children () as $datacontent ) {
				$datas = $this->object_to_array ( $datacontent );
				$flag = preg_match("/^1[1-9][0-9]{9}$/",$datas['mphone_num'],$arr);
				if (!$flag) {
					$flag = false;
					break;
				}
				$checkresult = $this->checked_fields ( $datas, $checked_fields );
				if (empty ( $datas ) || !$checkresult) {
					$flag = false;
					break;
				}
                if ($datas['notify_type'] == 'CANCEL') {
                    $info ['CANCEL'][] = $datas;
                } elseif ($datas['notify_type'] == 'START') {
                    $info ['START'][] = $datas;
                }
			}
			
			$info['XMLCONTENT'] = $xmlcontent;
			$mBusinessphone = ClsFactory::Create('Model.mBusinessphone');
            $rs = $mBusinessphone->batchmangebusinessphone($info);
            if (empty($rs) && $flag) {
                $result = array(
    				'result'=>true,
    				'info'=>$info
			    );
            } else {
                $result = array(
    				'result'=>false,
    				'info'=>$info
			    );
            }
		}
		
		return $result;
	}

	//组建data 返回的 xml
	function buildresultXml($result = 'false', $err_code = '') {
		$xml = new SimpleXMLElement ( "<?xml version='1.0' encoding='UTF-8'?><data></data>" );
		$xml->addChild ( 'result', $result );
		$xml->addChild ( 'err_code', '' );
		return $xml->asXML ();
	}

	//把xml对象转换成数组
	function object_to_array($obj) {
		if (empty ( $obj )) {
			return false;
		}
		
		$obj_arr = is_object ( $obj ) ? get_object_vars ( $obj ) : $obj;
		foreach ( $obj_arr as $key => $val ) {
			if (is_object ( $val )) {
				$val = $this->object_to_array ( $val );
			}
			$obj_arr [$key] = $val;
		}
		
		return $obj_arr;
	}

	//检查传过来的xml的格式是否正确
	function checked_fields($datas, $fields, $joinstr = "!--!") {
		if (empty ( $datas )) {
			return false;
		}

		if (empty ( $joinstr )) {
			$joinstr = '!--!';
		}
		
		$keys = array_keys ( $datas );
		$datas_keys_str = implode ( $joinstr, $keys );
		$fields_str = implode ( $joinstr, $fields );
		return $datas_keys_str == $fields_str ? true : false;
	}
}