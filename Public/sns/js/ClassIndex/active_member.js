function active_member() {
	this.active_member_cache = [];
	this.personal_member_cache = [];
	this.getactivemember();
	this.showallmember();
	this.showtab();
	this.hidetab();
	this.privatemsg();
	this.add_friend_show();
	this.attachEventUserDefine();
	this.send_private_msg();
};

active_member.prototype.getactivemember = function() {
	var context = $("#active_member");
	$.ajax({
		type:'get',
		url:'/Api/Active/getactivemember/class_code/'+ $("#class_code").val(),
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status>0){
				$("p[class='f14 p_width']>span:first", context).html(parseInt(json.data.teacher_num) + parseInt(json.data.student_num) + "人");
				$("p[class='f14 p_width']>span[class='f13 pr10']", context).html("老师" + json.data.teacher_num + "人");
				$("p[class='f14 p_width']>span[class='f13']", context).html("学生" + json.data.student_num + "人");
				
				for(var i in json.data.active_member) {
					var data = json.data.active_member[i];
					var obj_p = $("div[class='class_people_small']>p", context);
					if(data.is_live == true) {
						$(obj_p).append('<a id="info_'+data.client_account+'" href="/Sns/PersonIndex/Index/index/client_account/' + data.client_account + '" target="_blank"><img style="border:1px solid green;" src="' + data.client_headimg_url + '"></a>');
					}else{
						$(obj_p).append('<a id="info_'+data.client_account+'" href="/Sns/PersonIndex/Index/index/client_account/' + data.client_account + '" target="_blank"><img src="' + data.client_headimg_url + '"></a>');
					}
				}
			}
		}
	});
};

active_member.prototype.showtab = function(){
	var me = this;
	$("a[id^='info_'],dl[id^='allinfo_']").live("mouseover", function(){
		var uid = (this.id.toString().match(/(\d+)/) || [])[1];
		var cache_key = "client_account:" + uid;
		var cache_datas = me.personal_member_cache[cache_key] || {};
		if($.isEmptyObject(cache_datas)){
			$.ajax({
				type:"post",
				url:"/Sns/ClassIndex/Index/getinfotab/uid/"+uid,
				dataType:"json",
				async:false,
				success:function(json) {
					cache_datas = me.personal_member_cache[cache_key] = json.data || {};
				}
			});
		}
		var height = $("img", this).css("height");
		var left = $(this).offset().left + 'px';
		var top = parseInt($(this).offset().top) + parseInt(height) + 'px';
		if(cache_datas.client_type == 1) {
			var divObj = $("#teacher_info");
			
			divObj.renderHtml({
				teacher_info:cache_datas
			});
			
			divObj.css({
				'z-index':999,
				"position":"absolute",
				"top":top,
				"left":left
			}).show();
			$("#student_info").hide();
			$("p,div,dl", divObj).css({'z-index':999});
		}else{
			var divObj = $("#student_info");
			
			divObj.renderHtml({
				student_info:cache_datas
			});
			
			divObj.css({
				'z-index':999,
				"position":"absolute",
				"top":top,
				"left":left
			}).show();
			$("#teacher_info").hide();
		}
	});
	
	$("a[id^='info_'],dl[id^='allinfo_']").live("mouseleave",function(){
		$("#teacher_info,#student_info").trigger("mouseleave");
	});
};
active_member.prototype.hidetab = function(){
	$("#teacher_info,#student_info").mouseleave(function(){
		$(this).hide();
	});
	$("#teacher_info,#student_info").mouseover(function(){
		$(this).show();
	});
};

active_member.prototype.showallmember = function(){
	var context = $("#active_member");
	var me = this;
	$("p[class='f14 p_width']", context).click(function(){
		var class_code = $("#class_code").val();
		var cache_key = "active_member:"+class_code;
	    var cache_datas = me.active_member_cache[cache_key] || {};
	    if($.isEmptyObject(cache_datas)){
			$.ajax({
				type:'get',
				url:'/Sns/ClassIndex/Index/getclientclass/class_code/'+ class_code,
				dataType:'json',
				async:false,
				success:function(json) {
					if(json.status>0){
						cache_datas = me.active_member_cache[cache_key] = json.data || {};
						for(var i in cache_datas) {
							var data = cache_datas[i];
							var dlObj = $("dl:last", context).clone();
							var ancestorObj = $("dl:last", context).closest(".class_people_big");
							$(ancestorObj).append(dlObj.show().attr("id", "allinfo_"+data.client_account));

							if(data.is_live == true) {
								$("dt", dlObj).addClass('side_color');
							}else{
								$("dt", dlObj).removeClass('side_color');
							}
							$("img", dlObj).attr('src',data.client_headimg_url);
							$("img", dlObj).parent().attr({
								'href':"/Sns/PersonIndex/Index/index/client_account/" + data.client_account,
								'target':'_blank'
							});
							$("dd", dlObj).html(data.client_name);
						}
					}
				}
			});
	    }

	    if($("div[class='class_people_big']", context).css("display") == "block") {
	    	$("a", $("p[class='f14 p_width']")).removeClass("icon_upsjx").addClass("icon_dsjx");
	    }else{
	    	$("a", $("p[class='f14 p_width']")).removeClass("icon_dsjx").addClass("icon_upsjx");
	    }
		$("div[class='class_people_big']", context).toggle(500);
		$("div[class='class_people_small']", context).toggle(500);
	});
};

