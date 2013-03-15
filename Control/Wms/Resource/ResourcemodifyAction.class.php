<?php 
class ResourcemodifyAction extends WmsController{
    public function _initialize(){
        parent::_initialize();
        header("content-type:text/html;charset=utf-8");
    }
    
    public function index() {
        $this->redirect("Resource/Resourcemodify/searchResource");
    }
    
    /**
     * 资源查找
     */
    public function searchResource() {
        $product_id = $this->objInput->postInt('product_id');
        $page = $this->objInput->postInt('page');
        
        $partTitle = $this->objInput->postStr('partTitle'); 
        
        $page = max($page, 1);
        $product_id = in_array($product_id, array(1, 2, 3)) ? $product_id : 1;
        
        $perpage =15;
        $offset = ($page-1)* $perpage;
        
        $resourceApi = ClsFactory::Create('@.Control.Api.Resource.ResourceApi');
        switch($product_id) {
            case 1:
                list($total_nums, $resource_list) = $resourceApi->getSynchroResourceByTitle($partTitle, $offset, $perpage);
                break;
            case 2:
                list($total_nums, $resource_list) = $resourceApi->getQualitySchoolByTitle($partTitle, $offset, $perpage);
                break;
            case 3:
                list($total_nums, $resource_list) = $resourceApi->getQualityResourceByTitle($partTitle, $offset, $perpage);
                break;
        }
        
        //添加显示的序号,关键词飘红
        if(!empty($resource_list)) {
            $num_id = $offset + 1;
            foreach($resource_list as $resource_id=>$resource) {
                $resource['num_id'] = $num_id++;
                $resource['title_highlight'] = str_replace($partTitle, "<span style='color:red;'>$partTitle</span>", $resource['title']);
                $resource['md5_key'] = $this->getMd5key($resource_id);
                
                $resource_list[$resource_id] = $resource;
            }
        } else {
            $resource_list = array();
        }
        
        $total_pages = ceil($total_nums / $perpage);
        
        $has_next = $page < $total_pages ? true : false;
        $has_pre = $page > 1 ? true : false;
        
        //查询当前产品id下符合包含题目的资源列表
        $this->assign('page', $page);
        $this->assign('total_pages', $total_pages);
        $this->assign('total_nums', $total_nums);
        $this->assign('has_pre', $has_pre);
        $this->assign('has_next', $has_next);
        
        $this->assign('product_id', $product_id);
        $this->assign('product_id_checked', $product_id);
        $this->assign('partTitle', $partTitle);
        $this->assign('resource_list', $resource_list); 
        
        $this->display('modifyResource');
    }
    
    /**
     * 远程获取资源的详细信息
     */
    public function getResourceAjax() {
        $resource_id = $this->objInput->getInt('resource_id');
        
        $resourceApi = ClsFactory::Create('@.Control.Api.Resource.ResourceApi');
        $resource_list = $resourceApi->getResourceByIds($resource_id);
        $resource_info = & $resource_list[$resource_id];
        if(empty($resource_info)) {
            $this->ajaxReturn(null, '资源获取失败!', -1, 'json');
        }
        
        $this->ajaxReturn($resource_info, '资源获取成功!', 1, 'json');        
    }
    
    /**
     * 删除资源信息
     */
    public function deleteResource() {
        $resource_id = $this->objInput->getInt('resource_id');
        $md5_key     = $this->objInput->getStr('md5_key');
        
        if($this->getMd5key($resource_id) != $md5_key) {
            $this->ajaxReturn(null, '您没有权限删除!', -1, 'json');
        }
        
         //删除相关信息
        $mResourceInfo = ClsFactory::Create('Model.Resource.mResourceInfo');
        if($mResourceInfo->delResourceInfo($resource_id)) {
            $this->deleteResourceAttachments($resource_id);
            
            $this->ajaxReturn(null, '删除成功!', 1, 'json');
        }
        
        $this->ajaxReturn(null, '删除失败!', -1, 'json');
    }
    
    /**
     * 删除资源相应的附件信息
     * @param $resource_info
     */
    private function deleteResourceAttachments($resource_id) {
        if(empty($resource_id)) {
            return false;
        }
        
        $mResourceInfo = ClsFactory::Create('Model.Resource.mResourceInfo');
        $resource_list = $mResourceInfo->getResourceInfoById($resource_id);
        $resource_info = & $resource_list[$resource_id];
        
        $attachment_file = $resource_info['file_path'] . '/' . $resource_info['file_name'];
        
        $is_remote_file = preg_match("/^http(s)?:\/\/(.+)$/", $attachment_file) ? true : false;
        
        if($is_remote_file || !is_file($attachment_file)) {
            return false;
        }
        
        return @ unlink($attachment_file);
    }
    
    /**
     * 获取文件的加密key
     * @param $resource_id
     */
    private function getMd5key($resource_id) {
        return md5($this->user['client_account'] . $resource_id . substr(time(), 0, 6));
    }
    
}