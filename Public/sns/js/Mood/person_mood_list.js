
function dump(obj) {
	for(var i in obj) {
		alert(i + "=>" + obj[i]);
	}
}

function person_mood_list() {
	this.attachEvent();
	this.init();
}

person_mood_list.prototype = {
	init:function() {
		var me = this;
		//加载第一页的说说信息
		me.loadMoodDatas(1);
		me.getPersonMoodStat();
		
		//将对象绑定到div上
		$('#mood_main').data('handler', me);
	},
		
	attachEvent:function() {
		var me = this;
		//绑定说说发布框的相关事件
		$('.say_textarea').sendBox({
			panels:'emote,upload',
			chars:120,
			file_size:2,
			type:'post',
			url:'/Sns/Mood/PersonMood/publishAjax',
			dataType:'json',
			beforeSubmit:function() {
				return true;
			},
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
				//追加到当前的说说列表中
				if(!$.isEmptyObject(json.data)) {
					var data = json.data;
					var mood_info = data.mood_info;
					mood_unit.create(mood_info || {}).prependTo($('#show_mood_list_div'));
				}
				me.getPersonMoodStat();
			}
		});
		
		//加载更多说说信息
		$('#load_more_mood_a').click(function() {
			var page = $(this).data('page') || 1;
			var divObj = $(this).closest('#more_mood_div');
			var hasNextPage = me.loadMoodDatas(page + 1);
			if(!hasNextPage) {
				divObj.hide();
			} else {
				divObj.show();
			}
			return false;
		});
	},
	
	loadMoodDatas:function(page) {
		var me = this;
		page = page > 1 ? page : 1;
		var hasNextPage = true;
		$.ajax({
			type:'get',
			url:'/Sns/Mood/PersonMood/getPersonMoodListAjax/page/' + page,
			dataType:'json',
			async:false,
			success:function(json) {
				if(json.status < 0 || $.isEmptyObject(json.data)) {
					hasNextPage = false;
				} else {
					me.fillMoodList(json.data || {});
				}
			}
		});
		
		return hasNextPage;
	},
	
	fillMoodList:function(mood_list) {
		mood_list = mood_list || {};
		if($.isEmptyObject(mood_list)) {
			return false;
		}
		var parentObj = $('#show_mood_list_div');
		for(var i in mood_list) {
			var mood_info = mood_list[i];
			mood_unit.create(mood_info).appendTo(parentObj);
		}
	},
	
	getPersonMoodStat:function() {
		$.ajax({
			type:'get',
			url:'/Sns/Mood/PersonMood/getPersonMoodStatAjax',
			dataType:'json',
			success:function(json) {
				var mood_nums = json.data || 0;
				$('#mood_stat_span').html(mood_nums);
			}
		});
	}
	
};

function mood_unit() {
	this.delegateEvent();
}

mood_unit.create=function(mood_info) {
	var divObj = $('#mood_unit_div').clone().removeAttr('id').show();
	divObj.renderHtml({
		mood_info:mood_info || {}
	});
	
	//加载图片信息
	$('img', divObj).each(function() {
		var data_original = $(this).attr('data-original');
		if(data_original) {
			var imgObj = $(this);
			var img = new Image();
			img.src = data_original;
			img.onload = function() {
				var height = img.height;
				var width = img.width;
				if(width > 620) {
					img.width = 620;
					img.height = (620 / width) * height;
				}
				imgObj.replaceWith($(img));
			};
		}
	});
	
	return $(divObj);
};

mood_unit.prototype = {
	delegateEvent:function() {
		//删除个人说说按钮
		$('.delete_mood_selector').live('click', function() {
			var aObj = $(this);
			var ancestorObj = aObj.closest('.mood_unit_selector');
			var mood_id = $('input[name="mood_id"]', ancestorObj).val();
			
			$.showDeleteMood({
				follow:aObj[0],
				url:'/Sns/Mood/PersonMood/deletePersonMoodAjax/mood_id/' + mood_id,
				msg:'您确定要删除该说说信息?',
				callback:function() {
					ancestorObj.remove();
					//刷新说说的记录数
					var handler = $('#mood_main').data('handler');
					handler.getPersonMoodStat();
				}
			});
		});
		
		//个人说说的评论按钮
		$('.comment_mood_selector').live('click', function() {
			var aObj = $(this);
			var ancestorObj = aObj.closest('.mood_unit_selector');
			var mood_id = $('input[name="mood_id"]', ancestorObj).val();
			if(!aObj.data('inited')) {
				//刷新相应的评论数
				function reflush(num) {
					var aObj = $('.comment_mood_selector', ancestorObj);
					var html = aObj.html() || "";
					var pattern = /(\d+)/;
					if(pattern.test(html)) {
						var comment_nums = html.match(pattern)[1];
						html = html.replace(pattern, parseInt(comment_nums) + num);
					} else {
						html = html + "(" + num + ")";
					}
					aObj.html(html);
				}
				
				//加载sendbox的相关事件
				$('.pl_textarea', ancestorObj).sendBox({
					panels:'emote',
					type:'post',
					url:'/Sns/Mood/Comments/publishMoodCommentsAjax',
					dataType:'json',
					data:{
						mood_id:mood_id,
						up_id:0
					},
					success:function(json) {
						if(json.status < 0) {
							$.showError(json.info);
							return false;
						}
						//处理成功后的回调函数
						var divObj = comment_1st_unit.create(json.data || {});
						$('#comment_list_div', ancestorObj).prepend(divObj);
						reflush(1);
					}
				});
				
				//加载评论的相关信息
				var options = {
					show_load_more:false,
					//更新说说的评论数
					callback:function(num) {
						reflush(num);
					}
				};
				$('#comment_list_div', ancestorObj).loadMoodComments(mood_id, options);
				aObj.data('inited', true);
			}
			//处理相关的切换效果
			if($('#send_1st_mood_div', ancestorObj).is(':visible')) {
				$('#send_1st_mood_div,#comment_list_div,#click_see_div', ancestorObj).hide();
			} else {
				$('#send_1st_mood_div,#comment_list_div,#click_see_div', ancestorObj).show();
			}
			return false;
		});
	}	
};

$(document).ready(function() {
	new person_mood_list();
	new mood_unit();
});