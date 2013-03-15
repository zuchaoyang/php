$(function(){
	$('.spxx').click(function(){
		var $surl = this.href;
	   	$sid = $surl.substring($surl.indexOf('#')+1);
	   	var $aurl = '/Admingroup/Schoolmanage/getSchoolInfo?sid='+$sid;
		$.ajax({
			type:"POST", 	//post
			url:$aurl, 
			dataType:"json",	//json
			success:function(data){ 
			/*
			var $a = data.split(',');
            $("#scname").text($a[0]);	
            $("#scaddr").text($a[1]);
            $("#sccode").text($a[2]);
            $("#scdate").text($a[3]);
            $("#sctype").text($a[4]);
            $("#scresource").text($a[5]);
            $("#scmaster").text($a[6]);
            $("#sccontact").text($a[7]);
            $("#classnum").text($a[8]);
            $("#technum").text($a[9]);
            $("#stunum").text($a[10]);
            $("#netmanager").text($a[11]);
            $("#netphone").text($a[12]);
            $("#netemail").text($a[13]);
            $("#oldurl").text($a[14]);
            $("#newurl").text($a[15]);
            */
			var r=data.error;
            if(r.code > 0)
            {
                var d=data.data;
                $("#scname").text(d.school_name);              //学校名称（string）
                $("#scaddr").text(d.school_address);        //学校地址（string）
                $("#sccode").text(d.post_code);                  //邮政编码（string）
                $("#scdate").text(d.school_create_date);//建校时间（string）
                $("#sctype").text(d.school_type);              //学校类型（string）
                $("#gradetype").text(d.grade_type);              //学制类型（string）
                $("#scresource").text(d.resource_advantage);//资源优势（string）
                $("#scmaster").text(d.school_master);          //校长（string）
                $("#sccontact").text(d.contact_person);        //联系方式（string）
                $("#classnum").text(d.class_num);                  //班级数量（string）
                $("#technum").text(d.teacher_num);              //教师数量（string）
                $("#stunum").text(d.student_num);              //学生数量（string）
                $("#netmanager").text(d.net_manager);              //姓名（string）
                $("#netphone").text(d.net_manager_phone);  //联系方式（string）
                $("#netemail").text(d.net_manager_email);  //邮箱设置（string）
                $("#oldurl").text(d.school_url_old);        //原学校网址（string）
                $("#newurl").text(d.school_url_new);        //学校新网址(string)
                $('#popDiv2').show();
            }
            else
            {
                pageTable.closeSelect();
                alert(r.message);
            }
           }
		});
 });

	$('.show').click(function(){
       	var $surl = this.href;
       	var $sid = $surl.substring($surl.indexOf('#')+1);
       	var $rurl = '/Admingroup/Schoolmanage/showRefuseReason?sid='+$sid;
		$.ajax({
			type:"GET", 
			url:$rurl, 
			dataType:"html",
			success:function(data){ 
                 $("#reason").html(data);
            }
		});
 });
})

function showDiv(){
$('#popDiv').show();
$('#popIframe').show();
$('#bg').show();
}
function closeDiv(){
$('#popDiv').hide();
$('#bg').hide();
$('#popIframe').hide();
}
function showDiv1(schoolid){
$('#jxpopDiv').show();
$('#jxpopIframe').show();
$('#jxbg').show();
$("#sid").val(schoolid);
//
$("input[name='wtg'][value='1']").attr("checked","checked");
$("#pStrategy").show();
$("#jxly").hide();
$("#pStrategy_sel").val("A");
$("cmt_content").text("请输入拒绝的理由......");

}
function closeDiv1(){
$('#jxpopDiv').hide();
$('#jxbg').hide();
$('#jxpopIframe').hide();
$('#jxly').hide();
}
function hidly(){
$("#pStrategy").hide();
$('#jxly').show();
}

function show(){
$('#jxly').hide();
$("#pStrategy").show();
}

function showDiv2(){
$('#popDiv2').show();
$('#popIframe2').show();
$('#bg2').show();
}
function closeDiv2(){
$('#popDiv2').hide();
$('#bg2').hide();
$('#popIframe2').hide();
}
function tijiao(){
	var flag = $(":radio[name=wtg][checked]").val();
	if(flag == '2'){
		
		var cmt_content = $("#cmt_content").val();
		if(cmt_content.length<=0 || cmt_content=='请输入拒绝的理由......'){
			alert("请输入未通过原因");
			return ;
		}
		if(cmt_content.length>200){
			alert("原因最长输入200个字符");
			return ;
		}
		var schoolid = $("#sid").val();
		$("#hid").val(cmt_content);
		document.forms[0].action="/Admingroup/Schoolmanage/addSchoolInfo/schoolid/"+schoolid;
		document.forms[0].submit();
	}
	if(flag == '1'){
		var sell = $("#sellid").val();
		var schoolid = $("#sid").val();
		$('#jxpopDiv').hide();
		$('#popemail').show();
		$('#bg').show();
		window.location.href="/Admingroup/Schoolmanage/addSchoolInfo/sellid/"+sell+"/schoolid/"+schoolid;
	}
}