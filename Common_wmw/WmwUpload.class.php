<?php
define('WMW_UPLOAD_DIR', dirname(__FILE__));
//引入公共接口
include_once WMW_UPLOAD_DIR . "/Vendor/Upload/UploadfileInterface.php";
include_once WMW_UPLOAD_DIR . "/Vendor/WmwAutoLoader.class.php";

class WmwUpload implements UploadfileInterface {
    private $objUpload = null;
    
    public function __construct() {
        $this->objUpload = new Uploadfile();
    }
    
     /** 
     * 上传文件
     * @param $field 上传文件表单名称
     * @param $options 上传配制文件属性数组
     *        array(
     *        	'allow_type' => array (
     *              0 => "jpg",
     *              1 => "gif",
     *              2 => "png"
     *           ),
     *           
     *           //文件的绝对路径
     *           'path'=>'',
     *           
     *           //上传附件目录
     *           'attachmentspath' => ''
     *           
     *           //重命名后的名字, 不包含扩展名
     *		    'newname' => '',
     *           
     *          //上传文件最大限制，单位:kb
     *           'max_size' => "2048",
     *           
     *           //如果有相同文件存在，是否覆盖, true表示覆盖，false表示不覆盖
     *           'overwrite' => true | false,
     *        
     *          //是否重新命名上传文件
     *          'renamed' => true | false,
     *          
     *          //是否生成缩略图
     *          'ifresize' => true | false,
     *          //缩略图宽
     *          'resize_width' => 45,
     *          //缩略高
     *          'resize_height' => 45,
     *        );
     * @return 
     *   成功:
     *   array(
     *       name=>97c300fejw1dydnuchvdlg.gif
     *       type=>application/octet-stream
     *       tmp_name=>/tmp/phpCaY7Ys
     *       error=>0
     *       size=>296559
     *       filename=>/opt/wmw/attachment/photo_pic/11070004/135768866550ecaf590fae3.jpg
     *       getfilename=>/opt/wmw/attachment/photo_pic/11070004/135768866550ecaf590fae3.jpg
     *       ext=>gif
     *       getsmallfilename=>/opt/wmw/attachment/photo_pic/11070004/135768866550ecaf590fae3_small.jpg
     *       md5_key=>7cc01f57ba9842fd22752d59ec8bd872
     *   );
     *   失败:false
     */
    public function upfile($field, $options = array()) {
        return $this->objUpload->upfile($field, $options);
    }
    
    public function _set_options($options = array()) {
        $this->objUpload->_set_options($options);
    }
    
    /** 
     * 取得错误信息
     * @param void
     * @return boolean
     */
    public function get_error() {
        return $this->objUpload->get_error();
    }
    
    /** 
     * 显示错误信息
     * @param $msg 错误信息
     * @return void
     */
    public function error($msg) {
        return $this->objUpload->error($msg);
    }
    
    public function ignore_mine() {
        if(method_exists($this->objUpload, 'ignore_mine')) {
            return $this->objUpload->ignore_mine();
        }
        
        return false;
    }
}