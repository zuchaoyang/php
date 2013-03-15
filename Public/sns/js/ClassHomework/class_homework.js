function searchhomework() {
	this.showdate();
	this.getaccepters();
	this.delete_homework();
	this.send();
};

searchhomework.prototype.showdate = function() {
	var self=this;
	$("#start_time").click(function() {
		WdatePicker(); 
	});
	
	$("#end_time").click(function() {
		WdatePicker();
	});
};

searchhomework.prototype.getaccepters = function() {
	$(":button[id^='click_accepters']").click(function() {
		
		var homework_id = $(this).attr('id').toString().match(/(\d+)/)[1];
		
		$.ajax({
			type:"post",
			data:{'homework_id' :homework_id},
			dataType:"json",
			url:"/Sns/ClassHomework/Published/accepters_json",
			async:false,
			success:function(json) {
				//展示内容
			}
		});
	});
;}

searchhomework.prototype.delete_homework = function() {
	$(":button[id^='del']").click(function() {
		var homework_id = $(this).attr('id').toString().match(/(\d+)/)[1];
		if(confirm('确定要删除该作业吗？')) {
			window.location.href='/Sns/ClassHomework/Del/del_homework/homework_id/'+ homework_id;
		} 
	});
};

searchhomework.prototype.send = function() {
	$("#send").click(function() {
		var homework_id = $("#homework_id").val();
		window.location.href='/Sns/ClassHomework/Publish/SendReissue/homework_id/' + homework_id;
	});
};

$(document).ready(function() {
	new searchhomework();
});