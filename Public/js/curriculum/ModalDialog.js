function ModalDialog(name,divid,width,height,leftop,topop,color)
        {
            this.name=name;//名称
            this.div=divid;//要放入窗体中的元素名称
            this.width=width;//窗体高
            this.height=height;//窗体宽
            this.leftop=leftop;//左侧位置
            this.topop=topop;//上部位置
            this.color=color;//整体颜色
            this.show=function()//显示窗体
            {
                document.all(obj.name+"_divshow").style.width=obj.width;
                document.all(obj.name+"_divshow").style.height=obj.height;
                document.all(obj.name+"_divshow").style.left=obj.leftop;
                document.all(obj.name+"_divshow").style.top=obj.topop;
                document.all(obj.name+"_mask").style.width=document.body.clientWidth;
                document.all(obj.name+"_mask").style.height=document.body.clientHeight;
                document.all(obj.name+"_divshow").style.visibility="visible";
                document.all(obj.name+"_mask").style.visibility="visible";
				
				//alert(document.all(obj.name+"_divshow").style.width);
            }
            
            this.close=function()//关闭窗体
            {  
                document.all(obj.name+"_divshow").style.width=0;
                document.all(obj.name+"_divshow").style.height=0;
                document.all(obj.name+"_divshow").style.left=0;
                document.all(obj.name+"_divshow").style.top=0;
                document.all(obj.name+"_mask").style.width=0;
                document.all(obj.name+"_mask").style.height=0;
                document.all(obj.name+"_divshow").style.visibility="hidden";
                document.all(obj.name+"_mask").style.visibility="hidden";         
            }
            
            this.toString=function()
            {
                var tmp="<div id='"+this.name+"_divshow' style='position:absolute; left:0; top:0;z-index:10; visibility:hidden;width:0;height:0'>";
                tmp+="<table cellpadding=0 cellspacing=0 border=0 width=100% height=100%>";
              
                tmp+="<tr>";
                tmp+="<td bgcolor='"+obj.color+"' width=2></td>";
                tmp+="<td bgcolor=#ffffff id='"+this.name+"_content' valign=top>&nbsp;</td>";
                tmp+="<td bgcolor='"+obj.color+"'width=2></td>";
                tmp+="</tr>";
                tmp+="<tr height=2><td  bgcolor='"+obj.color+"' colspan=3></td></tr>"
                tmp+="</table>";
                tmp+="</div>";
                tmp+="<div  id='"+this.name+"_mask' style='position:absolute; top:0; left:0; width:0; height:0; background:#666; filter:ALPHA(opacity=60); z-index:9; visibility:hidden'></div>";
          
                document.write(tmp);
                document.all(this.name+"_content").insertBefore(document.all(this.div));
            }
             var obj=this;
        }
