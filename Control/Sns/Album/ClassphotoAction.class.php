<?php
class ClassphotoAction extends SnsController {
    public function _initialize() {
        parent::_initialize();
    }
   
    /**
     * 班级相册列表
     */
    public function photolist() {
        $class_code = $this->objInput->getInt('class_code');
        $album_id = $this->objInput->getInt('album_id');
        $client_account = $this->objInput->getInt('client_account');
        $login_account = $this->user['client_account'];
        
        if(empty($class_code) || empty($album_id)) {
            $this->showError("数据错误",'/Sns/ClassIndex/Index');exit;
        }
        if(empty($client_account)) {
            $client_account = $this->user['client_account'];
        }
        
        $is_edit = false;
        import("@.Control/Api/AlbumApi");
        $AlbumApi = new AlbumApi();
        $album_list = $AlbumApi->getClassAlbumByAlbumId($album_id, $class_code);
        if(empty($album_list)){
            $this->showError('数据错误','/Sns/ClassIndex/Index');exit;
        }
        $album_list = reset($album_list);
        $grant = $album_list['grant'];
       
        $tmp_class_code = $this->check_class_code($class_code);
        
        if($tmp_class_code == $class_code) {
            $class_role = $this->user['client_class'][$class_code]['teacher_class_role'];
            $class_admin = $this->user['client_class'][$class_code]['class_admin'];
            
            if(in_array($class_role, array(1,3)) || !empty($class_admin) || $login_account==$album_list['add_account']) {
                $is_edit = true;
            }
        }else{
            if($grant !== 0) {
                $this->showError("没有权限查看","/Sns/Album/Classalbum/albumlist/class_code/{$class_code}");exit;
            }
        }
        
        $this->assign('album_list', $album_list);
        $this->assign('is_edit', $is_edit);
        $this->assign('class_code', $class_code);
        $this->assign('album_id', $album_id);
        $this->assign('client_account', $client_account);
        $this->assign('login_account', $login_account);
        
        $this->display('class_list_photo');
    }
    
    
    //瀑布流
    public function photoPlist() {
        $class_code = $this->objInput->getInt('class_code');
        $album_id = $this->objInput->getInt('album_id');
        $client_account = $this->objInput->getInt('client_account');
        $login_account = $this->user['client_account'];
        
        if(empty($class_code) || empty($album_id)) {
            $this->showError("数据错误",'/Sns/ClassIndex/Index');exit;
        }
        if(empty($client_account)) {
            $client_account = $login_account;
        }
        //检测登陆者是否有编辑的权限
        $is_edit = false;
        import("@.Control/Api/AlbumApi");
        $AlbumApi = new AlbumApi();
        $album_list = $AlbumApi->getClassAlbumByAlbumId($album_id, $class_code);
        if(empty($album_list)){
            $this->showError('数据错误','/Sns/ClassIndex/Index');exit;
        }
        $album_list = reset($album_list);
        $grant = $album_list['grant'];
        $tmp_class_code = $this->check_class_code($class_code);
        if($tmp_class_code == $class_code) {
            $class_role = $this->user['client_class'][$class_code]['teacher_class_role'];
            $class_admin = $this->user['client_class'][$class_code]['class_admin'];
            if(in_array($class_role, array(1,3)) || !empty($class_admin) || $login_account==$album_list['add_account']) {
                $is_edit = true;
            }
        }else{
            if($grant !== 0) {
                $this->showError("没有权限查看","/Sns/Album/Classalbum/albumlist/class_code/{$class_code}");exit;
            }
        }
        
        $this->assign('is_edit', $is_edit);
        $this->assign('class_code', $class_code);
        $this->assign('album_id', $album_id);
        $this->assign('client_account', $client_account);
        $this->assign('login_account', $login_account);
        
        $this->display('class_list_photo_p');
    }
    
