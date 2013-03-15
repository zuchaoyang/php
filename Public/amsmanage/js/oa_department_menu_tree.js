function dptMenuTreeCls() {};
dptMenuTreeCls.prototype.school_id = 0;
dptMenuTreeCls.prototype.img_path = "";
dptMenuTreeCls.prototype.openUrl = "";
dptMenuTreeCls.prototype.formObj = null;
dptMenuTreeCls.prototype.tree = null;

dptMenuTreeCls.prototype.setImagePath = function(img_path) {
	this.img_path = img_path;
};

dptMenuTreeCls.prototype.setSchoolId = function(school_id) {
	this.school_id = school_id;
};

dptMenuTreeCls.prototype.setFormObj = function(formObj) {
	this.formObj = formObj;
};

dptMenuTreeCls.prototype.setOpenUrl = function(open_url) {
	this.openUrl = open_url;
}

dptMenuTreeCls.prototype.loadTree = function() {
	var _this = this;
	
	this.tree = new dhtmlXTreeObject("doctree_box", "100%", "100%", 0);
	this.tree.setImagePath(this.img_path + "js/dhtmlxtree/codebase/imgs/");
	this.tree.setOnClickHandler(function(id){
		_this.openPathDocs(id);
		_this.tree.setItemColor(id, '#369', 'blue');
	});
	this.tree.attachEvent("onOpenEnd", this.updateTreeSize);
	this.tree.enableCheckBoxes(false);
	this.tree.setDataMode("json");
	
	var loadJson = function() {
		var _jsonObj = {};
		$.ajax({
			type:'get',
			url:"/Public/Department/loadTree/data_type/json/school_id/" + _this.school_id,
			dataType:'json',
			async:false,
			success:function(json) {
				_jsonObj = json;
	    	}
	    });
		return _jsonObj;
	};
	//load first level of tree
	this.tree.loadJSONObject(loadJson());
	var itemId = this.tree.getSelectedItemId();
	this.openItem(itemId);
	this.openPathDocs(itemId);
};

dptMenuTreeCls.prototype.openPathDocs = function(id) {
	id = id > 0 ? id : 0;
	this.formObj.attr('action', this.openUrl.toString().replace('#id#', id)).trigger('submit');
};

dptMenuTreeCls.prototype.openItem = function(id) {
	if(id <= 0) {
		return false;
	}
	
	this.tree.selectItem(id, true);
	this.tree.openItem(id);
};

dptMenuTreeCls.prototype.updateTreeSize = function() {
	
};

dptMenuTreeCls.prototype.reloadTree = function(id) {
	$('#doctree_box *').remove();
	this.loadTree();
	this.openItem(id);
};

var objTree = new dptMenuTreeCls();

$(document).ready(function() {
	var school_id = $('#school_id').val();
	var img_path = $('#img_path').val();
	var open_url = $('#open_url').val();
	
	objTree.setSchoolId(school_id);
	objTree.setImagePath(img_path);
	objTree.setFormObj($('#goto_form'));
	objTree.setOpenUrl(open_url);
	
	objTree.loadTree();
});
