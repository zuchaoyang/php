$(document).ready(function(){
	$('#closeFrm').click(function(){
		parent.tb_remove()
	});
	$("#downList_Title >span>img").click(function(){
		$('#downList_div').hide();
	});
	$("#photoslisdeatial").hide();


});	

	function lockScreen(contentvalue){
		art.dialog({
			lock: true,
			background: '#600', // 背景色
			opacity: 0.87,	// 透明度
			content: contentvalue,
			icon: 'error'
		});
	
	}
	
	function needtoLogTip(tipMsg){
		art.dialog.alert(tipMsg);
	}






/*相册-----------------------------------*/
	
	function openpycomment(rowsid){
		art.dialog.open('/Homeclass/Myclass/pyCommentOpen/rowsid/'+rowsid);
	}
	function openmypycomment(rowsid){
		art.dialog.open('/Homeclass/Myclass/mypyCommentOpen/rowsid/'+rowsid);
	}

	function communicatemore(class_code){
		art.dialog.open('/Homeclass/Myclass/Communicatemore/class_code/'+class_code);
	}
	
	function openModfiyGroup(group_id){
		art.dialog.open('/Homefriends/Friendsmanage/modifyfriendgroup/group_id/'+group_id);
	}
	
	function openModfiyfriendGroup(group_id,account){
		if(group_id==''){
		group_id =0;
		}
		art.dialog.open('/Homefriends/Friendsmanage/changefriendgroup/group_id/'+group_id+'/fid/'+account);
	}
	
		
	function photoplunComback(plun_id,add_account,type){
		document.getElementById("curhfuser").value = add_account;
		document.getElementById("plid").value = plun_id;
		document.getElementById("backtype").value = type;

		var contentValue = $("#classPhotoplunsing_box").html();
		var dialog = art.dialog({
			follow: document.getElementById('followTestBtn'+plun_id),
			title: '回复评论',
			content: contentValue
		});	
	}
	
	//function plfaceshow(objid){
	//	art.dialog.data('homeDemoPath', '/Public/local/js/artDialog/_doc/');
	//	art.dialog.open('/Public/local/js/artDialog/_doc/www_facelist.html?objid='+objid);
	//}

	//查看评论
	function showlistcomment(showid){
		//art.dialog.open('/Homepzone/Pzonephoto/createxc/user_account/');
		var contentValue = $("#pcount_content_"+showid).html();
		var dialog = art.dialog({
			follow: document.getElementById('pcount_'+showid),
			title: '查看评论',
			content: contentValue
		});	
	}


	function chkpluncontent(){
		var objcontent = $("#photoplsing").val();
		if(objcontent==""){
				alert('请输入评论内容');
			$("#photoplsing").focus();
			return false;
		}
		
	}

/*结束相册-----------------------------------*/



function changeClassHmtl(){

	var contentValue = $("#classChange").html();
	var dialog = art.dialog({
		follow: document.getElementById('spanbtnclass'),
		title: '切换班级',
		content: contentValue
	});	

}



function contactfamily(objuid){
	art.dialog.open('/Homeclass/Class/getStudentsfamily/account/'+objuid);
}


//上传头像
function uploadheaderimg(){
	art.dialog.open('/Uploadphoto/changephoto');
}


function __showAccountFamily(objuid){
	///Homeclass/classStudentsFamily/account/
    tb_show(null,"/Homeclass/Class/getStudentsfamily/account/"+objuid+"?keepThis=true&TB_iframe=true&height=310&width=525&modal=true",false)
}

function __showOpenFoll(url,width,height){
    tb_show(null,url+"?keepThis=true&TB_iframe=true&height="+height+"&width="+width+"&modal=true",false)
}

function __showModfilyXcName(url,width,height){
	var intdelphoto = $("#delphoto").val();
	url = url + "/photoid/"+intdelphoto;
	//alert(url);
    tb_show(null,url+"?keepThis=true&TB_iframe=true&height="+height+"&width="+width+"&modal=true",false)
}


function checkinfo(){
var checkflag = true;
if(($("#loginuser").val()).replace(/(^\s*)|(\s*$)/g, "") == "" || ($("#loginpwd").val()).replace(/(^\s*)|(\s*$)/g, "") == ""){
	$("#login_account_pwd").css("color","#FF0000").html("请您完整输入账号和密码");
	checkflag = false;
}
return checkflag;
}

function loginsubmit() {
	if (checkinfo()) {
		document.forms[0].action="/Login/checkaccount";
		document.forms[0].submit();
	}
}

function keyLogin(){
if (event.keyCode==13)   //回车键的键值为13
  loginsubmit();  //调用登录按钮的登录事件
}



function backphoto(flag,xcid){
if(flag=='1'){
	window.location.href="/Homepzone/Pzonephoto/photoindex/user_account/{$account}";
}else
	window.location.href="/Homepzone/Pzonephoto/xcmanager/user_account/{$account}/xcid/"+xcid;
}


