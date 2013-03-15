<?php
class PymanageAction extends SnsController {
   
   /**
     * 加载系统的评语模板
     */
    public function getSysPyInfoTemplate() {
        import('@.Common_wmw.Constancearr');
        $py_type_list = Constancearr::pytype();
		$py_att_list = Constancearr::pytypeatt();

		$this->assign('py_type_list', $py_type_list);
        $this->assign('py_att_list', $py_att_list);
        
		$this->display('Subtemplate#sys_py_list');
    }

    /**
     * 获取信息的评语信息
     */
    public function getSysPyinfoAjax() {
        $py_type = $this->objInput->getInt('py_type');
        $py_att = $this->objInput->getInt('py_att');

        $py_type = max(0, $py_type);
        $py_att = max(0, $py_att);

        import('@.Common_wmw.Constancearr');
        $py_type_list = Constancearr::pytype();
		$py_att_list = Constancearr::pytypeatt();

        //查询的处理
        $mPyInfo = ClsFactory::Create('Model.mPyInfo');
        $sys_py_list = $mPyInfo->getPyInfoByPyTypeAndPyAtt($py_type, $py_att, 0, 500);

        $this->ajaxReturn($sys_py_list, '成功', 1, 'json');
    }
    
     /**
     * 获取个人收藏的评语信息
     */
    public function getMyPyinfoTemplateAjax() {
        import('@.Common_wmw.Constancearr');
        $py_type_list = Constancearr::pytype();
		$py_att_list = Constancearr::pytypeatt();

		$this->assign('py_type_list', $py_type_list);
        $this->assign('py_att_list', $py_att_list);

        $this->display('Subtemplate#my_py_list');
    }
    
    /**
     * 获取个人的评语信息
     */
    public function getMyPyinfoAjax() {
        $py_type = $this->objInput->getInt('py_type');
        $py_att = $this->objInput->getInt('py_att');
        
        $py_type = max(0, $py_type);
        $py_att = max(0, $py_att);
        
        $client_account = $this->user['client_account'];
        $mMyPyCollect = ClsFactory::Create('Model.mMypyCollect');
        
        $where = array( "client_account=$client_account");
        if (!empty($py_type)) {
            $where[] = "py_type=$py_type";
        }
        if (!empty($py_att)) {
            $where[] = "py_att=$py_att";
        }
        
        $my_py_list = $mMyPyCollect->getMyPycollectInfo($where, 'collect_id desc', 0, 300);

        $this->ajaxReturn($my_py_list, '成功', 1, 'json');
    }
    
    /**
     * 评语输入框的模板的获取
     */
    public function getPyInputTemplateAjax() {
        $this->display('Subtemplate#py_input');
    }
    
	/**
     * 个人评语收藏
     */
    public function collectPyinfoAjax() {
        $py_id = $this->objInput->getInt('py_id');

        if(empty($py_id)) {
            $this->ajaxReturn(null, '参数有误!', -1, 'json');
        }
        if($this->user['client_type'] != CLIENT_TYPE_TEACHER) {
            $this->ajaxReturn(null, '您暂时没有收藏权限!', -1, 'json');
        }

        $client_account = $this->user['client_account'];

		$mMypyCollect = ClsFactory::Create('Model.mMypyCollect');
		$my_py_arr = $mMypyCollect->getMyPycollectByaccount($client_account);
		$my_py_list = & $my_py_arr[$client_account];
		if(count($my_py_list) >= 300) {
		    $this->ajaxReturn(null, '您的评语库已满，最多收藏300个!', -1, 'json');
		}

		$mPyInfo = ClsFactory::Create('Model.mPyInfo');
		$sys_py_info_list = $mPyInfo->getPyInfoById($py_id);
		$sys_py_info = & $sys_py_info_list[$py_id];
		if(empty($sys_py_info)) {
		    $this->ajaxReturn(null, '该系统评语不存在或已删除,收藏失败!', -1, 'json');
		}

	    $my_py_datas = array(
	        'py_content' => $sys_py_info['py_content'],
    	    'py_type'    => $sys_py_info['py_type'],
    	    'py_att'     => $sys_py_info['py_att'],
            'client_account' => $client_account,
	        'add_time' => time(),
	    );
	    $collect_id = $mMypyCollect->addMyPyCollect($my_py_datas, true);
	    if(empty($collect_id)) {
	        $this->ajaxReturn(null, '系统繁忙,收藏失败!', -1, 'json');
	    }
 
		$this->ajaxReturn(null, '收藏成功!', 1, 'json');
    }

     /**
     * 删除个人收藏的评语
     */
    public function delMyPyAjax() {
        $collect_id = $this->objInput->getInt('collect_id');

        if(empty($collect_id)) {
            $this->ajaxReturn(null, '参数有误!', -1, 'json');
        }
        if($this->user['client_type'] != CLIENT_TYPE_TEACHER) {
            $this->ajaxReturn(null, '您暂时没有收藏权限!', -1, 'json');
        }

        $client_account = $this->user['client_account'];
        
		$mMypyCollect = ClsFactory::Create('Model.mMypyCollect');
		$my_py_arr = $mMypyCollect->getMyPycollectById($collect_id);
		$my_py_info = & $my_py_arr[$collect_id];
		if(empty($my_py_info) || $my_py_info['client_account'] != $client_account) {
		    $this->ajaxReturn(null, '该系统评语不存在或已删除!', -1, 'json');
		}

	    $is_del = $mMypyCollect->delMyCollect($collect_id);
	    if(empty($is_del)) {
	        $this->ajaxReturn(null, '系统繁忙,删除失败!', -1, 'json');
	    }
 
		$this->ajaxReturn(null, '删除成功!', 1, 'json');
    }

}