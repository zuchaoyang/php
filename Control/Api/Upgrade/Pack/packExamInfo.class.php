<?php
/**
 * 成绩表 主导类的ids为class_code
 * @author Administrator
 *
 */
class packExamInfo extends packAbstract {
    public function __construct() {
    	$this->addChildNode('class_code', new packClassInfo());
    	$this->addChildNode('subject_id', new packSubjectInfo());
    }
    
    protected function initInfoList() {
        $mExamInfo = ClsFactory::Create('Model.mExamInfo');
        $ExamInfo_arr = $mExamInfo->getExamInfoByClassCodeTO($this->ids);
        
        $this->info_list = reset($ExamInfo_arr);
    }
}