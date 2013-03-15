<?php
interface UploadfileInterface {
    /** 
     * 上传文件
     * @param $field 上传文件表单名称
     * @param $options 上传配制文件属性数组
     * @return boolean
     */
    public function upfile($field, $options = array());
    
    
    public function _set_options($options = array());
    
    /** 
     * 取得错误信息
     * @param void
     * @return boolean
     */
    public function get_error();
    /** 
     * 显示错误信息
     * @param $msg 错误信息
     * @return void
     */
    public function error($msg);
}
?>
