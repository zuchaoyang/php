	function dellog(log_id,pagetype){
		if(confirm("确定要删除吗")){
			window.location="/Homepzone/Pzonelog/del_log/log_id/"+log_id+"/log_status/"+pagetype;
		}
	}

	function changelog(liid){
		if(liid == "log"){
			window.location.href="/Homepzone/Pzonelog/mylogindex/log_account/{$log_account}/pagetype/log";			
		}else{
			window.location.href="/Homepzone/Pzonelog/mylogindex/log_account/{$log_account}/pagetype/cgao";
		}
	}

	//删除相册
	function deletexc(xcid){
		if(confirm("确定删除该相册吗？")){
			window.location.href="/Homepzone/Pzonephoto/deletexc/user_account/{$account}/xcid/"+xcid;
		}
	}

	//创建相册
	function createxc(){

		window.location.href="/Homepzone/Pzonephoto/createxc/user_account/{$account}";
	}
	
	//上传照片
	function upphoto(xcid,userId){
		var str="";
		if(xcid!=""){
			var str="/xcid/"+xcid;
		}
		window.location.href="/Homepzone/Pzonephoto/uploadphoto/user_account/"+userId+str;
	}
	//删除评论
	function delphotoplun(plun_id,account){
		if(confirm("确定删除该条评论吗？")){
			window.location.href="/Homepzone/Pzonephoto/delphotonewplun/plun_id/"+plun_id+"/user_account/"+account;
		}
	}

