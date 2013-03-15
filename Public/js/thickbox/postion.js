function getElementPos(elementId)
{
    var ua=navigator.userAgent.toLowerCase();
    var isOpera=(ua.indexOf('opera')!=-1);
    var isIE=(ua.indexOf('msie')!=-1&&!isOpera);
    var el=document.getElementById(elementId);
    if(el.parentNode===null||el.style.display=='none')
    {
        return false
    }
    var parent=null;
    var pos=[];
    var box;
    if(el.getBoundingClientRect)
    {
        box=el.getBoundingClientRect();
        var scrollTop=Math.max(document.documentElement.scrollTop,document.body.scrollTop);
        var scrollLeft=Math.max(document.documentElement.scrollLeft,document.body.scrollLeft);
        return{ x:box.left+scrollLeft,y:box.top+scrollTop}
    }
    else if(document.getBoxObjectFor)
    {
        box=document.getBoxObjectFor(el);
        var borderLeft=(el.style.borderLeftWidth)?parseInt(el.style.borderLeftWidth):0;
        var borderTop=(el.style.borderTopWidth)?parseInt(el.style.borderTopWidth):0;
        pos=[box.x-borderLeft,box.y-borderTop]
    }
    else
    {
        pos=[el.offsetLeft,el.offsetTop];
        parent=el.offsetParent;
        if(parent!=el)
        {
            while(parent)
            {
                pos[0]+=parent.offsetLeft;
                pos[1]+=parent.offsetTop;
                parent=parent.offsetParent
            }
        }
        if(ua.indexOf('opera')!=-1||(ua.indexOf('safari')!=-1&&el.style.position=='absolute'))
        {
            pos[0]-=document.body.offsetLeft;
            pos[1]-=document.body.offsetTop
        }
    }
    if(el.parentNode)
    {
        parent=el.parentNode
    }
    else
    {
        parent=null
    }
    while(parent&&parent.tagName!='BODY'&&parent.tagName!='HTML')
    {
        pos[0]-=parent.scrollLeft;
        pos[1]-=parent.scrollTop;
        if(parent.parentNode)
        {
            parent=parent.parentNode
        }
        else
        {
            parent=null
        }
    }
    return{ x:pos[0],y:pos[1]}
}
jQuery.getPos=function(e)
{
    var l=0;
    var t=0;
    var w=jQuery.intval(jQuery.css(e,'width'));
    var h=jQuery.intval(jQuery.css(e,'height'));
    var wb=e.offsetWidth;
    var hb=e.offsetHeight;
    while(e.offsetParent)
    {
        l+=e.offsetLeft+(e.currentStyle?jQuery.intval(e.currentStyle.borderLeftWidth):0);
        t+=e.offsetTop+(e.currentStyle?jQuery.intval(e.currentStyle.borderTopWidth):0);
        e=e.offsetParent
    }
    l+=e.offsetLeft+(e.currentStyle?jQuery.intval(e.currentStyle.borderLeftWidth):0);
    t+=e.offsetTop+(e.currentStyle?jQuery.intval(e.currentStyle.borderTopWidth):0);
    return{x:l,y:t,w:w,h:h,wb:wb,hb:hb}
};
jQuery.getClient=function(e)
{
    if(e)
    {
        w=e.clientWidth;
        h=e.clientHeight
    }
    else
    {
        w=(window.innerWidth)?window.innerWidth:(document.documentElement&&document.documentElement.clientWidth)?document.documentElement.clientWidth:document.body.offsetWidth;
        h=(window.innerHeight)?window.innerHeight:(document.documentElement&&document.documentElement.clientHeight)?document.documentElement.clientHeight:document.body.offsetHeight
    }
    return{ w:w,h:h }
};
jQuery.getScroll=function(e)
{
    if(e)
    {
        t=e.scrollTop;
        l=e.scrollLeft;
        w=e.scrollWidth;
        h=e.scrollHeight
    }
    else
    {
        if(document.documentElement&&document.documentElement.scrollTop)
        {
            t=document.documentElement.scrollTop;
            l=document.documentElement.scrollLeft;
            w=document.documentElement.scrollWidth;
            h=document.documentElement.scrollHeight
        }
        else if(document.body)
        {
            t=document.body.scrollTop;
            l=document.body.scrollLeft;
            w=document.body.scrollWidth;
            h=document.body.scrollHeight
        }
    }
    return{t:t,l:l,w:w,h:h }
};
jQuery.intval=function(v)
{
    v=parseInt(v);
    return isNaN(v)?0:v
};
jQuery.fn.ScrollTo=function(s)
{
    o=jQuery.speed(s);
return this.each(function()
{
    new jQuery.fx.ScrollTo(this,o)
}
)
};
jQuery.fx.ScrollTo=function(e,o)
{
var z=this;
z.o=o;
z.e=e;
z.p=jQuery.getPos(e);
z.s=jQuery.getScroll();
z.clear=function()
{
    clearInterval(z.timer);
    z.timer=null
};
z.t=(new Date).getTime();
z.step=function()
{
    var t=(new Date).getTime();
    var p=(t-z.t)/z.o.duration;
    if(t>=z.o.duration+z.t)
    {
        z.clear();
setTimeout(function()
{
    z.scroll(z.p.y,z.p.x)
}
,13)
}
else
{
st=((-Math.cos(p*Math.PI)/2)+0.5)*(z.p.y-z.s.t)+z.s.t;
sl=((-Math.cos(p*Math.PI)/2)+0.5)*(z.p.x-z.s.l)+z.s.l;
z.scroll(st,sl)
}
};
z.scroll=function(t,l)
{
window.scrollTo(l,t)
};
z.timer=setInterval(function()
{
    z.step()
}
,13)
};
