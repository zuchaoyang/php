<?php
/**
 * author:sailong<shailong123@126.com>
 * 功能：Album manage
 * 说明：作为相册或照片操作的统一接口
 * 
 * @return json
 */


class AlbumApi extends ApiController {
    /**
     * 
     * 固定函数
     */
    public function __construct() {
        parent::__construct();
    }    
   
    /**
     * 
     * 固定函数
     */    
    public function _initialize(){
		parent::_initialize();        
    }
    
    
    
    
        //班级相册接口start
    /**
     * 实例化班级相册类
     */
    private function initClass() {
        import('@.Control.Api.AlbumImpl.ByClass');
        return  new ByClass();
    }
    
    /**
     * 创建班级相册
     * 接收值方式：post
     * 
     */
    //album_function
    public function createByClass() {
        
        $album_name      = $this->objInput->postStr('album_name');
        $explain         = $this->objInput->postStr('album_explain');
        $grant           = $this->objInput->postInt('grant_sel');
        $class_code      = $this->objInput->postInt('class_code');
        $client_account  = $this->objInput->postInt('client_account');
        
        $data_arr = array(
            'album_name'=>$album_name,
            'album_explain'=>$explain,
            'grant'=>$grant,
            'uid'=>$client_account
        );
        if(empty($data_arr)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByClass = $this->initClass();
        $json_list = $ByClass->create($data_arr, $class_code);
        if(empty($json_list)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        
        $this->ajaxReturn($json_list, '', 1, 'json');
    }
    
    
    /**
     * 根据班级ID只获取相册表的信息列表
     * @param int $class_code
     * 
     * @return array album_list
     */
    //album_class_relation_function
    public function getOnlyAlbumListByClassCode() {
        $class_code = $this->objInput->getInt('class_code');
        $ByClass = $this->initClass();
        $album_list = $ByClass->getOnlyAlbumListByClassCode($class_code);
        
        if(empty($album_list)) {
            $this->ajaxReturn(null, '没有了', -1, 'json');
        }
        
        $this->ajaxReturn($album_list, '', 1, 'json');
    }
    
    /**
     * 根据班级class_code获取班级相册信息列表
     * todolist 分页
     * 接收值方式：post
     * 
     */
    //album_class_relation_function
    public function getListByClass() {
        $class_code = $this->objInput->getInt('class_code');
        $page = $this->objInput->getInt('page');
        $limit = 4;
        $offset = null;
        $page = max(1,$page);
        $offset = ($page-1)*$limit;
        if(empty($class_code)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByClass = $this->initClass();
        $album_list = $ByClass->getListByClass($class_code, $offset, $limit);
        if(empty($album_list)) {
            $this->ajaxReturn(null, '没有了', -1, 'json');
        }
        
        $this->ajaxReturn($album_list, '', 1, 'json');
    }
    
    /**
     * 获取班级相册信息
     * @param int $album_id
     * @param int $class_code
     */
    //album_class_relation_function
    public function getAlbumByClass() {
        $album_id = $this->objInput->getInt('album_id');
        $class_code = $this->objInput->getInt('class_code');
        if(empty($album_id)) {
            $this->ajaxReturn('', '', -1, 'json');
        }
        
        //获取相处信息
        $ByClass = $this->initClass();
        $rs = $ByClass->getAlbumByClassAlbumId($album_id, $class_code);
        if(empty($rs)) {
            $this->ajaxReturn($rs, '', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '', 1, 'json');
    }
    
    /**
     * 修改班级相册信息
     * 
     * @param $album_id 相册id
     * @param $album_name 相册name
     * @param $explain 相册描述
     * @param $grant 相册权限
     * @param $uid 操作uid
     * 
     */
    public function updAlbumByClass() {
        $album_id   = $this->objInput->postInt('album_id');
        $album_name = $this->objInput->postStr('album_name');
        $explain    = $this->objInput->postStr('album_explain');
        $grant      = $this->objInput->postInt('grant');
        $class_code = $this->objInput->postInt('class_code');
        $uid        = $this->objInput->postInt('client_account');
        $data = array(
            'album_name'=>$album_name,
            'album_explain'=>$explain,
            'grant'=>$grant,
            'upd_account'=>$uid,
        	'upd_time'=>time()
        );
        $ByClass = $this->initClass();
        $rs = $ByClass->updAlbum($data, $album_id, $class_code);
        if(empty($rs)) {
            $this->ajaxReturn($rs, '修改失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '修改成功', 1, 'json');
    }
    
    /**
     * 根据相册album_id及班级class_code删除相册信息
     * 
     * @param int $album_id
     * @param int $class_code
     * 
     */
    public function delAlbumByClass () {
        $album_id = $this->objInput->getInt('album_id');
        $class_code = $this->objInput->getInt('class_code');
        if(empty($album_id) && empty($class_code)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByClass = $this->initClass();
        $rs = $ByClass->delAlbumByClass($album_id, $class_code);
        if(empty($rs)) {
            $this->ajaxReturn($rs, '删除失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '删除成功', 1, 'json');
    }
    
    /**
     * 上传班级相片
     */
    public function uploadImgByClass() {
        $class_code = $this->objInput->postInt('class_code');
        $img_file = $_FILES['img_file'];
    }
    
    /**
     * 设置班级相册封面
     * 
     * @param int $album_id
     * @param String $album_img 相册封面图片名称
     * 
     */
    public function setAlbumImgByClass() {
        $album_id = $this->objInput->postInt('album_id');
        $album_img = $this->objInput->postStr('album_img');
        if(empty($album_id) && empty($album_img)) {
            $this->ajaxReturn('', '', -1, 'json');
        }
        $ByClass = $this->initClass();
        $rs = $ByClass->setAlbumImg($album_img, $album_id);
        if(empty($rs)) {
            $this->ajaxReturn($rs, '设置失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '设置成功', 1, 'json');
    }
    
    
    /**
     * 根据相册id获取相片列表
     * @param int $album_id
     * 
     * @return array $photo_list
     */
    public function getClassPhotoListByAlbumId() {
        $album_id   = $this->objInput->getInt('album_id');
        $class_code = $this->objInput->getInt('class_code');
        $client_account = $this->objInput->getInt('client_account');
        $page       = $this->objInput->getInt('page');
        if($page !== false) {
            $limit = 12;
            $offset = null;
            $page = max(1,$page);
            $offset = ($page-1)*$limit;
        }
        
        if(empty($class_code)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByClass = $this->initClass();
        $photo_list = $ByClass->getClassPhotoListByAlbumId($album_id, $offset, $limit);
        
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
     * 根据相册ID获取相片数量
     * @param int $album_id
     * 
     * @return int $count
     */
    public function getClassPhotoCountByAlbumId() {
        $album_id = $this->objInput->getInt("album_id");
        if(empty($album_id)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByClass = $this->initClass();
        $count = $ByClass->getClassPhotoCountByAlbumId($album_id);
        
        if(empty($count)) {
            $this->ajaxReturn(0, '', 1, 'json');
        }
        
        $this->ajaxReturn($count, '', 1, 'json');
        
    }
    /**
     * 根据相片ID获取相片信息
     * @param int $album_id
     * 
     * @return array $photo_arr
     */
    public function getClassPhotoByPhotoId() {
        $photo_id = $this->objInput->getInt('photo_id');
        $class_code = $this->objInput->getInt('class_code');
        $client_account = $this->objInput->getInt('client_account');
        if(empty($photo_id)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByClass = $this->initClass();
        $photo_list = $ByClass->getClassPhotoByPhotoId($photo_id);
        
        if(empty($photo_list)) {
            $this->ajaxReturn(0, '', 1, 'json');
        }
        import("@.Common_wmw.Pathmanagement_sns");
        $img_path = Pathmanagement_sns::getAlbum($client_account).'/';
        foreach($photo_list as $photo_id=>$photo_val) {
            $photo_val['big_img'] = $img_path.$photo_val['file_big'];
            $photo_val['middle_img'] = $img_path.$photo_val['file_middle'];
            $photo_val['small_img'] = $img_path.$photo_val['file_small'];
            $photo_val['img_path'] = $img_path;
            $photo_val['add_date'] = date('Y-m-d',$photo_val['upd_time']);
            $photo_val['upd_date'] = date('Y-m-d',$photo_val['upd_time']);
            $photo_list[$photo_id] = $photo_val;
        }
        
        $this->ajaxReturn($photo_list, '', 1, 'json');
        
    }
    /**
     * 修改相片名称
     * @param int $photo_id
     * @param String $photo_name
     * 
     * @return boolean
     */
    public function updPhotoNameByClass() {
        $photo_id = $this->objInput->postInt('photo_id');
        $photo_name = $this->objInput->postStr('photo_name');
        if(empty($photo_id) || empty($photo_name)) {
            $this->ajaxReturn(null, '更新失败', -1, 'json');
        }
        $datas = array(
            'name'=>$photo_name,
            'upd_time'=>time()
        );
        $ByClass = $this->initClass();
        $rs = $ByClass->updClassPhotoByPhotoId($datas, $photo_id);
        if(empty($rs)) {
            $this->ajaxReturn(null, '更新失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '更新成功', 1, 'json');
    }
    /**
     * 修改相片描述
     * @param int $photo_id
     * @param string @content
     * 
     * @return boolean
     */
    public function updPhotoDescriptionByClass() {
        $photo_id = $this->objInput->postInt('photo_id');
        $description = $this->objInput->postStr('content');
        if(empty($photo_id) || empty($description)) {
            $this->ajaxReturn(null,'',-1,'json');
        }
        $datas = array(
            'description'=>$description,
            'upd_time'=>time()
        );
        $ByClass = $this->initClass();
        $rs = $ByClass->updClassPhotoByPhotoId($datas, $photo_id);
        if(empty($rs)) {
            $this->ajaxReturn(null, '更新失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '更新成功', 1, 'json');
    }
    /**
     * 获取相片一级评论
     */
    public function getCommentListByUpId() {
        $up_id = $this->objInput->getInt('up_id');
        $page = $this->objInput->getInt('page');
        $limit = 5;
        $offset = null;
        $page = max(1,$page);
        $offset = ($page-1)*$limit;
        if(empty($up_id)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByClass = $this->initClass();
        $comment_list = $ByClass->getClassPhotoCommentByUpId($up_id, $offset, $limit);
        if(empty($comment_list)) {
            $this->ajaxReturn(null, '没有了', -1, 'json');
        }
        $this->ajaxReturn($comment_list, '', 1, 'json');
    }
    
    /**
     * 获取相片二级评论
     */
    public function getSecCommentListByUpId() {
        $up_id = $this->objInput->getInt('up_id');
        $page = $this->objInput->getInt('page');
        $limit = 500;
        $offset = null;
        $page = max(1,$page);
        $offset = ($page-1)*$limit;
        if(empty($up_id)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByClass = $this->initClass();
        $comment_list = $ByClass->getClassPhotoSecCommentByUpId($up_id, $offset, $limit);
        if(empty($comment_list)) {
            $this->ajaxReturn(null, '没有了', -1, 'json');
        }
        $this->ajaxReturn($comment_list, '', 1, 'json');
    }
    
    /**
     * 获取相片评论
     * 
     */
    public function getPhotoCommentListByClass() {
        $up_id = $this->objInput->getInt('up_id');
        $page = $this->objInput->getInt('page');
        $limit = 2;
        $offset = null;
        $page = max(1,$page);
        $offset = ($page-1)*$limit;
        if(empty($up_id)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByClass = $this->initClass();
        $comment_1st_list = $ByClass->getClassPhotoCommentByUpId($up_id, $offset, $limit);
        $up_id_arr = array_keys($comment_1st_list);
        $comment_2sec_list = $ByClass->getClassPhotoSecCommentByUpId($up_id_arr, 0, 100);
        foreach($comment_2sec_list as $comment_id=>$comment_info) {
            $up_child_id = $comment_info['up_id'];
            if(empty($comment_1st_list[$up_child_id])) {
                continue;
            }
            $comment_1st_list[$up_child_id]['child_items'][$comment_id]=$comment_info;
        }
        
        if(empty($comment_1st_list)) {
            $this->ajaxReturn(null, '没有了', -1, 'json');
        }
        $this->ajaxReturn($comment_1st_list, '', 1, 'json');
    }
    /**
     * 删除班级相片
     * 
     * @param int $photo_id
     */
    public function delPhotoByClass() {
        $photo_id = $this->objInput->getInt('photo_id');
        if(empty($photo_id)) {
            $this->ajaxReturn('', '', -1, 'json');
        }
        $ByClass = $this->initClass();
        $rs = $ByClass->deletePhotoByPhotoId($photo_id);
        if(empty($rs)) {
            $this->ajaxReturn($rs, '删除失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '删除成功', 1, 'json');
    }
    
    /**
     * 添加评论
     *
     */
    public function addCommentByClass() {
        $client_account = $this->objInput->postInt('add_uid');
        $content = $this->objInput->postStr("content");
        $photo_id = $this->objInput->postInt("photo_id");
        $up_id = $this->objInput->postInt("up_id");
        if(empty($client_account) || empty($content) || empty($photo_id)) {
            $this->ajaxReturn(null, '数据错误', -1, 'json');
        }
        $level = 2;
        if($photo_id == $up_id){
            $level = 1;
        }
        $add_time = time();
        $data_arr = array(
            "up_id"=>$up_id,
            "photo_id"=>$photo_id,
            "content"=>$content,
            "client_account"=>$client_account,
            "add_time"=>$add_time,
            "level"=>$level
        );
        $ByClass = $this->initClass();
        $rs = $ByClass->addComment($data_arr,true);
        
        if(empty($rs)) {
            $this->ajaxReturn(null, '评论失败', -1, 'json');
        }
        import('@.Common_wmw.WmwFace');
        $data_arr['content'] = WmwFace::parseFace($data_arr['content']);
        $data_arr['comment_id']=$rs;
        $data_arr['add_date']=date('y-m-d H:i:s', $add_time);
        $this->ajaxReturn($data_arr, '评论成功', 1, 'json');
    }
    
    /**
     * 删除班级照片评论
     * @param int $commment_id 相片评论ID
     */
    public function delPhotoCommentByClass() {
        $comment_id = $this->objInput->getInt('comment_id');
        if(empty($comment_id)) {
            $this->ajaxReturn($comment_id, '操作失败', -1, 'json');
        }
        
        $ByClass = $this->initClass();
        $rs = $ByClass->delCommentByCommnetId($comment_id);
        if(empty($rs)) {
            $this->ajaxReturn($rs, '操作失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '删除成功', 1, 'json');
    }
    
    /**
     * 移动班级相片到相册
     * 
     * @param int $album_id
     * @param int $photo_id
     * 
     */
    public function movePhotoByClass() {
        $img_name = $this->objInput->postStr('img_name');
        $from_album_id = $this->objInput->postInt('from_album_id');
        $to_album_id = $this->objInput->postInt('to_album_id');
        $photo_id = $this->objInput->postInt('photo_id');
        if(empty($from_album_id) || empty($photo_id) || empty($to_album_id)) {
            $this->ajaxReturn('', '', -1, 'json');
        }
        $ByClass = $this->initClass();
        $rs = $ByClass->movePhoto($to_album_id,$photo_id);
        if(!empty($rs)) {
            $to_upd_data['album_auto_img'] = "$img_name";
            $ByClass->updateAlbumPhotoCountByAlbumId($from_album_id);
            $ByClass->updateAlbumPhotoCountByAlbumId($to_album_id, $to_upd_data);
        }
        if(empty($rs)) {
            $this->ajaxReturn($rs, '移动相片失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '移动相片成功', 1, 'json');
    }
    
    /**
     * 通过class_code,uid获取权限设置列表
     * @param int $class_code
     * @param int $uid
     * 
     * @reutrn array $grants_list
     */
    public function getClassGrantList() {
        $ByClass = $this->initClass();
        $rs = $ByClass->grant_arr();
        if(empty($rs)) {
            $this->ajaxReturn($rs, '', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '', 1, 'json');
    }
    //班级相册接口end
    
    
    
    
    
    
        //个人相册接口start
    /**
     * 实例化个人相册类
     */
    private function initPerson() {
        import('@.Control.Api.AlbumImpl.ByPerson');
        return  new ByPerson();
    }
    
    /**
     * 创建个人相册
     * 接收值方式：post
     * 
     */
    //album_function
    public function createByPerson() {
        
        $album_name      = $this->objInput->postStr('album_name');
        $explain         = $this->objInput->postStr('album_explain');
        $grant           = $this->objInput->postInt('grant_sel');
        $client_account  = $this->objInput->postInt('client_account');
        
        $data_arr = array(
            'album_name'=>$album_name,
            'album_explain'=>$explain,
            'grant'=>$grant,
            'uid'=>$client_account
        );
        if(empty($data_arr)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByPerson = $this->initPerson();
        $json_list = $ByPerson->create($data_arr, $client_account);
        if(empty($json_list)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        
        $this->ajaxReturn($json_list, '', 1, 'json');
    }
    
    
    /**
     * 根据个人ID只获取相册表的信息列表
     * @param int $client_account
     * 
     * @return array album_list
     */
    //album_class_relation_function
    public function getOnlyAlbumListByClientAccount() {
        $client_account = $this->objInput->getInt('client_account');
        $ByPerson = $this->initPerson();
        $album_list = $ByPerson->getOnlyAlbumListByClientAccount($client_account);
        
        if(empty($album_list)) {
            $this->ajaxReturn(null, '没有了', -1, 'json');
        }
        
        $this->ajaxReturn($album_list, '', 1, 'json');
    }
    
    /**
     * 根据个人client_account获取个人相册信息列表
     * todolist 分页
     * 接收值方式：post
     * 
     */
    //album_class_relation_function
    public function getListByPerson() {
        $client_account = $this->objInput->getInt('client_account');
        $page = $this->objInput->getInt('page');
        $limit = 4;
        $offset = null;
        $page = max(1,$page);
        $offset = ($page-1)*$limit;
        if(empty($client_account)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByPerson = $this->initPerson();
        $album_list = $ByPerson->getListByPerson($client_account, $offset, $limit);
        if(empty($album_list)) {
            $this->ajaxReturn(null, '没有了', -1, 'json');
        }
        
        $this->ajaxReturn($album_list, '', 1, 'json');
    }
    
    /**
     * 获取个人相册信息
     * @param int $album_id
     * @param int $client_account
     */
    //album_class_relation_function
    public function getAlbumByPerson() {
        $album_id = $this->objInput->getInt('album_id');
        $client_account = $this->objInput->getInt('client_account');
        if(empty($album_id)) {
            $this->ajaxReturn('', '', -1, 'json');
        }
        
        //获取相处信息
        $ByPerson = $this->initPerson();
        $rs = $ByPerson->getAlbumByPersonAlbumId($album_id, $client_account);
        if(empty($rs)) {
            $this->ajaxReturn($rs, '', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '', 1, 'json');
    }
    
    /**
     * 修改个人相册信息
     * 
     * @param $album_id 相册id
     * @param $album_name 相册name
     * @param $explain 相册描述
     * @param $grant 相册权限
     * @param $uid 操作uid
     * 
     */
    public function updAlbumByPerson() {
        $album_id   = $this->objInput->postInt('album_id');
        $album_name = $this->objInput->postStr('album_name');
        $explain    = $this->objInput->postStr('album_explain');
        $grant      = $this->objInput->postInt('grant');
        $client_account = $this->objInput->postInt('client_account');
        $uid        = $this->objInput->postInt('client_account');
        $data = array(
            'album_name'=>$album_name,
            'album_explain'=>$explain,
            'grant'=>$grant,
            'upd_account'=>$uid,
        	'upd_time'=>time()
        );
        $ByPerson = $this->initPerson();
        $rs = $ByPerson->updAlbum($data, $album_id, $client_account);
        if(empty($rs)) {
            $this->ajaxReturn($rs, '修改失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '修改成功', 1, 'json');
    }
    
    /**
     * 根据相册album_id及个人client_account删除相册信息
     * 
     * @param int $album_id
     * @param int $client_account
     * 
     */
    public function delAlbumByPerson () {
        $album_id = $this->objInput->getInt('album_id');
        $client_account = $this->objInput->getInt('client_account');
        if(empty($album_id) && empty($client_account)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByPerson = $this->initPerson();
        $rs = $ByPerson->delAlbumByPerson($album_id, $client_account);
        if(empty($rs)) {
            $this->ajaxReturn($rs, '删除失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '删除成功', 1, 'json');
    }
    
    /**
     * 上传个人相片
     */
    public function uploadImgByPerson() {
        $client_account = $this->objInput->postInt('client_account');
        $img_file = $_FILES['img_file'];
    }
    
    /**
     * 设置个人相册封面
     * 
     * @param int $album_id
     * @param String $album_img 相册封面图片名称
     * 
     */
    public function setAlbumImgByPerson() {
        $album_id = $this->objInput->postInt('album_id');
        $album_img = $this->objInput->postStr('album_img');
        if(empty($album_id) && empty($album_img)) {
            $this->ajaxReturn('', '', -1, 'json');
        }
        $ByPerson = $this->initPerson();
        $rs = $ByPerson->setAlbumImg($album_img, $album_id);
        if(empty($rs)) {
            $this->ajaxReturn($rs, '设置失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '设置成功', 1, 'json');
    }
    
    
    /**
     * 根据相册id获取相片列表
     * @param int $album_id
     * 
     * @return array $photo_list
     */
    public function getPersonPhotoListByAlbumId() {
        $album_id   = $this->objInput->getInt('album_id');
        $client_account = $this->objInput->getInt('client_account');
        $page       = $this->objInput->getInt('page');
        if($page !== false) {
            $limit = 12;
            $offset = null;
            $page = max(1,$page);
            $offset = ($page-1)*$limit;
        }
        
        if(empty($client_account)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByPerson = $this->initPerson();
        $photo_list = $ByPerson->getPersonPhotoListByAlbumId($album_id, $offset, $limit);
        
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
     * 根据相册ID获取相片数量
     * @param int $album_id
     * 
     * @return int $count
     */
    public function getPersonPhotoCountByAlbumId() {
        $album_id = $this->objInput->getInt("album_id");
        if(empty($album_id)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByPerson = $this->initPerson();
        $count = $ByPerson->getPersonPhotoCountByAlbumId($album_id);
        
        if(empty($count)) {
            $this->ajaxReturn(0, '', 1, 'json');
        }
        
        $this->ajaxReturn($count, '', 1, 'json');
        
    }
    /**
     * 根据相片ID获取相片信息
     * @param int $album_id
     * 
     * @return array $photo_arr
     */
    public function getPersonPhotoByPhotoId() {
        $photo_id = $this->objInput->getInt('photo_id');
        $client_account = $this->objInput->getInt('client_account');
        if(empty($photo_id)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByPerson = $this->initPerson();
        $photo_list = $ByPerson->getPersonPhotoByPhotoId($photo_id);
        
        if(empty($photo_list)) {
            $this->ajaxReturn(0, '', 1, 'json');
        }
        import("@.Common_wmw.Pathmanagement_sns");
        $img_path = Pathmanagement_sns::getAlbum($client_account).'/';
        foreach($photo_list as $photo_id=>$photo_val) {
            $photo_val['big_img'] = $img_path.$photo_val['file_big'];
            $photo_val['middle_img'] = $img_path.$photo_val['file_middle'];
            $photo_val['small_img'] = $img_path.$photo_val['file_small'];
            $photo_val['img_path'] = $img_path;
            $photo_val['add_date'] = date('Y-m-d',$photo_val['upd_time']);
            $photo_val['upd_date'] = date('Y-m-d',$photo_val['upd_time']);
            $photo_list[$photo_id] = $photo_val;
        }
        
        $this->ajaxReturn($photo_list, '', 1, 'json');
        
    }
    /**
     * 修改相片名称
     * @param int $photo_id
     * @param String $photo_name
     * 
     * @return boolean
     */
    public function updPhotoNameByPerson() {
        $photo_id = $this->objInput->postInt('photo_id');
        $photo_name = $this->objInput->postStr('photo_name');
        if(empty($photo_id) || empty($photo_name)) {
            $this->ajaxReturn(null, '更新失败', -1, 'json');
        }
        $datas = array(
            'name'=>$photo_name,
            'upd_time'=>time()
        );
        $ByPerson = $this->initPerson();
        $rs = $ByPerson->updPersonPhotoByPhotoId($datas, $photo_id);
        if(empty($rs)) {
            $this->ajaxReturn(null, '更新失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '更新成功', 1, 'json');
    }
    /**
     * 修改相片描述
     * @param int $photo_id
     * @param string @content
     * 
     * @return boolean
     */
    public function updPhotoDescriptionByPerson() {
        $photo_id = $this->objInput->postInt('photo_id');
        $description = $this->objInput->postStr('content');
        if(empty($photo_id) || empty($description)) {
            $this->ajaxReturn(null,'',-1,'json');
        }
        $datas = array(
            'description'=>$description,
            'upd_time'=>time()
        );
        $ByPerson = $this->initPerson();
        $rs = $ByPerson->updPersonPhotoByPhotoId($datas, $photo_id);
        if(empty($rs)) {
            $this->ajaxReturn(null, '更新失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '更新成功', 1, 'json');
    }
    /*/**
     * 获取相片一级评论
     */
    /*
    public function getCommentListByUpId() {
        $up_id = $this->objInput->getInt('up_id');
        $page = $this->objInput->getInt('page');
        $limit = 5;
        $offset = null;
        $page = max(1,$page);
        $offset = ($page-1)*$limit;
        if(empty($up_id)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByPerson = $this->initPerson();
        $comment_list = $ByPerson->getPersonPhotoCommentByUpId($up_id, $offset, $limit);
        if(empty($comment_list)) {
            $this->ajaxReturn(null, '没有了', -1, 'json');
        }
        $this->ajaxReturn($comment_list, '', 1, 'json');
    }
    
    /**
     * 获取相片二级评论
     */
    /*
    public function getSecCommentListByUpId() {
        $up_id = $this->objInput->getInt('up_id');
        $page = $this->objInput->getInt('page');
        $limit = 500;
        $offset = null;
        $page = max(1,$page);
        $offset = ($page-1)*$limit;
        if(empty($up_id)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByPerson = $this->initPerson();
        $comment_list = $ByPerson->getPersonPhotoSecCommentByUpId($up_id, $offset, $limit);
        if(empty($comment_list)) {
            $this->ajaxReturn(null, '没有了', -1, 'json');
        }
        $this->ajaxReturn($comment_list, '', 1, 'json');
    }*/
    
    /**
     * 获取相片评论
     * 
     */
    public function getPhotoCommentListByPerson() {
        $up_id = $this->objInput->getInt('up_id');
        $page = $this->objInput->getInt('page');
        $limit = 2;
        $offset = null;
        $page = max(1,$page);
        $offset = ($page-1)*$limit;
        if(empty($up_id)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $ByPerson = $this->initPerson();
        $comment_1st_list = $ByPerson->getPersonPhotoCommentByUpId($up_id, $offset, $limit);
        $up_id_arr = array_keys($comment_1st_list);
        $comment_2sec_list = $ByPerson->getPersonPhotoSecCommentByUpId($up_id_arr, 0, 100);
        foreach($comment_2sec_list as $comment_id=>$comment_info) {
            $up_child_id = $comment_info['up_id'];
            if(empty($comment_1st_list[$up_child_id])) {
                continue;
            }
            $comment_1st_list[$up_child_id]['child_items'][$comment_id]=$comment_info;
        }
        
        if(empty($comment_1st_list)) {
            $this->ajaxReturn(null, '没有了', -1, 'json');
        }
        $this->ajaxReturn($comment_1st_list, '', 1, 'json');
    }
    /**
     * 删除个人相片
     * 
     * @param int $photo_id
     */
    public function delPhotoByPerson() {
        $photo_id = $this->objInput->getInt('photo_id');
        if(empty($photo_id)) {
            $this->ajaxReturn('', '', -1, 'json');
        }
        $ByPerson = $this->initPerson();
        $rs = $ByPerson->deletePhotoByPhotoId($photo_id);
        if(empty($rs)) {
            $this->ajaxReturn($rs, '删除失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '删除成功', 1, 'json');
    }
    
    /**
     * 添加评论
     *
     */
    public function addCommentByPerson() {
        $client_account = $this->objInput->postInt('add_uid');
        $content = $this->objInput->postStr("content");
        $photo_id = $this->objInput->postInt("photo_id");
        $up_id = $this->objInput->postInt("up_id");
        if(empty($client_account) || empty($content) || empty($photo_id)) {
            $this->ajaxReturn(null, '数据错误', -1, 'json');
        }
        $level = 2;
        if($photo_id == $up_id){
            $level = 1;
        }
        $add_time = time();
        $data_arr = array(
            "up_id"=>$up_id,
            "photo_id"=>$photo_id,
            "content"=>$content,
            "client_account"=>$client_account,
            "add_time"=>$add_time,
            "level"=>$level
        );
        $ByPerson = $this->initPerson();
        $rs = $ByPerson->addComment($data_arr,true);
        
        if(empty($rs)) {
            $this->ajaxReturn(null, '评论失败', -1, 'json');
        }
        import('@.Common_wmw.WmwFace');
        $data_arr['content'] = WmwFace::parseFace($data_arr['content']);
        $data_arr['comment_id']=$rs;
        $data_arr['add_date']=date('y-m-d H:i:s', $add_time);
        $this->ajaxReturn($data_arr, '评论成功', 1, 'json');
    }
    
    /**
     * 删除个人照片评论
     * @param int $commment_id 相片评论ID
     */
    public function delPhotoCommentByPerson() {
        $comment_id = $this->objInput->getInt('comment_id');
        if(empty($comment_id)) {
            $this->ajaxReturn($comment_id, '操作失败', -1, 'json');
        }
        
        $ByPerson = $this->initPerson();
        $rs = $ByPerson->delCommentByCommnetId($comment_id);
        if(empty($rs)) {
            $this->ajaxReturn($rs, '操作失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '删除成功', 1, 'json');
    }
    
    /**
     * 移动个人相片到相册
     * 
     * @param int $album_id
     * @param int $photo_id
     * 
     */
    public function movePhotoByPerson() {
        $img_name = $this->objInput->postStr('img_name');
        $from_album_id = $this->objInput->postInt('from_album_id');
        $to_album_id = $this->objInput->postInt('to_album_id');
        $photo_id = $this->objInput->postInt('photo_id');
        if(empty($from_album_id) || empty($photo_id) || empty($to_album_id)) {
            $this->ajaxReturn('', '', -1, 'json');
        }
        $ByPerson = $this->initPerson();
        $rs = $ByPerson->movePhoto($to_album_id,$photo_id);
        if(!empty($rs)) {
            $from_album_photo_num = $ByPerson->getPersonPhotoCountByAlbumId($from_album_id);
            $form_upd_data['photo_num']=$from_album_photo_num-1;
            $ByPerson->upd($form_upd_data,$from_album_id);
            
            $to_album_photo_num = $ByPerson->getPersonPhotoCountByAlbumId($to_album_id);
            $to_upd_data['album_auto_img'] = "$img_name";
            $to_upd_data['photo_num']=$to_album_photo_num+1;
            $ByPerson->upd($to_upd_data,$to_album_id);
        }
        if(empty($rs)) {
            $this->ajaxReturn($rs, '移动相片失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '移动相片成功', 1, 'json');
    }
    
    /**
     * 通过client_account,uid获取权限设置列表
     * @param int $client_account
     * @param int $uid
     * 
     * @reutrn array $grants_list
     */
    public function getPersonGrantList() {
        $ByPerson = $this->initPerson();
        $rs = $ByPerson->grant_arr();
        if(empty($rs)) {
            $this->ajaxReturn($rs, '', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '', 1, 'json');
    }
    //个人相册接口end
}
