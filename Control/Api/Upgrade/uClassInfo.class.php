<?php
/**
 * 
 * @author Administrator
 * 注明：1. ClassInfo相关的数据打包类已存在于Pack目录下；
 */
class uClassInfo extends uBase {
    protected $level = 1;
    
    //毕业
    public function runGraduate() {
        $classinfo_list = $this->packDatas();
        $classinfo_datas = & $classinfo_list[$this->class_code];
        
        //添加历史信息
        $classinfo_datas['graduation_time'] = time();
        
        $mClassInfoHistory = ClsFactory::Create('Model.mClassInfoHistory');
        $mClassInfoHistory->addClassInfoHistory($classinfo_datas);
        
        //删除原有的班级信息
        $mClassInfo = ClsFactory::Create('Model.mClassInfo');
        $mClassInfo->delClassInfo($this->class_code);
    }
    
    //升级
    public function runUpgrade($next_grade_id) {
        $mClassInfo = ClsFactory::Create('Model.mClassInfo');
        $datas = array(
            'grade_id' => $next_grade_id,
            'upgrade_year' => '%upgrade_year+1%',
        );
        $mClassInfo->modifyClassInfo($datas, $this->class_code);
    }
    
}



