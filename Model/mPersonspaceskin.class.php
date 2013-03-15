<?php
class mPersonspaceskin extends mBase {
	
    protected $_dPersonspaceskin = null;
    
    public function __construct() {
        $this->_dPersonspaceskin = ClsFactory::Create('Data.dPersonspaceskin');
    }
    
    /**
    * 按用户类别读取用户空间模版信息
    * @param $client_type 允许值：0,1,2,3
    */
	public function getPersonSpaceSkinByClientType($client_type) {
	    
	    if(empty($client_type)) {
	        $client_type = 0;
	    }
	    $wheresql = "use_type in('" . implode("','", (array)$client_type) . "')";
	    $personspaceskin_list = $this->_dPersonspaceskin->getInfo($wheresql);
	    
	    $new_personspaceskin_list = array();
	    if(!empty($personspaceskin_list)) {
	        foreach($personspaceskin_list as $skin_id=>$skin) {
	            $new_personspaceskin_list[$skin['use_type']][$skin_id] = $skin;
	            unset($personspaceskin_list[$skin_id]);
	        }
	    }
	    
	    return !empty($new_personspaceskin_list) ? $new_personspaceskin_list : false;
	}

    /**
    * 按皮肤ID查找皮肤样式名称
    * @param $skin_id
    */	
	public function getPersonSpaceSkinById($skin_id) {
	    if(empty($skin_id)) {
	        return false;
	    }
	    
		return $this->_dPersonspaceskin->getPersonSpaceSkinById($skin_id);
	}
}
