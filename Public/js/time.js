
var tags_before_clock = "<font face='宋体' style='font-size:10pt' color='#323232'>";
var tags_middle_clock = "on";
var tags_after_clock  = "</font>";
var cTop = top.location.href;
//if (navigator.appVersion.indexOf("MSIE") != -1){
document.write('<span id="clock"></span>');
//}
DaysofWeek = new Array();
  DaysofWeek[0]="星期日";
  DaysofWeek[1]="星期一";
  DaysofWeek[2]="星期二";
  DaysofWeek[3]="星期三";
  DaysofWeek[4]="星期四";
  DaysofWeek[5]="星期五";
  DaysofWeek[6]="星期六";
Months = new Array();
  Months[0]="1月";
  Months[1]="2月";
  Months[2]="3月";
  Months[3]="4月";
  Months[4]="5月";
  Months[5]="6月";
  Months[6]="7月";
  Months[7]="8月";
  Months[8]="9月";
  Months[9]="10月";
  Months[10]="11月";
  Months[11]="12月";
function upclock(){
var dte = new Date();
var hrs = dte.getHours();
var min = dte.getMinutes();
var sec = dte.getSeconds();
var day = DaysofWeek[dte.getDay()];
var date = dte.getDate();
var month = Months[dte.getMonth()];
var year = dte.getFullYear();
var col = ":";
var spc = " ";
if (hrs == 0) hrs=12;
if (hrs<=9) hrs="0"+hrs;
if (min<=9) min="0"+min;
if (sec<=9) sec="0"+sec;	
//if (navigator.appVersion.indexOf("MSIE") != -1){
	clock.innerHTML = tags_before_clock+year+"年"+month+date+"日<br><br>"+spc+day;
//	}
}
if(cTop.search("editor.html") == -1)
{
	setInterval("upclock()",1000);
}
else
{
	document.write(tags_before_clock + "2008年8月15日 星期五 17:33:56" + tags_after_clock);
}
