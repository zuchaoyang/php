<?php
/**
 * 资源导入相应的api，其中的product_id存在于datas中
 * @author Administrator
 *
 */
class ResourceApi extends ApiController {
    
    public function getResourceByIds($resource_ids) {
        if(empty($resource_ids)) {
            return false;
        }
        
        $mResourceInfo = ClsFactory::Create('Model.Resource.mResourceInfo');
        $resource_list = $mResourceInfo->getResourceInfoById($resource_ids);
        
        return $this->parseResource($resource_list);
    }
    
    /**
     * 通过标题获取同步资源
     * @param $title
     * @param $offset
     * @param $limit
     */
    public function getSynchroResourceByTitle($title, $offset = 0, $limit = 10) {
        if(empty($title)) {
            return false;
        }
        
        $title = str_replace(array('%', '_'), '', $title);
        $wheresql = array(
            'title' => "title like '%$title%'",
            'product_id' => 'product_id=1',
            'resource_status' => 'resource_status=1',
        );
        
        $mResourceInfo = ClsFactory::Create('Model.Resource.mResourceInfo');
        list($count, $resource_list) = $mResourceInfo->getResourceInfoWithCount($wheresql, null, $offset, $limit);
        $resource_list = $this->parseResource($resource_list);
        
        return array($count, $resource_list);
    }
    
    /**
     * 通过标题获取精品网校
     * @param $title
     * @param $offset
     * @param $limit
     */
    public function getQualitySchoolByTitle($title, $offset = 0, $limit = 10) {
        if(empty($title)) {
            return false;
        }
        
        $title = str_replace(array('%', '_'), '', $title);
        $wheresql = array(
            'title' => "title like '%$title%'",
            'product_id' => 'product_id=2',
        	'resource_status' => 'resource_status=1',
        );
        
        $mResourceInfo = ClsFactory::Create('Model.Resource.mResourceInfo');
        list($count, $resource_list) = $mResourceInfo->getResourceInfoWithCount($wheresql, null, $offset, $limit);
        $resource_list = $this->parseResource($resource_list);
        
        return array($count, $resource_list);
    }
    
    /**
     * 通过标题获取精品资源
     * @param $title
     * @param $offset
     * @param $limit
     */
    public function getQualityResourceByTitle($title, $offset = 0, $limit = 10) {
        if(empty($title)) {
            return false;
        }
        
        $title = str_replace(array('%', '_'), '', $title);
        $wheresql = array(
            'title' => "title like '%$title%'",
            'product_id' => 'product_id=3',
            'resource_status' => 'resource_status=1',
        );
        
        $mResourceInfo = ClsFactory::Create('Model.Resource.mResourceInfo');
        list($count, $resource_list) = $mResourceInfo->getResourceInfoWithCount($wheresql, null, $offset, $limit);
        $resource_list = $this->parseResource($resource_list);
        
        return array($count, $resource_list);
    }
    
    /**
     * 根据用户id获取资源信息
     * @param $uids
     * @param $where_appends
     * @param $orderby
     * @param $offset
     * @param $limit
     */
    public function getResourceInfoByUid($uids, $where_appends, $offset = 0, $limit = 10) {
        if(empty($uids)) {
            return false;
        }
        
        $wherearr = array(
            'add_account' => "add_account in('" . implode("','", (array)$uids) . "')",
        );
        if(!empty($where_appends)) {
            $wherearr = array_merge($wherearr, (array)$where_appends);
        }
        
        $mResourceInfo = ClsFactory::Create('Model.Resource.mResourceInfo');
        list($count, $resource_list) = $mResourceInfo->getResourceInfoWithCount($wherearr, 'resource_id desc', $offset, $limit);
        $resource_list = $this->parseResource($resource_list);
        
        return array($count, $resource_list);
    }
    
	/**
     * 通过资源的属性组合获取资源列表
     * @param $resource_ids
     */
    public function getReourceInfoByCombinedKey($CombinedKey, $orderby, $offset, $limit) {
        if(empty($CombinedKey)) {
            return false;
        }
        
        $mResourceInfo = ClsFactory::Create('Model.Resource.mResourceInfo');
        $orderby = !empty($orderby) ? $orderby : null;
        list($count, $resource_list) = $mResourceInfo->getReourceInfoByCombinedKey($CombinedKey, $orderby, $offset, $limit);
        
        import('@.Control.Api.Resource.Curd.ResourceParser');
        $ResourceParser = new ResourceParser();
        $resource_list = $ResourceParser->parseResource($resource_list);
        
        return array($count, $resource_list);
    }
    
    //单条插入资源信息
    public function addResource($datas) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }
        
        return $this->addResourceBat(array($datas));
    }
    
    /**
     * 批量插入资源信息
     * 注明: 
     * 1. api导入数据的是否最多每次 200多余的数据自动截取,
     * 2. 可以需要回执数据，为插入的资源id号
     * 
     * @param $dataarr
     */
    public function addResourceBat($dataarr) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        import('@.Control.Api.Resource.Curd.AddResource');
        $AddResource = new AddResource();
        
        return $AddResource->addResourceBat($dataarr);
    }
    
    /**
     * 批量导入章节信息
     * @param $dataarr
     */
    public function addDynamicAttrsBat($dataarr) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        import('@.Control.Api.Resource.Curd.AddResource');
        $AddResource = new AddResource();
        
        return $AddResource->addDynamicAttrsBat($dataarr);
    }
    
    private function parseResource($resource_list) {
        if(empty($resource_list)) {
            return false;
        }
        
        import('@.Control.Api.Resource.Curd.ResourceParser');
        $ResourceParser = new ResourceParser();
        return $ResourceParser->parseResource($resource_list);
    }
    
}