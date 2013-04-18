<?php
class ClassalbumAction extends SnsController{
    protected $AlbumApi = null;
    public function _initialize() {
        parent::_initialize();
        import('@.Control.Api.AlbumApi');
        $this->AlbumApi = new AlbumApi();
    }
    
    public function albumlist() {
        $class_code = $this->objInput->getInt('class_code');
        $tmp_class_code = $this->check_class_code($class_code);
        if(empty($class_code)) {
            $this->showError('班级不存在！','/Sns/Index');
            exit;
        }
        //检测登陆者是否有编辑的权限
        $is_edit = false;
        $is_stu_edit = false;
        if($tmp_class_code == $class_code && $this->user['client_type'] != CLIENT_TYPE_FAMILY) {
            $class_role = $this->user['client_class'][$class_code]['teacher_class_role'];
            $class_admin = $this->user['client_class'][$class_code]['class_admin'];
            $is_stu_edit = true;
            if(in_array($class_role, array(1,3)) || !empty($class_admin)) {
                $is_edit = true;
            }
        }
        
        $img_file_url = '/Public/wmw_images/auto_photo_img/wzp.jpg';
        $this->assign('is_edit', $is_edit);
        $this->assign('is_stu_edit', $is_stu_edit);
        $this->assign('class_code', $class_code);
        $this->assign('client_account', $this->user['client_account']);
        $this->assign('no_photo_img', $img_file_url);
        
        $this->display('class_list_album');
    }
    
    public function loadMoreAlbum(){
        $class_code = $this->objInput->getInt('class_code');
        $page = $this->objInput->getInt('page');
        $limit = 4;
        $offset = null;
        $page = max(1,$page);
        $offset = ($page-1)*$limit;
        if(empty($class_code)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $album_list = $this->AlbumApi->getClassAlbumListByClassCode($class_code, $offset, $limit);
        if(empty($album_list)) {
            $this->ajaxReturn(null, '没有了', -1, 'json');
        }
        
        $this->ajaxReturn($album_list, '', 1, 'json');
    }
    /**
     * 获取班级相册信息
     */
    public function getAlbum() {
        $album_id = $this->objInput->getInt('album_id');
        $class_code = $this->objInput->getInt('class_code');
        if(empty($album_id)) {
            $this->ajaxReturn('', '', -1, 'json');
        }
        
        //获取相处信息
        $rs = $this->AlbumApi->getClassAlbumByAlbumId($album_id, $class_code);
       
        if(empty($rs)) {
            $this->ajaxReturn($rs, '', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '', 1, 'json');
    }
    
    /**
     * 创建班级相册
     */
    public function createAlbum() {
        $album_name      = $this->objInput->postStr('album_name');
        $explain         = $this->objInput->postStr('album_explain');
        $grant           = $this->objInput->postInt('grant_sel');
        $class_code      = $this->objInput->postInt('class_code');
        $client_account  = $this->objInput->postInt('client_account');
        
        $data_arr = array(
            'album_name'=>$album_name,
            'album_explain'=>$explain,
            'grant'=>$grant,
            'uid'=>$client_account,
        	'class_code'=>$class_code
        );
        if(empty($data_arr)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $album_id = $this->AlbumApi->addClassAlbum($data_arr);
        if(empty($album_id)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        //添加动态
        import("@.Control.Api.FeedApi");
        $feed_api = new FeedApi();
        $feed_id = $feed_api->class_create($class_code,$this->user['client_account'],$album_id,FEED_ALBUM, FEED_ACTION_PUBLISH);
        
        $this->ajaxReturn($album_id, '', 1, 'json');
    }
    
    /**
     * 修改相册信息
     */
    public function updAlbum() {
        $album_id   = $this->objInput->postInt('album_id');
        $album_name = $this->objInput->postStr('album_name');
        $explain    = $this->objInput->postStr('album_explain');
        $grant      = $this->objInput->postInt('grant');
        $class_code = $this->objInput->postInt('class_code');
        $uid        = $this->objInput->postInt('client_account');
        $album_data = array(
            'album_name'=>$album_name,
            'album_explain'=>$explain,
            'grant'=>$grant,
            'upd_account'=>$uid,
        	'upd_time'=>time()
        );
        
        $rs = $this->AlbumApi->updClassAlbum($album_data, $album_id, $class_code);
        if(empty($rs)) {
            $this->ajaxReturn($rs, '修改失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '修改成功', 1, 'json');
    }
    
    /**
     * 删除相册
     */
    public function delClassAlbum() {
        $album_id = $this->objInput->getInt('album_id');
        $class_code = $this->objInput->getInt('class_code');
        if(empty($album_id) && empty($class_code)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $rs = $this->AlbumApi->delClassAlbum($album_id, $class_code);
        if(empty($rs)) {
            $this->ajaxReturn($rs, '删除失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '删除成功', 1, 'json');
    }
    /**
     * 获取相册权限列表
     */
    public function getClassGrantList() {
        $rs = $this->AlbumApi->getClassGrantList();
        if(empty($rs)) {
            $this->ajaxReturn($rs, '', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '', 1, 'json');
    }
    /**
     * 更新相册封面
     */
    public function setAlbumImg() {
        $album_id = $this->objInput->postInt('album_id');
        $album_img = $this->objInput->postStr('album_img');
        $class_code = $this->objInput->postInt('class_code');
        if(empty($album_id) && empty($album_img)) {
            $this->ajaxReturn('', '更新失败', -1, 'json');
        }
        if(!$this->check_class_code($class_code)) {
            $this->ajaxReturn('', '更新失败', -1, 'json');
        }
        $album_data = array('album_img'=>$album_img);
        $rs = $this->AlbumApi->updClassAlbum($album_data, $album_id, $class_code);
        if(empty($rs)) {
            $this->ajaxReturn(null, '更新失败', -1, 'json');
        }
        $this->ajaxReturn($rs, '更新成功', 1, 'json');
    }
    /**
     * 检测班级编号是否存在
     * @param int $class_code
     * 
     * @return  $class_code为存在，false为不存在
     */
    private function check_class_code($class_code) {
        //获取当前用户所有班级编号
        $class_codes = array_keys($this->user['client_class']);
        if(empty($class_codes)) {
            return false;
        }
        if(in_array($class_code, $class_codes)) {
            return $class_code;
        }else{
            //获取默认班级编号
            $class_code = reset($class_codes);
        }
        
        return $class_code;
    }
    
    
    
    
}