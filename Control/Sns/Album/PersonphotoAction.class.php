<?php
class PersonphotoAction extends SnsController {
    public function _initialize() {
        parent::_initialize();
    }
    /**
     * 用户相册列表
     */
    public function photolist() {
        $album_id = $this->objInput->getInt('album_id');
        $client_account = $this->objInput->getInt('client_account');
        $login_account = $this->user['client_account'];
        $is_edit = false;
        if(empty($client_account) || empty($album_id)) {
            $this->showError("数据错误",'/Sns/PersonIndex/Index');exit;
        }
        
        import("@.Control/Api/AlbumApi");
        $AlbumApi = new AlbumApi();
        $album_list = $AlbumApi->getPersonAlbumByAlbumId($album_id, $client_account);
        if(empty($album_list)) {
            $this->showError("数据错误",'/Sns/PersonIndex/Index');exit;
        }
        $album_list = reset($album_list);
        $grant = $album_list['grant'];
        
        if($client_account == $login_account) {
            $is_edit = true;
        }else{
            if(!$this->check_grant($grant,$client_account)) {
                $this->showError("没有权限查看","/Sns/Album/Personalbum/albumlist/client_account/$client_account");exit;
            }
        }
        
        
        
        //检测登陆者是否有编辑的权限
        if($client_account == $login_account) {
            $is_edit = true;
        }
        
        $this->assign('album_list', $album_list);
        $this->assign('is_edit', $is_edit);
        $this->assign('client_account', $client_account);
        $this->assign('album_id', $album_id);
        $this->assign('login_account', $login_account);
        
        $this->display('person_list_photo');
    }
    
