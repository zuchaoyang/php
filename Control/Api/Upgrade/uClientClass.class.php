<?php
/**
 * 
 * @author Administrator
 * 注明：1. ClassInfo相关的数据打包类已存在于Pack目录下；
 */
class uClientClass extends uBase {
    protected $level = 3;
    
    //毕业
    public function runGraduate() {
        $clientclass_list = $this->packDatas();
        
        //添加班级的历史信息
        $mClientClassHistory = ClsFactory::Create('Model.mClientClassHistory');
        $mClientClassHistory->addClientClassHistoryBat($clientclass_list);
        
        //删除班级的相关信息
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        foreach((array)$clientclass_list as $clientclass) {
            $client_class_id = $clientclass['client_class_id'];
            $mClientClass->delClientClass($client_class_id);
        }
    }
    
    //升级
    public function runUpgrade($next_grade_id) {
        return true;
    }
    
}

/**
 * 主导类的ids为class_code
 * @author Administrator
 *
 */
class packClientClass extends packAbstract {
    protected function initInfoList() {
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $classteacher_arr = $mClientClass->getClientClassByClassCode($this->ids);
        $this->info_list = reset($classteacher_arr);
    }
}