active_member.prototype.add_friend_show = function(){
	$("input[id^='client_account_']").live("click", function(){
		var Obj_div = $(this).closest(".stu_card");
		var friend_uid = (this.id.toString().match(/(\d+)/) || [])[0];
		var client_name = $("#client_name_" + friend_uid, $(Obj_div)).val();
		$("span:first", $("#add_frient_div")).html("");
		$("#add_frient_div").trigger("openEvent",[{
			data: {
				friend_uid:friend_uid,
				client_name:client_name
			}
		}]);
	});
};

active_member.prototype.attachEventUserDefine = function(){
	$("#add_frient_div").bind({
			openEvent:function(evt, options) {
				var me = this;
			    var divObj = $(this);
		    	options = options || {};
		    	$(this).data('options', options);
				art.dialog({
					id:'add_friend_div',
					titile:'添加好友',
					content:$('#add_frient_div').get(0),
					init:function(){
						$("span:first", $("#add_frient_div")).html(options.data.client_name);
						var param = {};
						param.accept_account = options.data.friend_uid;
						var sendBoxObj = $('#add_friend_textarea').sendBox({
							//加载工具条，多个选项之间使用逗号隔开，目前支持：表情：emoto，文件上传：upload(form表单提交的文件的名字为:pic)
							panels:'',
							//设置编辑框中的字符数限制
							chars:30,
							//限制文件上传大小,(单位是：m 兆)
							file_size:2,
							//表单的提交类型，建议使用post的方式，支持(get, post)
							type:'post',
							//表单的post数据
							data:param,
							//表单提交到的位置
							url:'/Sns/Friend/Manage/add_friend',
							//数据返回格式，支持：json,html等数据格式，于success回调函数的数据格式保持一致
							dataType:'json',
							//表单提交前验证信息，返回false表示验证失败，表单不提交；返回true表示通过验证；
							beforeSubmit:function() {
								if(sendBoxObj.getSource() == ""){
									$.showTip("请您输入附加信息");
									return false;
								}
								return true;
							},
							//服务器返回数据后的回调函数
							success:function(json) {
								if(json.status > 0){
									var dialogObj = art.dialog.list['add_friend_div'];
									if(!$.isEmptyObject(dialogObj)) {
										dialogObj.close();
									}
									$.showSuccess("您的好友添加请求已经发送成功，正在等待对方确认。");
									return ;
								}
								
								$.showError(json.info);
								
							}
						}, true);
					}
				});
		}
	});
};


active_member.prototype.privatemsg = function(){
	$("#add_privatemsg_div").bind({
			openEvent:function(evt, options) {
				var me = this;
			    var divObj = $(this);
		    	options = options || {};
		    	$(this).data('options', options);
				art.dialog({
					id:'add_privatemsg_div',
					titile:'添加好友',
					content:$('#add_privatemsg_div').get(0),
					init:function(){
						$("span:first", $("#add_privatemsg_div")).html(options.data.client_name);
						var sendBoxObj = $('#add_privatemsg_textarea').sendBox({
							//加载工具条，多个选项之间使用逗号隔开，目前支持：表情：emoto，文件上传：upload(form表单提交的文件的名字为:pic)
							panels:'emote,upload',
							//设置编辑框中的字符数限制
							chars:30,
							//限制文件上传大小,(单位是：m 兆)
							file_size:2,
							//表单的提交类型，建议使用post的方式，支持(get, post)
							type:'post',
							//表单的post数据
//							data:param,
							//表单提交到的位置
							url:'/Sns/PrivateMsg/PrivateMsg/add_private_msg/to_uid/' + options.data.to_uid,
							//数据返回格式，支持：json,html等数据格式，于success回调函数的数据格式保持一致
							dataType:'json',
							//表单提交前验证信息，返回false表示验证失败，表单不提交；返回true表示通过验证；
							beforeSubmit:function() {
								if(sendBoxObj.getSource() == ""){
									$.showTip("请您输入私信信息");
									return false;
								}
								return true;
							},
							//服务器返回数据后的回调函数
							success:function(json) {
								if(json.status > 0){
									var dialogObj = art.dialog.list['add_privatemsg_div'];
									if(!$.isEmptyObject(dialogObj)) {
										dialogObj.close();
									}
									$.showSuccess("私信发送成功");
									return ;
								}
								
								$.showError(json.info);
								
							}
						}, true);
					}
				});
		}
	});
};

active_member.prototype.send_private_msg = function(){
	$("input[id^='private_msg_']").live("click", function(){
		var Obj_div = $(this).closest(".stu_card");
		var to_uid = (this.id.toString().match(/(\d+)/) || [])[0];
		var client_name = $("#client_name_" + to_uid, $(Obj_div)).val();
		$("span:first", $("#add_privatemsg_div")).html("");
		$("#add_privatemsg_div").trigger("openEvent",[{
			data: {
				to_uid:to_uid,
				client_name:client_name
			}
		}]);
	});
};


$(function(){
	new active_member();
});