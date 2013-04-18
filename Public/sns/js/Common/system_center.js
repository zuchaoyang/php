function system_center(){
	this.class_code = $("#class_code").val();
	this.attachEvent();
	this.delegateEvent();
	this.init();
};

system_center.prototype = {
		init : function(){
			var me = this;
			$.ajax({
				type:'get',
				url:'/Sns/HomePage/Systemcenter/index/class_code/'+me.class_code,
				dataType:'json',
				async:false,
				success:function(json) {
					if(json.status < 0) {
						return false;
					}
					me.fillSystemList(json.data);
				}
			});
		},
		attachEvent : function() {
			$("#icon_show").toggle(
				function(){
					$(this).attr('class','icon_show_down');
					$(this).attr('title','展开');
					$('div.shu',$("#system_center_selector")).slideToggle('slow');
//					$('dl.heng',$(".heng_list")).slideToggle('slow');
				},
				function() {
					$(this).attr('class','icon_show_up');
					$(this).attr('title','收起');
					$('div.shu',$("#system_center_selector")).slideToggle('slow');
//					$('dl.heng',$(".heng_list")).slideToggle('slow');
				}
			);
		},
		delegateEvent : function() {
			var me = this;
			$('#sys_del').live('click', function() {
				var parentObj = $(this).parents('.info');
				//删除记录
				
				//移除页面对象
				parentObj.remove();
				
			});
		},
		fillSystemList : function(sys_list) {
			var me = this;
			sys_list = sys_list || {};
			var parentObj = $("#system_center_selector");
			for(var i in sys_list) {
				var item = sys_list[i];
				item.sys_img_obj = '<img src="' + item.sys_img  + '" />';
				var sysObj = $('#system_center_clone_selector').clone().removeAttr('id');
				sysObj.renderHtml({
					items:item
				});
				$(sysObj).appendTo(parentObj);
			}
		}
};

$(document).ready(function(){
	new system_center();
});