<?php
class IndexAction extends SnsController{
    public function _initialize(){
        parent::_initialize();
    }
    
    public function index(){
        $class_code = $this->checkoutClassCode($class_code);
        $this->redirect("/Sns/ClassIndex/Index/index/class_code/" . $class_code);
    }
    
    public function getTemplate() {
        $this->display(WEB_ROOT_DIR . '/View/Template/Public/Tip/commontip.html');
    }
}