    //瀑布流
    public function photoPlist() {
        $album_id = $this->objInput->getInt('album_id');
        $client_account = $this->objInput->getInt('client_account');
        $login_account = $this->user['client_account'];
        $is_edit = false;
        if(empty($client_account) || empty($album_id)) {
            $this->showError("数据错误",'/Sns/PersonIndex/Index');exit;
        }
        
        $is_true = $this->check_client_account($client_account);
        if(!$is_true) {
            $this->showError("数据错误",'/Sns/PersonIndex/Index');exit;
        }
        //检测登陆者是否有编辑的权限
       
        
        import("@.Control/Api/AlbumApi");
        $AlbumApi = new AlbumApi();
        $album_list = $AlbumApi->getPersonAlbumByAlbumId($album_id, $client_account);
        if(empty($album_list)) {
            $this->ajaxReturn(null, '数据错误', -1, 'json');
        }
        $album_list = reset($album_list);
        $grant = $album_list['grant'];
        
        if($client_account == $login_account) {
            $is_edit = true;
        }else{
            if(!$this->check_grant($grant,$client_account)) {
                $this->showError("没有权限查看","/Sns/Album/Personalbum/albumlist/client_account/$client_account");exit;
            }
        }
        
        $this->assign('album_list', $album_list);
        $this->assign('is_edit', $is_edit);
        $this->assign('client_account', $client_account);
        $this->assign('album_id', $album_id);
        $this->assign('client_account', $client_account);
        $this->assign('login_account', $login_account);
        
        $this->display('person_list_photo_p');
    }
    //检测查看权限
    public function check_grant($grant, $client_account) {
        if($grant === '' || empty($client_account)) {
            return false;
        }
        switch ($grant){
            case 1:
                //好友
                $mAccountrelation = ClsFactory::Create('Model.mAccountrelation');
                $friend_rel_list = $mAccountrelation->getAccountRelationByClientAccout($client_account);
                $friend_rel_list = $friend_rel_list[$client_account];
                $friend_account_arr = array();
                foreach($friend_rel_list as $rel_id=>$rel_val) {
                    $friend_account_arr[$rel_val['friend_account']] = $rel_val['friend_account'];
                }
                if(!in_array($this->user['client_account'],$friend_account_arr)) {
                    return false;
                }
                break;
            case 2:
                if($client_account != $this->user['client_account']) {
                    return false;
                }
                break;
            default:
                return true;
                break;
        };
    }
    /**
     * 获取个人相片
     */
    public function getPhotosByAlbumId() {
        $album_id   = $this->objInput->getInt('album_id');
        $client_account = $this->objInput->getInt('client_account');
        $page       = $this->objInput->getInt('page');
        if($page !== false) {
            $limit = 20;
            $offset = null;
            $page = max(1,$page);
            $offset = ($page-1)*$limit;
        }
        
        if(empty($client_account)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        
        import("@.Control/Api/AlbumApi");
        $AlbumApi = new AlbumApi();
        $album_list = $AlbumApi->getPersonAlbumByAlbumId($album_id, $client_account);
        if(empty($album_list)) {
            $this->ajaxReturn(null, '数据错误', -1, 'json');
        }
        import("@.Control/Api/AlbumImpl/PhotoInfo");
        $PhotoInfo = new PhotoInfo();
        $photo_list = $PhotoInfo->getPhotoListByAlbumId($album_id, $offset, $limit);
        
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
        $client_account = $this->objInput->getInt('client_account');
        $album_id = $this->objInput->getInt('album_id');
        $photo_id = $this->objInput->getInt('photo_id');
        
        $login_account = $this->user['client_account'];
        
        if(empty($client_account) || empty($photo_id)) {
            $this->showError("数据错误",'/Sns/PersonIndex/Index');exit;
        }
        if(empty($album_id)) {
            import("@.Control/Api/AlbumImpl/PhotoInfo");
            $PhotoInfo = new PhotoInfo();
            $photo_list = $PhotoInfo->getPhotoByPhotoId($photo_id);
            $photo_list = reset($photo_list);
            $album_id = $photo_list['album_id'];
        }
        
        //检测登陆者是否有编辑的权限
        $is_edit = false;
       
        import('@.Control.Api.AlbumApi');
        $AlbumApi = new AlbumApi();
        $album_list = $AlbumApi->getPersonAlbumByAlbumId($album_id,$client_account);
        if(empty($album_list)) {
            $this->showError("数据错误",'/Sns/PersonIndex/Index');exit;
        }
        if(empty($album_list)) {
            $this->ajaxReturn(null, '数据错误', -1, 'json');
        }
        $album_list = reset($album_list);
        $grant = $album_list['grant'];
        
        if($client_account == $login_account) {
            $is_edit = true;
        }else{
            if(!$this->check_grant($grant,$client_account)) {
                $this->showError("没有权限查看","/Sns/Album/Personalbum/albumlist/client_account/$client_account");exit;
            }
        }
        
        $this->assign('album', $album_list);
        $this->assign('is_edit', $is_edit);
        $this->assign('photo_id', $photo_id);
        $this->assign('album_id', $album_id);
        $this->assign('client_account', $client_account);
        $this->assign('login_account', $login_account);
        
        $this->display('person_photo_show');
    }
    
	/**
     * 上传照片页面
     */
    public function uplaodPhoto() {
        $album_id = $this->objInput->getInt('album_id');
        $client_account = $this->user['client_account'];
        if(!$this->check_client_account($client_account)) {
            $this->showError("用户不存在",'/Sns/PersonIndex/Index');exit;
        }
        
        //获取上传时的密钥处理
        import('@.Control.Sns.Album.Extra.UploadSecretkey');
        $UploadSecretKeyObject = new UploadSecretkey();
        $secret_key = $UploadSecretKeyObject->getSecretkey($this->user['client_account']);
        
        $this->assign('secret_key', $secret_key);
        $this->assign('login_account', $this->user['client_account']);
        $this->assign('client_account', $client_account);
        $this->assign('album_id', $album_id);
        
        $this->display('person_upload');
    }
	/**
     * 获取班级相册列表
     */
    public function getAlbumList() {
        $client_account = $this->objInput->getInt('client_account');
        
        if(!$this->check_client_account($client_account)) {
            $this->ajaxReturn(null, '数据错误', -1, 'json');
        }
        import("@.Control/Api/AlbumApi");
        $AlbumApi = new AlbumApi();
        $album_list = $AlbumApi->getPersonAlbumListByUid($client_account, 0, 100);
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
        if(empty($photo_id)) {
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
     * 检测用户编号是否存在
     * @param int $client_account
     * 
     * @return  $client_account为存在，false为不存在
     */
    private function check_client_account($client_account) {
        if(empty($client_account)) {
            return false;
        }
        $mUserVm = ClsFactory::Create('RModel.mUserVm');
        $is_client = $mUserVm->getClientAccountById($client_account);
        if(empty($is_client)) {
            return false;
        }
        
        return true;
    }
}
