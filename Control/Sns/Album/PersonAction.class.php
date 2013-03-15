<?php
class PersonAction extends SnsController{
    public function _initialize() {
        parent::_initialize();
        import('@.Control.Api.AlbumImpl.ByPerson');
    }
    
    public function albumlist() {
        $client_account = $this->objInput->getInt('client_account');
        $page = $this->objInput->getInt('page');
        
        if(!$this->check_client_account($client_account)) {
            $client_account = $this->user['client_account'];
        }
        //检测登陆者是否有编辑的权限
        $is_edit = false;
        
        if($client_account == $this->user['client_account']) {
            $is_edit = true;
        }
        $img_file_url = '/Public/wmw_images/auto_photo_img/wzp.jpg';
        $this->assign('is_edit', $is_edit);
        $this->assign('client_account', $client_account);
        $this->assign('login_account', $this->user['client_account']);
        $this->assign('no_photo_img', $img_file_url);
       
        $this->display('person_list_album');
    }
    /**
     * 上传照片页面
     */
    public function uplaodPhoto() {
        $client_account = $this->objInput->getInt('client_account');
        $album_id = $this->objInput->getInt('album_id');
        if(!$this->check_client_account($client_account)) {
            echo "用户不存在！";exit;
        }
        
        //获取上传时的密钥处理
        import('@.Control.Sns.Album.Extra.UploadSecretkey');
        $UploadSecretKeyObject = new UploadSecretkey();
        $secret_key = $UploadSecretKeyObject->getSecretkey($this->user['client_account']);
        
        $this->assign('secret_key', $secret_key);
        $this->assign('login_account', $this->user['client_account']);
        $this->assign('client_account', $client_account);
        
        $this->display('person_upload');
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