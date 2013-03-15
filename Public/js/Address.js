function AddressCls() {
	this.baseUrl = "/Public/Area/";
	this.element_id_mark = "show_area";
	this.css_class_name = "pulldown_menu";
	this.cache = [];
	
	this.attachEvent();
}
AddressCls.prototype.attachEvent = function() {
	var self = this;
	$('[id^="' + self.element_id_mark + '"]').each(function() {
		var element_id = $(this).attr('id');
		//初始化div信息
		self.fillShowAreaDiv(element_id);
		self.attachEventForShowAreaDiv(element_id);
		self.initDataForShowAreaDiv(element_id);
	});
};
AddressCls.prototype.fillShowAreaDiv = function(element_id) {
	var self = this;
	var suffix = element_id.substr(self.element_id_mark.length);
	var Obj = $('#' + element_id);
	$('<select id="province"><option value="-1">请选择省</option></select>').addClass(self.css_class_name).appendTo(Obj);
	$('<select id="city"><option value="-1">请选择市</option></select>').addClass(self.css_class_name).appendTo(Obj);
	$('<select id="county"><option value="-1">请选择县/区</option></select>').addClass(self.css_class_name).appendTo(Obj);
	$('<input type="hidden" id="area_id" name="area_id' + suffix + '"/>').appendTo(Obj);
};
AddressCls.prototype.initDataForShowAreaDiv = function(element_id) {
	var self = this;
	var context = $('#' + element_id);
	var init_area_id = parseInt($('#init_area_id', context).val());
	if(isNaN(init_area_id) || init_area_id < 0) {
		init_area_id = 0;
	}
	var is_init = init_area_id > 0 ? 1 : 0;
	var json = self.loadData(init_area_id, is_init);
	self.fillSelectWithJson($('#province', context), json.data.province);
	self.fillSelectWithJson($('#city', context), json.data.city);
	if(!$.isEmptyObject(json.data.county)) {
		self.fillSelectWithJson($('#county', context), json.data.county);
		$('#county', context).show();
	} else {
		$('#county', context).hide();
	}
	self.reloadCurrentAreaId(element_id);
};
AddressCls.prototype.attachEventForShowAreaDiv = function(element_id) {
	var self = this;
	var context = $('#' + element_id);
	$('#province', context).unbind('change').bind('change', function() {
		var area_id = parseInt($(this).val());
		if(isNaN(area_id)) {
			area_id = -1;
		}
		$('#city option:gt(0),#county option:gt(0)', context).remove();
		if(area_id > 0) {
			var json = self.loadData(area_id, 0);
			//用请求到得数据填充后即选项，如果没有则隐藏
			self.fillSelectWithJson($('#city', context), json.data.city);
			if(!$.isEmptyObject(json.data.county)) {
				self.fillSelectWithJson($('#county', context), json.data.county);
				$('#county', context).show();
			} else {
				$('#county', context).hide();
			}
		}
		self.reloadCurrentAreaId(element_id);
	});
	$('#city', context).unbind('change').bind('change', function() {
		var area_id = parseInt($(this).val());
		if(isNaN(area_id)) {
			area_id = -1;
		}
		$('#county option:gt(0)', context).remove();
		if(area_id > 0) {
			var json = self.loadData(area_id, 0);
			//用请求到得数据填充后即选项，如果没有则隐藏
			if(!$.isEmptyObject(json.data.county)) {
				self.fillSelectWithJson($('#county', context), json.data.county);
				$('#county', context).show();
			} else {
				$('#county', context).hide();
			}
		}
		self.reloadCurrentAreaId(element_id);
	});
	$('#county', context).unbind('change').bind('change', function() {
		self.reloadCurrentAreaId(element_id);
	});
};
AddressCls.prototype.loadData = function(area_id, is_init) {
	var self = this;
	var params = {
		'area_id':area_id,
		'init':is_init
	};
	var json_datas = self.getCache(area_id, is_init);
	if($.isEmptyObject(json_datas)) {
		$.ajax({
			type:'get',
			url:self.baseUrl + 'getAreaList',
			data:params,
			dataType:'json',
			async:false,
			success:function(json) {
				json_datas = json;
			}
		});
		self.setCache(area_id, is_init, json_datas);
	}
	return json_datas;
};
AddressCls.prototype.reloadCurrentAreaId = function(element_id) {
	var context = $('#' + element_id);
	var province_id = $('#province', context).val();
	var city_id = $('#city', context).val();
	var county_id = $('#county', context).val();
	if(!isNaN(county_id) && county_id > 0) {
		$('#area_id', context).val(county_id);
	} else if(!isNaN(city_id) && city_id > 0) {
		$('#area_id', context).val(city_id);
	} else if(!isNaN(province_id) && province_id > 0) {
		$('#area_id', context).val(province_id);
	} else {
		$('#area_id', context).val(0);
	}
};
AddressCls.prototype.fillSelectWithJson = function(selectObj, json) {
	if($.isEmptyObject(json) || $.isEmptyObject(selectObj)) {
		return false;
	}
	for(var i in json) {
		$('<option value="' + json[i].value + '">' + json[i].innerHtml + '</option>').attr('selected', json[i].selected ? 'selected' : '').appendTo(selectObj);
	}
	return true;
};
AddressCls.prototype.setCache = function(area_id, is_init, json) {
	var cache_id = area_id.toString() + "_" + is_init.toString();
	this.cache[cache_id] = json;
};
AddressCls.prototype.getCache = function(area_id, is_init){
	var cache_id = area_id.toString() + "_" + is_init.toString();
	if(!$.isEmptyObject(this.cache[cache_id])) {
		return this.cache[cache_id];
	}
	return {};
};
$(document).ready(function() {
	new AddressCls();
});