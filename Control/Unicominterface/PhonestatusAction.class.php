<?php
class PhonestatusAction extends Controller {

	public function _initialize() {
		header ( "Content-Type:text/html;charset=utf-8" );
	}

	function managephonestatus() {
		$UnicomInterface = ClsFactory::Create ( 'Model.mUnicomInterface' );
		$phoneinfolist = $this->objInput->postStr('DataInput',false);

		$result = $UnicomInterface->getXmlVal ( html_entity_decode($phoneinfolist));
	    if(empty($phoneinfolist)) {
		    echo $UnicomInterface->buildresultXml ("参数为空");
		    return false;
		}
		if (! empty ( $result['result'] )) {
			echo $UnicomInterface->buildresultXml ( 'true' );
			return false;
		} else {
			echo $UnicomInterface->buildresultXml ();
			return false;
		}
	}

	function index(){
	    $this->display("test");
	}
}