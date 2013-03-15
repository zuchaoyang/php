function checksetwork(){
var content = $.trim($("#news_content").val());
content = content.replace("<P>","");
content = content.replace("</P>","");
content = content.replace("&nbsp;","");
content = content.replace("<BR>","");
content= delHtmlTag(content);
content=content.replace(/&nbsp;/ig, "");
content = content.replace(/(^\s*)(\s*$)/g,"");
var subject_id = document.getElementById("subject_id").value;
var b = true;
if(!subject_id){
	needtoLogTip("请选择科目");
	b = false;
	return b;
} else if(content==null || Trim(content)==""){
	needtoLogTip("内容不能为空");
	b = false;
	return b;
} else if(content.length > 200){
	needtoLogTip("作业内容不能超过200字");
	b = false;
	return b;
}
if(b){
	var fom = document.getElementById("form");
	fom.submit();
}

  function checkempty(){
	if(document.getElementById("news_content").value!=''){
		document.getElementById("showspan").innerHTML=''
	}
  }
  
  function num(){
	if(document.getElementById("showspan").innerHTML != ""){
	  document.getElementById("showspan").innerHTML="";
	}
	var content = document.getElementById("news_content").value;
	document.getElementById("num").innerHTML=content.length;
  }

}



function LTrim(str)     // 左空格 
{  
    var i;  
    for(i=0;i<str.length;i++)  
    {  
    if(str.charAt(i)!=" "&&str.charAt(i)!=" ")break;  
    }  
    str=str.substring(i,str.length);  
    return str;  
}  
function RTrim(str)     // 右空格 
{  
    var i;  
    for(i=str.length-1;i>=0;i--)  
    {  
    if(str.charAt(i)!=" "&&str.charAt(i)!=" ")break;  
    }  
    str=str.substring(0,i+1);  
    return str;  
}  
function Trim(str)  // 两边空格 
{  
    return LTrim(RTrim(str));  
} 