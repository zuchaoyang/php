<?php
class PersonalbumAction extends SnsController{
    public function _initialize() {
        parent::_initialize();
        import('@.Control.Api.AlbumApi');
        $this->AlbumApi = new AlbumApi();
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
    
    public function loadMoreAlbum(){
        $client_account = $this->objInput->getInt('client_account');
        $page = $this->objInput->getInt('page');
        $limit = 4;
        $offset = null;
        $page = max(1,$page);
        $offset = ($page-1)*$limit;
        if(empty($client_account)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $album_list = $this->AlbumApi->getPersonAlbumListByUid($client_account, $offset, $limit);
        if(empty($album_list)) {
            $this->ajaxReturn(null, '没有了', -1, 'json');
        }
        
        $this->ajaxReturn($album_list, '', 1, 'json');
    }
    /**
     * 获取个人相册信息
     */
    public function getAlbum() {
        $album_id = $this->objInput->getInt('album_id');
        $client_account = $this->objInput->getInt('client_account');
        if(!$this->check_client_account($client_account)) {
            $client_account = $this->user['client_account'];
        }
        if(empty($album_id)) {
            $this->ajaxReturn('', '', -1, 'json');
        }
        
        //获取相处信息
        $rs = $this->AlbumApi->getPersonAlbumByAlbumId($album_id, $client_account);
       
        if(empty($rs)) {
            $this->ajaxReturn($rs, '', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '', 1, 'json');
    }
    
    /**
     * 创建个人相册
     */
    public function createAlbum() {
        $album_name      = $this->objInput->postStr('album_name');
        $explain         = $this->objInput->postStr('album_explain');
        $grant           = $this->objInput->postInt('grant_sel');
        $client_account      = $this->objInput->postInt('client_account');
        if(!$this->check_client_account($client_account)) {
            $client_account = $this->user['client_account'];
        }
        $data_arr = array(
            'album_name'=>$album_name,
            'album_explain'=>$explain,
            'grant'=>$grant,
            'uid'=>$client_account,
        	'client_account'=>$client_account
        );
        if(empty($data_arr)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $album_id = $this->AlbumApi->addPersonAlbum($data_arr);
        if(empty($album_id)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        //添加动态
        import("@.Control.Api.FeedApi");
        $feed_api = new FeedApi();
        $feed_api->user_create($this->user['client_account'], $album_id, FEED_ACTION_PUBLISH);
        
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
        $client_account = $this->objInput->postInt('client_account');
        if(!$this->check_client_account($client_account)) {
            $client_account = $this->user['client_account'];
        }
        $album_data = array(
            'album_name'=>$album_name,
            'album_explain'=>$explain,
            'grant'=>$grant,
            'upd_account'=>$client_account,
        	'upd_time'=>time()
        );
        $rs = $this->AlbumApi->updPersonAlbum($album_data, $album_id, $client_account);
        
        if(empty($rs)) {
            $this->ajaxReturn($rs, '修改失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '修改成功', 1, 'json');
    }
    
    /**
     * 删除相册
     */
    public function delAlbum() {
        $album_id = $this->objInput->getInt('album_id');
        $client_account = $this->objInput->getInt('client_account');
        if(!$this->check_client_account($client_account)) {
            $client_account = $this->user['client_account'];
        }
        if(empty($album_id) && empty($client_account)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $rs = $this->AlbumApi->delPersonAlbum($album_id, $client_account);
        if(empty($rs)) {
            $this->ajaxReturn($rs, '删除失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '删除成功', 1, 'json');
    }
    /**
     * 获取相册权限列表
     */
    public function getGrantList() {
        $rs = $this->AlbumApi->getPersonGrantList();
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
        $client_account = $this->user['client_account'];
        if(empty($album_id) && empty($album_img)) {
            $this->ajaxReturn('', '更新失败', -1, 'json');
        }
        
        $album_data = array('album_img'=>$album_img);
        $rs = $this->AlbumApi->updPersonAlbum($album_data, $album_id, $client_account);
        if(empty($rs)) {
            $this->ajaxReturn(null, '更新失败', -1, 'json');
        }
        $this->ajaxReturn($rs, '更新成功', 1, 'json');
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