    /**
     * 获取班级相片
     */
    public function getPhotosByAlbumId() {
        $album_id   = $this->objInput->getInt('album_id');
        $class_code = $this->objInput->getInt('class_code');
        $client_account = $this->objInput->getInt('client_account');
        $page       = $this->objInput->getInt('page');
        $js_page       = $this->objInput->getInt('js_page');
        $offset = null;
        $limit = null;
        if(empty($js_page)) {
            $page = max(1,$page);
            $limit = 10;
            $offset = null;
            $page = max(1,$page);
            $offset = ($page-1)*$limit;
        }
        
        if(empty($class_code)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        
        import("@.Control/Api/AlbumApi");
        $AlbumApi = new AlbumApi();
        $album_list = $AlbumApi->getClassAlbumByAlbumId($album_id, $class_code);
        if(empty($album_list)) {
            $this->ajaxReturn(null, '数据错误', -1, 'json');
        }
        import("@.Control/Api/AlbumImpl/PhotoInfo");
        $PhotoInfo = new PhotoInfo();
        $photo_list = $PhotoInfo->getPhotoListByAlbumId($album_id, $offset, $limit);
//        if(!empty($js_page)) {
//            $count_photos = count($photo_list)-10;
//            $photo_list = array_slice($photo_list,$offset,$count_photos);
//        }
        if(empty($photo_list)) {
            $this->ajaxReturn(null, '没有了', -1, 'json');
        }
        import("@.Common_wmw.Pathmanagement_sns");
        $img_path = Pathmanagement_sns::getAlbum($client_account);
        foreach($photo_list as $photo_id=>$photo_val) {
            $photo_val['big_img'] = $img_path.$photo_val['file_big'];
            $photo_val['middle_img'] = $img_path.$photo_val['file_middle'];
            $photo_val['small_img'] = $img_path.$photo_val['file_small'];
            $photo_val['img_path'] = $img_path;
            $photo_list[$photo_id] = $photo_val;
        }
        $this->ajaxReturn($photo_list, '', 1, 'json');
    }
    
    /**
     * 单张照片
     */
    public function photo() {
        $class_code = $this->objInput->getInt('class_code');
        $album_id = $this->objInput->getInt('album_id');
        $photo_id = $this->objInput->getInt('photo_id');
        $client_account = $this->objInput->getInt('client_account');
        $login_account = $this->user['client_account'];
        
        if(empty($class_code) || empty($photo_id)) {
            $this->showError("数据错误",'/Sns/ClassIndex/Index');exit;
        }
        if(empty($album_id)) {
            import("@.Control/Api/AlbumImpl/PhotoInfo");
            $PhotoInfo = new PhotoInfo();
            $photo_list = $PhotoInfo->getPhotoByPhotoId($photo_id);
            $photo_list = reset($photo_list);
            $album_id = $photo_list['album_id'];
        }
        
        if(empty($client_account)) {
            $client_account = $login_account;
        }
        
        //检测登陆者是否有编辑的权限
        $is_edit = false;
        import("@.Control/Api/AlbumApi");
        $AlbumApi = new AlbumApi();
        $album_list = $AlbumApi->getClassAlbumByAlbumId($album_id, $class_code);
        if(empty($album_list)){
            $this->showError('数据错误','/Sns/ClassIndex/Index');exit;
        }
        $album_list = reset($album_list);
        $grant = $album_list['grant'];
        $tmp_class_code = $this->check_class_code($class_code);
        if($tmp_class_code == $class_code) {
            $class_role = $this->user['client_class'][$class_code]['teacher_class_role'];
            $class_admin = $this->user['client_class'][$class_code]['class_admin'];
            if(in_array($class_role, array(1,3)) || !empty($class_admin) || $login_account==$album_list['add_account']) {
                $is_edit = true;
            }
        }else{
            if($grant !== 0) {
                $this->showError("没有权限查看","/Sns/Album/Classalbum/albumlist/class_code/{$class_code}");exit;
            }
        }
        $this->assign('album', $album_list);
        $this->assign('photo_id', $photo_id);
        $this->assign('is_edit', $is_edit);
        $this->assign('class_code', $class_code);
        $this->assign('album_id', $album_id);
        $this->assign('client_account', $client_account);
        $this->assign('login_account', $login_account);
        
        $this->display('class_photo_show');
    }
    
    /**
     * 上传照片页面
     */
    public function uplaodPhoto() {
        $class_code = $this->objInput->getInt('class_code');
        $album_id = $this->objInput->getInt('album_id');
        $tmp_class_code = $this->check_class_code($class_code);
        if($tmp_class_code != $class_code) {
            $this->showError("数据错误",'/Sns/ClassIndex/Index');exit;
        }
        //检测登陆者是否有编辑的权限
        $is_edit = false;
        $class_role = $this->user['client_class'][$class_code]['teacher_class_role'];
        $class_admin = $this->user['client_class'][$class_code]['class_admin'];
        if(in_array($class_role, array(1,3)) || !empty($class_admin)) {
            $is_edit = true;
        }
        
        //获取上传时的密钥处理
        import('@.Control.Sns.Album.Extra.UploadSecretkey');
        $UploadSecretKeyObject = new UploadSecretkey();
        $secret_key = $UploadSecretKeyObject->getSecretkey($this->user['client_account']);
        
        $this->assign('is_edit', $is_edit);
        $this->assign('secret_key', $secret_key);
        $this->assign('album_id',$album_id);      
        $this->assign('client_account', $this->user['client_account']);
        $this->assign('class_code', $class_code);
        
        $this->display('class_upload');
    }
    
    /**
     * 获取班级相册列表
     */
    public function getAlbumList() {
        $class_code = $this->objInput->getInt('class_code');
        
        if(!$this->check_class_code($class_code)) {
            $this->ajaxReturn(null, '数据错误', -1, 'json');
        }
        import("@.Control/Api/AlbumApi");
        $AlbumApi = new AlbumApi();
        $album_list = $AlbumApi->getClassAlbumListByClassCode($class_code, 0, 100);
        if(empty($album_list)) {
            $this->ajaxReturn(null, '没有相册', -1, 'json');
        }
        $this->ajaxReturn($album_list, '成功获取数据', 1, 'json');
    }
    
    /**
     * 通过相片id获取相片信息
     */
    public function getPhotoInfoByPhotoId() {
        $photo_id = $this->objInput->getInt('photo_id');
        if(empty($photo_id)) {
            $this->ajaxReturn(null, '数据错误', -1, 'json');
        }
        import('@.Control.Api.AlbumImpl.PhotoInfo');
        $PhotoInfo = new PhotoInfo();
        $photo_info = $PhotoInfo->getPhotoByPhotoId($photo_id);
        if(empty($photo_info)) {
            $this->ajaxReturn(null, '数据错误', -1, 'json');
        }
        $this->ajaxReturn(reset($photo_info), '成功获取数据', 1, 'json');
    }
    /**
     * 添加相片描述
     */
    public function addPhotoDescription() {
        $photo_id = $this->objInput->postInt('photo_id');
        $description = $this->objInput->postStr('description');
        if(empty($photo_id) || empty($description)) {
            $this->ajaxReturn(null, '数据错误', -1, 'json');
        }
        import('@.Control.Api.AlbumImpl.PhotoInfo');
        $PhotoInfo = new PhotoInfo();
        $effect = $PhotoInfo->updPhoto(array('description'=>$description), $photo_id);
        if(empty($effect)) {
            $this->ajaxReturn(null, '更新失败', -1, 'json');
        }
        $this->ajaxReturn($effect, '更新成功', 1, 'json');
    }
	/**
     * 修改相片名称
     * @param int $photo_id
     * @param String $photo_name
     * 
     * @return boolean
     */
    public function updPhotoName() {
        $photo_id = $this->objInput->postInt('photo_id');
        $photo_name = $this->objInput->postStr('photo_name');
        if(empty($photo_id) || empty($photo_name)) {
            $this->ajaxReturn(null, '更新失败', -1, 'json');
        }
        $datas = array(
            'name'=>$photo_name,
            'upd_time'=>time()
        );
        import('@.Control.Api.AlbumImpl.PhotoInfo');
        $PhotoInfo = new PhotoInfo();
        $effect = $PhotoInfo->updPhoto($datas, $photo_id);
        if(empty($effect)) {
            $this->ajaxReturn(null, '更新失败', -1, 'json');
        }
        
        $this->ajaxReturn($effect, '更新成功', 1, 'json');
    }
    
	/**
     * 删除相片
     * 
     * @param int $photo_id
     */
    public function delPhoto() {
        $photo_id = $this->objInput->getInt('photo_id');
        $class_code = $this->objInput->getInt('class_code');
        if(empty($photo_id) || empty($class_code)) {
            $this->ajaxReturn('', '', -1, 'json');
        }
        import('@.Control.Api.AlbumImpl.PhotoInfo');
        $PhotoInfo = new PhotoInfo();
        $effect = $PhotoInfo->delPhotoByPhotoId($photo_id);
        if(empty($effect)) {
            $this->ajaxReturn($effect, '删除失败', -1, 'json');
        }
        $this->ajaxReturn($effect, '删除成功', 1, 'json');
    }
	/**
     * 移动相片到相册
     * 
     * @param int $album_id
     * @param int $photo_id
     * 
     */
    public function movePhoto() {
        $to_album_id = $this->objInput->postInt('to_album_id');
        $photo_id = $this->objInput->postInt('photo_id');
        if(empty($photo_id) || empty($to_album_id)) {
            $this->ajaxReturn('', '', -1, 'json');
        }
        import('@.Control.Api.AlbumImpl.PhotoInfo');
        $PhotoInfo = new PhotoInfo();
        $effect = $PhotoInfo->updPhoto(array('album_id'=>$to_album_id), $photo_id);
        if(empty($effect)) {
            $this->ajaxReturn(null, '移动相片失败', -1, 'json');
        }
        
        $this->ajaxReturn($effect, '移动相片成功', 1, 'json');
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
