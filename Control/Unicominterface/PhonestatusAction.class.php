<?php
class PhonestatusAction extends Controller {

	public function _initialize() {
		header ( "Content-Type:text/html;charset=utf-8" );
	}

	function managephonestatus() {
		$UnicomInterface = ClsFactory::Create ( 'Model.mUnicomInterface' );
		$phoneinfolist = $this->objInput->postStr('DataInput',false);
		$result = $UnicomInterface->getXmlVal ( html_entity_decode($phoneinfolist));
		if (! empty ( $result['result'] )) {
			echo $UnicomInterface->buildresultXml ( 'true' );
		} else {
			echo $UnicomInterface->buildresultXml ();
		}
	}
}