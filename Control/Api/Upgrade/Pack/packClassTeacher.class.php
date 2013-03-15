<?php
/**
 * 主导类的ids为class_code
 * @author Administrator
 *
 */
class packClassTeacher extends packAbstract {
    public function __construct() {
    	$this->addChildNode('class_code', new packClassInfo());
        $this->addChildNode('client_account', new packUser());
        $this->addChildNode('subject_id', new packSubjectInfo());
    }
    
    protected function initInfoList() {
        $mClassTeacher = ClsFactory::Create('Model.mClassTeacher');
        $classteacher_arr = $mClassTeacher->getClassTeacherByClassCode($this->ids);
        $this->info_list = reset($classteacher_arr);
    }
}