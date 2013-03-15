$(function() {
	var school_id = $('#school_id').val();
	var tree = new DialogTree({
		templatePath	: '/Public/Department/loadDialogTreeTemplate/school_id/' + school_id,
		ajaxPath 		: '/Public/Department/loadTree/data_type/html/school_id/' + school_id,
		selectType		: '1',
		onselected		: function(treeObj) {}
	});
	
	$('#showUpDpt').click(function(){
		tree.registeTree({
			title	: '请选择上级部门',
			id		: '#up_id', 
			name	: '#up_dptname'
		});
	});
});

$(function() {
	var school_id = $('#school_id').val();
	var tree1 = new DialogTree({
		templatePath	: '/Public/Department/loadDialogTreeTemplate/school_id/' + school_id,
		ajaxPath 		: '/Public/Department/loadTree/data_type/html/school_id/' + school_id,
		selectType		: '1',
		onselected		: function(treeObj) {}
	});
	
	$('#showDpt').click(function(){
		tree1.registeTree({
			title	: '请选择部门',
			id		: '#dpt_id', 
			name	: '#dptname'
		});
	});
	
	$('#showDpt1').click(function(){
		tree1.registeTree({
			title	: '请选择部门',
			id		: '#dpt_id1', 
			name	: '#dptname1'
		});
	});
});