//删除评论
function delphotoplun(plun_id,account){
if(confirm("确定删除该条评论吗？")){
	window.location.href="/Homepzone/Pzonephoto/delphotonewplun/plun_id/"+plun_id+"/user_account/"+account;
}
}



//上传照片
function upphoto(xcid,class_code){
	var str="";
	if(xcid!=""){
		var str="/xcid/"+xcid;
	}
	window.location.href="/Homepzone/Pzonephoto/uploadphoto/class_code/"+class_code+str;
}


function subphotoupdate(xcid){
	var objxcid = $("#xiangce").val();
	var objkey = $("#hiddenKey").val();
	var objclass_code = $("#class_code").val();
	
	var objwhereurl = $("#whereurl").val();
	var objclass_code = $("#class_code").val();
	window.location.href="/Homepzone/Pzonephoto/modifyAllbumId/xcid/"+objxcid+"/class_code/"+objclass_code;
	//+"/key/"+objkey+"/class_code/"+objclass_code
}

//班级相册照片上传
function classupphoto(xcid,class_code){
	var str="";
	if(xcid!=""){
		var str="/xcid/"+xcid;
	}
	window.location.href="/Homeclass/Class/uploadphoto"+str+'/class_code/'+class_code;
}
function classsubphotoupdate(){
	var objxcid = $("#xiangce").val();
	var objkey = $("#hiddenKey").val();
	var objclass_code = $("#class_code").val();
	//alert("/Homeclass/Class/modifyAllbumId/xcid/"+objxcid+"/key/"+objkey+"/class_code/"+objclass_code);
	window.location.href="/Homeclass/Class/modifyAllbumId/xcid/"+objxcid+"/key/"+objkey+"/class_code/"+objclass_code;
}


//通用删除控制
function Jqy_deleteBtn(msg,title,url,params){
	var sWidth,sHeight; 
	sWidth=document.body.offsetWidth - 22;//浏览器工作区域内页面宽度 
	sHeight=screen.height;//屏幕高度（垂直分辨率） 

	var bgObj=document.createElement("div");//创建一个div对象（背景层） 
	bgObj.setAttribute( "id", "bgDiv"); 
	bgObj.style.position="absolute"; 
	bgObj.style.top= "0"; 
	bgObj.style.background= ""; 
	bgObj.style.filter= "progid:DXImageTransform.Microsoft.Alpha(style=1,opacity=55,finishOpacity=95 "; 
	bgObj.style.opacity= "0.6"; 
	bgObj.style.left= "0"; 
	
	bgObj.style.width=sWidth   +   "px"; 
	bgObj.style.height=sHeight   +   "px"; 
	bgObj.style.zIndex   =   "10000"; 
	document.body.appendChild(bgObj);//在body内添加该div对象 
	
	jConfirm(msg, title, function(r) {

		if(r){
			//alert(url + params);
			window.location = url + params ;
		}else{
			 document.body.removeChild(bgObj);
		}
	});	

}


//是否输入了内容
function funcChina(obj){ 
var objbln = false;
if(/.*[\u4e00-\u9fa5]+.*$/.test(obj)) 
{ 
	objbln = true;
} 
return objbln; 
} 

function delHtmlTag(str)
{
 return str.replace(/<\/?.+?>/g,"");//去掉所有的html标记
} 


//简化JS

function GetObj(objName){
	if(document.getElementById){
		return eval('document.getElementById("' + objName + '")');
	}else if(document.layers){
		return eval("document.layers['" + objName +"']");
	}else{
		return eval('document.all.' + objName);
	}
}/*
function dcs(s,i){if(i&&$(i))$(i).parentNode.removeChild($(i));var js=document.body.appendChild(document.createElement("SCRIPT"));js.src=s;if(i)js.id=i}
function els(i, t) {if(typeof(i)=="string")i=$(i);return i.getElementsByTagName(t)}
function setCookie(k, v, opt){var val = encodeURIComponent(v);if(!opt) opt = {duration:365};if(opt && opt.duration){var d = new Date();d.setTime(d.getTime() + opt.duration*24*3600*1000);val += ';expires=' + d.toGMTString();}document.cookie = k + '=' + val + ';';}
function getCookie(k){var c = document.cookie.split("; "); for(var i=0; i<c.length; i++) { var p = c[i].split("="); if(k == p[0]) try{return decodeURIComponent(p[1]);}catch(e){return null;}}return null;}
function ajax(url, callback, tk){var xhr = (window.XMLHttpRequest) ? new XMLHttpRequest() : (document.all ? new ActiveXObject('Microsoft.XMLHTTP') : null);if(!xhr){callback(null, tk);return null;}xhr.open('GET', url, true);xhr.onreadystatechange = function(ev) {if(xhr.readyState != 4) return;if((xhr.status >= 200) && (xhr.status < 300))callback(xhr.responseText, tk);else{callback(null, tk)}};try{xhr.send(null)}catch(e){}return xhr;}
function gb2312(key) {var r = ""; for(var i=0;i<key.length;i++){ var t = key.charCodeAt(i); if(t>=0x4e00 || t==0x300A || t==0x300B){ try{execScript("ascCode=hex(asc(\""+key.charAt(i)+"\"))", "vbscript"); r += ascCode.replace(/(.{2})/g, "%$1"); }catch(e){}}else{r += escape(key.charAt(i))} } return r; }
function pos(ele){var el = ele, left = 0, top = 0;do {left += el.offsetLeft || 0;top += el.offsetTop || 0;el = el.offsetParent;} while (el);return {x: left, y: top};}
function set_home(o, url) {if(document.all){o.target = "_self";o.style.behavior = "url(#default#homepage)";o.setHomePage(url);}else{o.target = "_search";try { netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect"); var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch);prefs.setCharPref('browser.startup.homepage', url);} catch (e) {}}}
function AddFavorite(){var url=document.location.href+"?sc",title=document.title;try{window.external.addFavorite(url,title)}catch(e){try{window.sidebar.addPanel(title,url,'')}catch(e){alert("请按 Ctrl+D 键添加到收藏夹")}}}
*/


