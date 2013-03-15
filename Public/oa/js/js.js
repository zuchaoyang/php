// JavaScript Document
function showDiv(){
document.getElementById('popDiv').style.display='block';
document.getElementById('popIframe').style.display='block';
document.getElementById('bg').style.display='block';
}
function closeDiv(){
document.getElementById('popDiv').style.display='none';
document.getElementById('bg').style.display='none';
document.getElementById('popIframe').style.display='none';
}

function kaiDiv(){
document.getElementById('popDiv1').style.display='block';
document.getElementById('popIframe1').style.display='block';
document.getElementById('bg1').style.display='block';
}
function guanDiv(){
document.getElementById('popDiv1').style.display='none';
document.getElementById('bg1').style.display='none';
document.getElementById('popIframe1').style.display='none';
}


function show(x){
	    if(x==1){document.getElementById("koko").style.display="block"}
		if(x==2){document.getElementById("koko").style.display="none"}

	}