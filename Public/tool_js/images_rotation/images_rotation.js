// JavaScript Document
var arr_c = new Array();
arr_c['banner']=new Array();
arr_c['ban1']=new Array();
arr_c['ban2']=new Array();
	function show(divid){	
		var f;
		var n;
		var t=0;
		var count;
		arr_c[divid]['n']=0;
		arr_c[divid]['f']=0;
		count=$('#'+divid+'_list a').length;
		arr_c[divid]['count']=count;
		$('#'+divid+'_list a:not(:first-child)').hide();
		$('#'+divid+'_info').html($('#'+divid+'_list a:first-child').find("img").attr('alt'));
		$('#'+divid+'_info').click(function(){window.open($('#'+divid+'_list a:first-child').attr('href'), "_blank")});
		$('#'+divid+' li').click(function() {
			var i = $(this).text() - 1;
			if (f==i) return;
			f = n = i;
			arr_c[divid]['f']=i;
			arr_c[divid]['n']=i;
			if (i >= count) return;
			$('#'+divid+'_info').html($('#'+divid+'_list a').eq(i).find("img").attr('alt'));
			$('#'+divid+'_info').unbind().click(function(){window.open($('#'+divid+'_list a').eq(i).attr('href'), "_blank")})
			$('#'+divid+'_list a').filter(":visible").fadeOut(500).parent().children().eq(i).fadeIn(1000);
			document.getElementById(divid).style.background="";
			$(this).toggleClass("on");
			$(this).siblings().removeAttr("class");
		});
		t = setInterval("showAuto('"+divid+"')", 3000);
		$('#'+divid).hover(function(){clearInterval(t)}, function(){t = setInterval("showAuto('"+divid+"')", 3000);});
	}
	function showAuto(divid)
	{   
		var n=arr_c[divid]['n'],
		count = arr_c[divid]['count'];
		n = n >=(count - 1) ? 0 : ++n;
		arr_c[divid]['n'] = n;
		arr_c[divid]['f'] = parseInt(arr_c[divid]['f']) + 5;
		$('#'+divid+' li').eq(n).trigger('click');
	}
