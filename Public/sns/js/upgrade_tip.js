function upgrade_tip() {
	var need_upgrade = $("#need_upgrade").val();
	var uid = $("#uid").val();
	var class_code = $("#class_code").val();
	var secret_key = $("#secret_key").val();
	if(need_upgrade) {
		$.ajax({
			type:'get',
			url:'/Api/Upgrade/index',
			dataType:'json',
			data:{
				'secret_key':secret_key,
				'uid':uid,
				'class_code':class_code
			},
			async:false,
			success:function(json) {
				if(json.status > 0){
					window.location="/Homepage/Homepage/index/class_code/" + class_code;
				}
			}
		});
	}
}
$(document).ready(function() {
	upgrade_tip();
});