var ShadeDiv = {
 objid : null,
 Shade : document.createElement('div'),
 ShadeID : 'Shade',
 width : 400,
 height : 300,
 Position : function(){
  if (!ShadeDiv.objid){
   ShadeDiv.objid = null;
   return;
  }
  var de = document.documentElement;
  var w = window.innerWidth || self.innerWidth || (de&&de.clientWidth) || document.body.clientWidth;
  var ch = window.innerHeight || self.innerHeight || (de&&de.clientHeight) || document.body.clientHeight;
  if (self.pageYOffset) {
   var st = self.pageYOffset;
  } else if (document.documentElement && document.documentElement.scrollTop){  // Explorer 6 Strict
   var st = document.documentElement.scrollTop;
  } else if (document.body) {// all other Explorers
   var st = document.body.scrollTop;
  }
  if (window.innerHeight && window.scrollMaxY) {
   var sh = window.innerHeight + window.scrollMaxY;
  } else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
   var sh = document.body.scrollHeight;
  } else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
   var sh = document.body.offsetHeight;
  }
  ShadeDiv.Shade.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=60,finishOpacity=100,style=0)';
  ShadeDiv.Shade.style.height = (sh > ch ? sh : ch) + 'px';
  ShadeDiv.Shade.style.width = w + 'px';
  var pos = [], pw;
  pw = ShadeDiv.width;
  pos[0] = (w-pw)/2;
  pos[1] = (ch-(ShadeDiv.height || 300))/2 -100+st;
  //window.status="ch:"+ch+"st:"+st+"post[1]:"+pos[1]+"ShadeDiv.clientHeight"+ShadeDiv.objid.clientHeight;
  if (navigator.product && navigator.product == 'Gecko'){
   pw -= 40;
  }
  ShadeDiv.objid.style.width = ShadeDiv.width + 'px';
  ShadeDiv.objid.style.height = ShadeDiv.height + 'px';
  ShadeDiv.objid.style.left = pos[0] + 'px';
  ShadeDiv.objid.style.top = pos[1] + 'px';
  ShadeDiv.Shade.style.display = 'block';
  ShadeDiv.objid.style.display = 'block';
 },
 Show : function(id,w,h){
  ShadeDiv.height = parseInt(h);
  ShadeDiv.width = parseInt(w);
  ShadeDiv.Shade.id = ShadeDiv.ShadeID;
  ShadeDiv.objid = document.getElementById(id);
  document.body.insertBefore(ShadeDiv.Shade,null);
  ShadeDiv.Position();
 },
 Close : function(){
  if (ShadeDiv.objid==null){
   return;
  }
  ShadeDiv.Confirmed();
 },
 Confirmed : function() {
  ShadeDiv.objid.style.display = 'none';
  ShadeDiv.Shade.style.display = 'none';
  document.body.removeChild(ShadeDiv.Shade);
 }
}


function chkCheckBoxChs(objNam){
	var obj = document.getElementsByName(objNam);
	var objLen= obj.length; 
	var objYN;
	var i;
	objYN=false;
	for (i = 0;i< objLen;i++){
		if (obj [i].checked==true) {
			objYN= true;
		break;
		}
	}
return objYN;

}
/*信-----------------------------------*/

function openxin(){
	art.dialog.load('/Homeuser/Index/openxin', false);
	//art.dialog.open('/Homeuser/Index/openxin',{title:'给用户的一封信',width:320,height:400,lock:true});
}
