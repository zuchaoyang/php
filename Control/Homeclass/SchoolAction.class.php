<?php
class SchoolAction extends SnsController{

	public function _initialize(){
		parent::_initialize();
	}
	
	//校园大队门户 
	public function Campus(){
       $result =$this->user;
       foreach($result['school_info'] as $value){
          $school_url_old = $value['school_url_old'];
          $school_url_new = $value['school_url_new'];
       }
       if($school_url_new && $school_url_new != '无'){
           $school_url_new = strpos($school_url_new,'http://')===false ? 'http://'.$school_url_new : $school_url_new;
           redirect($school_url_new);
       }
       if($school_url_old){
           $school_url_old = strpos($school_url_old,'http://')===false ? 'http://'.$school_url_old : $school_url_old;
           redirect($school_url_old);
       }
	}

}

