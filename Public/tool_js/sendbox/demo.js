$(document).ready(function() {
	//返回值为sendbox对象
	//该插件可以在配置参数中指定模板的渲染参数，格式为:{name}, 则sendbox在渲染的时候会增加相关的属性设置
	var sendBoxObj = $('#textarea_1').sendBox({
		//加载工具条，多个选项之间使用逗号隔开，目前支持：表情：emoto，文件上传：upload(form表单提交的文件的名字为:pic)
		panels:'emote,upload',
		//设置编辑框中的字符数限制
		chars:120,
		//限制文件上传大小,(单位是：m 兆)
		file_size:2,
		//设置编辑框对应的样式,对应查看sendbox相应的目录对应的css文件目录下的css文件中的样式名的后缀,
		skin:'sendbox',
		//表单的提交类型，建议使用post的方式，支持(get, post)
		type:'post',
		//表单提交到的位置
		url:'/Sns/Feed/List/submitAjax',
		//表达提交时的附加信息
		data: {
		
		},
		//数据返回格式，支持：json,html等数据格式，于success回调函数的数据格式保持一致
		dataType:'json',
		//表单提交前验证信息，返回false表示验证失败，表单不提交；返回true表示通过验证；
		beforeSubmit:function() {
			return true;
		},
		//服务器返回数据后的回调函数
		success:function(json) {
			for(var i in json.data) {
				alert(i + "=>" + json.data[i]);
			}
		}
	});
	
	//设置编辑框中的值
	sendBoxObj.setSource('');
	
	//获取编辑框中的值
	sendBoxObj.getSource();
	
	
	//关于返回对象的属性信息
	sendBoxObj = {
		//属性=>Object,jquery对象:当前对应的sendbox内部的textarea对象
		_jTextarea:{},
		//属性=>Object,jquery对象：_jForm,当前对应的sendbox内部的form表单对象
		_jForm:{},
		//方法=>function('要设置的值'),设置编辑器中的内容
		setSource:function(value) {
		
		},
		//方法=>funciton(),获取编辑器的内容
		getSource:function() {
			
		}
	};
	
});