<?php਀⼀⨀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀ഊ // File:        JPGRAPH_CANVTOOLS.PHP਀ ⼀⼀ 䐀攀猀挀爀椀瀀琀椀漀渀㨀 匀漀洀攀 甀琀椀氀椀琀椀攀猀 昀漀爀 琀攀砀琀 愀渀搀 猀栀愀瀀攀 搀爀愀眀椀渀最 漀渀 愀 挀愀渀瘀愀猀ഊ // Created:     2002-08-23਀ ⼀⼀ 嘀攀爀㨀         ␀䤀搀㨀 樀瀀最爀愀瀀栀开挀愀渀瘀琀漀漀氀猀⸀瀀栀瀀 ㄀㠀㔀㜀 ㈀　　㤀ⴀ　㤀ⴀ㈀㠀 ㄀㐀㨀㌀㠀㨀㄀㐀娀 氀樀瀀 ␀ഊ //਀ ⼀⼀ 䌀漀瀀礀爀椀最栀琀 ⠀挀⤀ 䄀猀椀愀氀 䌀漀爀瀀漀爀愀琀椀漀渀⸀ 䄀氀氀 爀椀最栀琀猀 爀攀猀攀爀瘀攀搀⸀ഊ //========================================================================਀ ⨀⼀ഊ਀搀攀昀椀渀攀⠀✀䌀伀刀一䔀刀开吀伀倀䰀䔀䘀吀✀Ⰰ　⤀㬀ഊdefine('CORNER_TOPRIGHT',1);਀搀攀昀椀渀攀⠀✀䌀伀刀一䔀刀开䈀伀吀吀伀䴀刀䤀䜀䠀吀✀Ⰰ㈀⤀㬀ഊdefine('CORNER_BOTTOMLEFT',3);਀ഊ਀⼀⼀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀ഊ// CLASS CanvasScale਀⼀⼀ 䐀攀猀挀爀椀瀀琀椀漀渀㨀 䐀攀昀椀渀攀 愀 猀挀愀氀攀 昀漀爀 挀愀渀瘀愀猀 猀漀 眀攀ഊ// can abstract away with absolute pixels਀⼀⼀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀ഊ਀挀氀愀猀猀 䌀愀渀瘀愀猀匀挀愀氀攀 笀ഊ    private $g;਀    瀀爀椀瘀愀琀攀 ␀眀Ⰰ␀栀㬀ഊ    private $ixmin=0,$ixmax=10,$iymin=0,$iymax=10;਀ഊ    function __construct($graph,$xmin=0,$xmax=10,$ymin=0,$ymax=10) {਀        ␀琀栀椀猀ⴀ㸀最 㴀 ␀最爀愀瀀栀㬀ഊ        $this->w = $graph->img->width;਀        ␀琀栀椀猀ⴀ㸀栀 㴀 ␀最爀愀瀀栀ⴀ㸀椀洀最ⴀ㸀栀攀椀最栀琀㬀ഊ        $this->ixmin = $xmin;਀        ␀琀栀椀猀ⴀ㸀椀砀洀愀砀 㴀 ␀砀洀愀砀㬀ഊ        $this->iymin = $ymin;਀        ␀琀栀椀猀ⴀ㸀椀礀洀愀砀 㴀 ␀礀洀愀砀㬀ഊ    }਀ഊ    function Set($xmin=0,$xmax=10,$ymin=0,$ymax=10) {਀        ␀琀栀椀猀ⴀ㸀椀砀洀椀渀 㴀 ␀砀洀椀渀㬀ഊ        $this->ixmax = $xmax;਀        ␀琀栀椀猀ⴀ㸀椀礀洀椀渀 㴀 ␀礀洀椀渀㬀ഊ        $this->iymax = $ymax;਀    紀ഊ਀    昀甀渀挀琀椀漀渀 䜀攀琀⠀⤀ 笀ഊ        return array($this->ixmin,$this->ixmax,$this->iymin,$this->iymax);਀    紀ഊ਀    昀甀渀挀琀椀漀渀 吀爀愀渀猀氀愀琀攀⠀␀砀Ⰰ␀礀⤀ 笀ഊ        $xp = round(($x-$this->ixmin)/($this->ixmax - $this->ixmin) * $this->w);਀        ␀礀瀀 㴀 爀漀甀渀搀⠀⠀␀礀ⴀ␀琀栀椀猀ⴀ㸀椀礀洀椀渀⤀⼀⠀␀琀栀椀猀ⴀ㸀椀礀洀愀砀 ⴀ ␀琀栀椀猀ⴀ㸀椀礀洀椀渀⤀ ⨀ ␀琀栀椀猀ⴀ㸀栀⤀㬀ഊ        return array($xp,$yp);਀    紀ഊ਀    昀甀渀挀琀椀漀渀 吀爀愀渀猀氀愀琀攀堀⠀␀砀⤀ 笀ഊ        $xp = round(($x-$this->ixmin)/($this->ixmax - $this->ixmin) * $this->w);਀        爀攀琀甀爀渀 ␀砀瀀㬀ഊ    }਀ഊ    function TranslateY($y) {਀        ␀礀瀀 㴀 爀漀甀渀搀⠀⠀␀礀ⴀ␀琀栀椀猀ⴀ㸀椀礀洀椀渀⤀⼀⠀␀琀栀椀猀ⴀ㸀椀礀洀愀砀 ⴀ ␀琀栀椀猀ⴀ㸀椀礀洀椀渀⤀ ⨀ ␀琀栀椀猀ⴀ㸀栀⤀㬀ഊ        return $yp;਀    紀ഊ਀紀ഊ਀ഊ//===================================================਀⼀⼀ 䌀䰀䄀匀匀 匀栀愀瀀攀ഊ// Description: Methods to draw shapes on canvas਀⼀⼀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀ഊclass Shape {਀    瀀爀椀瘀愀琀攀 ␀椀洀最Ⰰ␀猀挀愀氀攀㬀ഊ਀    昀甀渀挀琀椀漀渀 开开挀漀渀猀琀爀甀挀琀⠀␀愀䜀爀愀瀀栀Ⰰ␀猀挀愀氀攀⤀ 笀ഊ        $this->img = $aGraph->img;਀        ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀匀攀琀䌀漀氀漀爀⠀✀戀氀愀挀欀✀⤀㬀ഊ        $this->scale = $scale;਀    紀ഊ਀    昀甀渀挀琀椀漀渀 匀攀琀䌀漀氀漀爀⠀␀愀䌀漀氀漀爀⤀ 笀ഊ        $this->img->SetColor($aColor);਀    紀ഊ਀    昀甀渀挀琀椀漀渀 䰀椀渀攀⠀␀砀㄀Ⰰ␀礀㄀Ⰰ␀砀㈀Ⰰ␀礀㈀⤀ 笀ഊ        list($x1,$y1) = $this->scale->Translate($x1,$y1);਀        氀椀猀琀⠀␀砀㈀Ⰰ␀礀㈀⤀ 㴀 ␀琀栀椀猀ⴀ㸀猀挀愀氀攀ⴀ㸀吀爀愀渀猀氀愀琀攀⠀␀砀㈀Ⰰ␀礀㈀⤀㬀ഊ        $this->img->Line($x1,$y1,$x2,$y2);਀    紀ഊ਀    昀甀渀挀琀椀漀渀 匀攀琀䰀椀渀攀圀攀椀最栀琀⠀␀愀圀攀椀最栀琀⤀ 笀ഊ        $this->img->SetLineWeight($aWeight);਀    紀ഊ਀    昀甀渀挀琀椀漀渀 倀漀氀礀最漀渀⠀␀瀀Ⰰ␀愀䌀氀漀猀攀搀㴀昀愀氀猀攀⤀ 笀ഊ        $n=count($p);਀        昀漀爀⠀␀椀㴀　㬀 ␀椀 㰀 ␀渀㬀 ␀椀⬀㴀㈀ ⤀ 笀ഊ            $p[$i]   = $this->scale->TranslateX($p[$i]);਀            ␀瀀嬀␀椀⬀㄀崀 㴀 ␀琀栀椀猀ⴀ㸀猀挀愀氀攀ⴀ㸀吀爀愀渀猀氀愀琀攀夀⠀␀瀀嬀␀椀⬀㄀崀⤀㬀ഊ        }਀        ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀倀漀氀礀最漀渀⠀␀瀀Ⰰ␀愀䌀氀漀猀攀搀⤀㬀ഊ    }਀ഊ    function FilledPolygon($p) {਀        ␀渀㴀挀漀甀渀琀⠀␀瀀⤀㬀ഊ        for($i=0; $i < $n; $i+=2 ) {਀            ␀瀀嬀␀椀崀   㴀 ␀琀栀椀猀ⴀ㸀猀挀愀氀攀ⴀ㸀吀爀愀渀猀氀愀琀攀堀⠀␀瀀嬀␀椀崀⤀㬀ഊ            $p[$i+1] = $this->scale->TranslateY($p[$i+1]);਀        紀ഊ        $this->img->FilledPolygon($p);਀    紀ഊ਀ഊ    // Draw a bezier curve with defining points in the $aPnts array਀    ⼀⼀ 甀猀椀渀最 ␀愀匀琀攀瀀猀 猀琀攀瀀猀⸀ഊ    // 0=x0, 1=y0਀    ⼀⼀ ㈀㴀砀㄀Ⰰ ㌀㴀礀㄀ഊ    // 4=x2, 5=y2਀    ⼀⼀ 㘀㴀砀㌀Ⰰ 㜀㴀礀㌀ഊ    function Bezier($p,$aSteps=40) {਀        ␀砀　 㴀 ␀瀀嬀　崀㬀ഊ        $y0 = $p[1];਀        ⼀⼀ 䌀愀氀挀甀氀愀琀攀 挀漀攀昀昀椀挀椀攀渀琀猀ഊ        $cx = 3*($p[2]-$p[0]);਀        ␀戀砀 㴀 ㌀⨀⠀␀瀀嬀㐀崀ⴀ␀瀀嬀㈀崀⤀ⴀ␀挀砀㬀ഊ        $ax = $p[6]-$p[0]-$cx-$bx;਀        ␀挀礀 㴀 ㌀⨀⠀␀瀀嬀㌀崀ⴀ␀瀀嬀㄀崀⤀㬀ഊ        $by = 3*($p[5]-$p[3])-$cy;਀        ␀愀礀 㴀 ␀瀀嬀㜀崀ⴀ␀瀀嬀㄀崀ⴀ␀挀礀ⴀ␀戀礀㬀ഊ਀        ⼀⼀ 匀琀攀瀀 猀椀稀攀ഊ        $delta = 1.0/$aSteps;਀ഊ        $x_old = $x0;਀        ␀礀开漀氀搀 㴀 ␀礀　㬀ഊ        for($t=$delta; $t<=1.0; $t+=$delta) {਀            ␀琀琀 㴀 ␀琀⨀␀琀㬀 ␀琀琀琀㴀␀琀琀⨀␀琀㬀ഊ            $x  = $ax*$ttt + $bx*$tt + $cx*$t + $x0;਀            ␀礀 㴀 ␀愀礀⨀␀琀琀琀 ⬀ ␀戀礀⨀␀琀琀 ⬀ ␀挀礀⨀␀琀 ⬀ ␀礀　㬀ഊ            $this->Line($x_old,$y_old,$x,$y);਀            ␀砀开漀氀搀 㴀 ␀砀㬀ഊ            $y_old = $y;਀        紀ഊ        $this->Line($x_old,$y_old,$p[6],$p[7]);਀    紀ഊ਀    昀甀渀挀琀椀漀渀 刀攀挀琀愀渀最氀攀⠀␀砀㄀Ⰰ␀礀㄀Ⰰ␀砀㈀Ⰰ␀礀㈀⤀ 笀ഊ        list($x1,$y1) = $this->scale->Translate($x1,$y1);਀        氀椀猀琀⠀␀砀㈀Ⰰ␀礀㈀⤀   㴀 ␀琀栀椀猀ⴀ㸀猀挀愀氀攀ⴀ㸀吀爀愀渀猀氀愀琀攀⠀␀砀㈀Ⰰ␀礀㈀⤀㬀ഊ        $this->img->Rectangle($x1,$y1,$x2,$y2);਀    紀ഊ਀    昀甀渀挀琀椀漀渀 䘀椀氀氀攀搀刀攀挀琀愀渀最氀攀⠀␀砀㄀Ⰰ␀礀㄀Ⰰ␀砀㈀Ⰰ␀礀㈀⤀ 笀ഊ        list($x1,$y1) = $this->scale->Translate($x1,$y1);਀        氀椀猀琀⠀␀砀㈀Ⰰ␀礀㈀⤀   㴀 ␀琀栀椀猀ⴀ㸀猀挀愀氀攀ⴀ㸀吀爀愀渀猀氀愀琀攀⠀␀砀㈀Ⰰ␀礀㈀⤀㬀ഊ        $this->img->FilledRectangle($x1,$y1,$x2,$y2);਀    紀ഊ਀    昀甀渀挀琀椀漀渀 䌀椀爀挀氀攀⠀␀砀㄀Ⰰ␀礀㄀Ⰰ␀爀⤀ 笀ഊ        list($x1,$y1) = $this->scale->Translate($x1,$y1);਀        椀昀⠀ ␀爀 㸀㴀 　 ⤀ഊ        $r   = $this->scale->TranslateX($r);਀        攀氀猀攀ഊ        $r = -$r;਀        ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䌀椀爀挀氀攀⠀␀砀㄀Ⰰ␀礀㄀Ⰰ␀爀⤀㬀ഊ    }਀ഊ    function FilledCircle($x1,$y1,$r) {਀        氀椀猀琀⠀␀砀㄀Ⰰ␀礀㄀⤀ 㴀 ␀琀栀椀猀ⴀ㸀猀挀愀氀攀ⴀ㸀吀爀愀渀猀氀愀琀攀⠀␀砀㄀Ⰰ␀礀㄀⤀㬀ഊ        if( $r >= 0 )਀        ␀爀   㴀 ␀琀栀椀猀ⴀ㸀猀挀愀氀攀ⴀ㸀吀爀愀渀猀氀愀琀攀堀⠀␀爀⤀㬀ഊ        else਀        ␀爀 㴀 ⴀ␀爀㬀ഊ        $this->img->FilledCircle($x1,$y1,$r);਀    紀ഊ਀    昀甀渀挀琀椀漀渀 刀漀甀渀搀攀搀刀攀挀琀愀渀最氀攀⠀␀砀㄀Ⰰ␀礀㄀Ⰰ␀砀㈀Ⰰ␀礀㈀Ⰰ␀爀㴀渀甀氀氀⤀ 笀ഊ        list($x1,$y1) = $this->scale->Translate($x1,$y1);਀        氀椀猀琀⠀␀砀㈀Ⰰ␀礀㈀⤀   㴀 ␀琀栀椀猀ⴀ㸀猀挀愀氀攀ⴀ㸀吀爀愀渀猀氀愀琀攀⠀␀砀㈀Ⰰ␀礀㈀⤀㬀ഊ਀        椀昀⠀ ␀爀 㴀㴀 渀甀氀氀 ⤀ഊ        $r = 5;਀        攀氀猀攀椀昀⠀ ␀爀 㸀㴀 　 ⤀ഊ        $r = $this->scale->TranslateX($r);਀        攀氀猀攀ഊ        $r = -$r;਀        ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀刀漀甀渀搀攀搀刀攀挀琀愀渀最氀攀⠀␀砀㄀Ⰰ␀礀㄀Ⰰ␀砀㈀Ⰰ␀礀㈀Ⰰ␀爀⤀㬀ഊ    }਀ഊ    function FilledRoundedRectangle($x1,$y1,$x2,$y2,$r=null) {਀        氀椀猀琀⠀␀砀㄀Ⰰ␀礀㄀⤀ 㴀 ␀琀栀椀猀ⴀ㸀猀挀愀氀攀ⴀ㸀吀爀愀渀猀氀愀琀攀⠀␀砀㄀Ⰰ␀礀㄀⤀㬀ഊ        list($x2,$y2)   = $this->scale->Translate($x2,$y2);਀ഊ        if( $r == null )਀        ␀爀 㴀 㔀㬀ഊ        elseif( $r > 0 )਀        ␀爀 㴀 ␀琀栀椀猀ⴀ㸀猀挀愀氀攀ⴀ㸀吀爀愀渀猀氀愀琀攀堀⠀␀爀⤀㬀ഊ        else਀        ␀爀 㴀 ⴀ␀爀㬀ഊ        $this->img->FilledRoundedRectangle($x1,$y1,$x2,$y2,$r);਀    紀ഊ਀    昀甀渀挀琀椀漀渀 匀栀愀搀漀眀刀攀挀琀愀渀最氀攀⠀␀砀㄀Ⰰ␀礀㄀Ⰰ␀砀㈀Ⰰ␀礀㈀Ⰰ␀昀挀漀氀漀爀㴀昀愀氀猀攀Ⰰ␀猀栀愀搀漀眀开眀椀搀琀栀㴀渀甀氀氀Ⰰ␀猀栀愀搀漀眀开挀漀氀漀爀㴀愀爀爀愀礀⠀㄀　㈀Ⰰ㄀　㈀Ⰰ㄀　㈀⤀⤀ 笀ഊ        list($x1,$y1) = $this->scale->Translate($x1,$y1);਀        氀椀猀琀⠀␀砀㈀Ⰰ␀礀㈀⤀ 㴀 ␀琀栀椀猀ⴀ㸀猀挀愀氀攀ⴀ㸀吀爀愀渀猀氀愀琀攀⠀␀砀㈀Ⰰ␀礀㈀⤀㬀ഊ        if( $shadow_width == null )਀        ␀猀栀愀搀漀眀开眀椀搀琀栀㴀㐀㬀ഊ        else਀        ␀猀栀愀搀漀眀开眀椀搀琀栀㴀␀琀栀椀猀ⴀ㸀猀挀愀氀攀ⴀ㸀吀爀愀渀猀氀愀琀攀堀⠀␀猀栀愀搀漀眀开眀椀搀琀栀⤀㬀ഊ        $this->img->ShadowRectangle($x1,$y1,$x2,$y2,$fcolor,$shadow_width,$shadow_color);਀    紀ഊ਀    昀甀渀挀琀椀漀渀 匀攀琀吀攀砀琀䄀氀椀最渀⠀␀栀愀氀椀最渀Ⰰ␀瘀愀氀椀最渀㴀∀戀漀琀琀漀洀∀⤀ 笀ഊ        $this->img->SetTextAlign($halign,$valign="bottom");਀    紀ഊ਀    昀甀渀挀琀椀漀渀 匀琀爀漀欀攀吀攀砀琀⠀␀砀㄀Ⰰ␀礀㄀Ⰰ␀琀砀琀Ⰰ␀搀椀爀㴀　Ⰰ␀瀀愀爀愀最爀愀瀀栀开愀氀椀最渀㴀∀氀攀昀琀∀⤀ 笀ഊ        list($x1,$y1) = $this->scale->Translate($x1,$y1);਀        ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀匀琀爀漀欀攀吀攀砀琀⠀␀砀㄀Ⰰ␀礀㄀Ⰰ␀琀砀琀Ⰰ␀搀椀爀Ⰰ␀瀀愀爀愀最爀愀瀀栀开愀氀椀最渀⤀㬀ഊ    }਀ഊ    // A rounded rectangle where one of the corner has been moved "into" the਀    ⼀⼀ 爀攀挀琀愀渀最氀攀 ✀椀眀✀ 眀椀搀琀栀 愀渀搀 ✀椀栀✀ 栀攀椀最栀琀⸀ 䌀漀爀渀攀爀猀㨀ഊ    // 0=Top left, 1=top right, 2=bottom right, 3=bottom left਀    昀甀渀挀琀椀漀渀 䤀渀搀攀渀琀攀搀刀攀挀琀愀渀最氀攀⠀␀砀琀Ⰰ␀礀琀Ⰰ␀眀Ⰰ␀栀Ⰰ␀椀眀㴀　Ⰰ␀椀栀㴀　Ⰰ␀愀䌀漀爀渀攀爀㴀㌀Ⰰ␀愀䘀椀氀氀䌀漀氀漀爀㴀∀∀Ⰰ␀爀㴀㐀⤀ 笀ഊ਀        氀椀猀琀⠀␀砀琀Ⰰ␀礀琀⤀ 㴀 ␀琀栀椀猀ⴀ㸀猀挀愀氀攀ⴀ㸀吀爀愀渀猀氀愀琀攀⠀␀砀琀Ⰰ␀礀琀⤀㬀ഊ        list($w,$h)   = $this->scale->Translate($w,$h);਀        氀椀猀琀⠀␀椀眀Ⰰ␀椀栀⤀ 㴀 ␀琀栀椀猀ⴀ㸀猀挀愀氀攀ⴀ㸀吀爀愀渀猀氀愀琀攀⠀␀椀眀Ⰰ␀椀栀⤀㬀ഊ਀        ␀砀爀 㴀 ␀砀琀 ⬀ ␀眀 ⴀ 　㬀ഊ        $yl = $yt + $h - 0;਀ഊ        switch( $aCorner ) {਀            挀愀猀攀 　㨀 ⼀⼀ 唀瀀瀀攀爀 氀攀昀琀ഊ                 ਀                ⼀⼀ 䈀漀琀琀漀洀 氀椀渀攀Ⰰ 氀攀昀琀 ☀  爀椀最栀琀 愀爀挀ഊ                $this->img->Line($xt+$r,$yl,$xr-$r,$yl);਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䄀爀挀⠀␀砀琀⬀␀爀Ⰰ␀礀氀ⴀ␀爀Ⰰ␀爀⨀㈀Ⰰ␀爀⨀㈀Ⰰ㤀　Ⰰ㄀㠀　⤀㬀ഊ                $this->img->Arc($xr-$r,$yl-$r,$r*2,$r*2,0,90);਀ഊ                // Right line, Top right arc਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䰀椀渀攀⠀␀砀爀Ⰰ␀礀琀⬀␀爀Ⰰ␀砀爀Ⰰ␀礀氀ⴀ␀爀⤀㬀ഊ                $this->img->Arc($xr-$r,$yt+$r,$r*2,$r*2,270,360);਀ഊ                // Top line, Top left arc਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䰀椀渀攀⠀␀砀琀⬀␀椀眀⬀␀爀Ⰰ␀礀琀Ⰰ␀砀爀ⴀ␀爀Ⰰ␀礀琀⤀㬀ഊ                $this->img->Arc($xt+$iw+$r,$yt+$r,$r*2,$r*2,180,270);਀ഊ                // Left line਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䰀椀渀攀⠀␀砀琀Ⰰ␀礀琀⬀␀椀栀⬀␀爀Ⰰ␀砀琀Ⰰ␀礀氀ⴀ␀爀⤀㬀ഊ਀                ⼀⼀ 䤀渀搀攀渀琀 栀漀爀椀稀漀渀琀愀氀Ⰰ 䰀漀眀攀爀 氀攀昀琀 愀爀挀ഊ                $this->img->Line($xt+$r,$yt+$ih,$xt+$iw-$r,$yt+$ih);਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䄀爀挀⠀␀砀琀⬀␀爀Ⰰ␀礀琀⬀␀椀栀⬀␀爀Ⰰ␀爀⨀㈀Ⰰ␀爀⨀㈀Ⰰ㄀㠀　Ⰰ㈀㜀　⤀㬀ഊ਀                ⼀⼀ 䤀渀搀攀渀琀 瘀攀爀琀椀挀愀氀Ⰰ 䤀渀搀攀渀琀 愀爀挀ഊ                $this->img->Line($xt+$iw,$yt+$r,$xt+$iw,$yt+$ih-$r);਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䄀爀挀⠀␀砀琀⬀␀椀眀ⴀ␀爀Ⰰ␀礀琀⬀␀椀栀ⴀ␀爀Ⰰ␀爀⨀㈀Ⰰ␀爀⨀㈀Ⰰ　Ⰰ㤀　⤀㬀ഊ਀                椀昀⠀ ␀愀䘀椀氀氀䌀漀氀漀爀 ℀㴀 ✀✀ ⤀ 笀ഊ                    $bc = $this->img->current_color_name;਀                    ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀倀甀猀栀䌀漀氀漀爀⠀␀愀䘀椀氀氀䌀漀氀漀爀⤀㬀ഊ                    $this->img->FillToBorder($xr-$r,$yl-$r,$bc);਀                    ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀倀漀瀀䌀漀氀漀爀⠀⤀㬀ഊ                }਀ഊ                break;਀ഊ            case 1: // Upper right਀ഊ                // Bottom line, left &  right arc਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䰀椀渀攀⠀␀砀琀⬀␀爀Ⰰ␀礀氀Ⰰ␀砀爀ⴀ␀爀Ⰰ␀礀氀⤀㬀ഊ                $this->img->Arc($xt+$r,$yl-$r,$r*2,$r*2,90,180);਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䄀爀挀⠀␀砀爀ⴀ␀爀Ⰰ␀礀氀ⴀ␀爀Ⰰ␀爀⨀㈀Ⰰ␀爀⨀㈀Ⰰ　Ⰰ㤀　⤀㬀ഊ਀                ⼀⼀ 䰀攀昀琀 氀椀渀攀Ⰰ 吀漀瀀 氀攀昀琀 愀爀挀ഊ                $this->img->Line($xt,$yt+$r,$xt,$yl-$r);਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䄀爀挀⠀␀砀琀⬀␀爀Ⰰ␀礀琀⬀␀爀Ⰰ␀爀⨀㈀Ⰰ␀爀⨀㈀Ⰰ㄀㠀　Ⰰ㈀㜀　⤀㬀ഊ਀                ⼀⼀ 吀漀瀀 氀椀渀攀Ⰰ 吀漀瀀 爀椀最栀琀 愀爀挀ഊ                $this->img->Line($xt+$r,$yt,$xr-$iw-$r,$yt);਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䄀爀挀⠀␀砀爀ⴀ␀椀眀ⴀ␀爀Ⰰ␀礀琀⬀␀爀Ⰰ␀爀⨀㈀Ⰰ␀爀⨀㈀Ⰰ㈀㜀　Ⰰ㌀㘀　⤀㬀ഊ਀                ⼀⼀ 刀椀最栀琀 氀椀渀攀ഊ                $this->img->Line($xr,$yt+$ih+$r,$xr,$yl-$r);਀ഊ                // Indent horizontal, Lower right arc਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䰀椀渀攀⠀␀砀爀ⴀ␀椀眀⬀␀爀Ⰰ␀礀琀⬀␀椀栀Ⰰ␀砀爀ⴀ␀爀Ⰰ␀礀琀⬀␀椀栀⤀㬀ഊ                $this->img->Arc($xr-$r,$yt+$ih+$r,$r*2,$r*2,270,360);਀ഊ                // Indent vertical, Indent arc਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䰀椀渀攀⠀␀砀爀ⴀ␀椀眀Ⰰ␀礀琀⬀␀爀Ⰰ␀砀爀ⴀ␀椀眀Ⰰ␀礀琀⬀␀椀栀ⴀ␀爀⤀㬀ഊ                $this->img->Arc($xr-$iw+$r,$yt+$ih-$r,$r*2,$r*2,90,180);਀ഊ                if( $aFillColor != '' ) {਀                    ␀戀挀 㴀 ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀挀甀爀爀攀渀琀开挀漀氀漀爀开渀愀洀攀㬀ഊ                    $this->img->PushColor($aFillColor);਀                    ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䘀椀氀氀吀漀䈀漀爀搀攀爀⠀␀砀琀⬀␀爀Ⰰ␀礀氀ⴀ␀爀Ⰰ␀戀挀⤀㬀ഊ                    $this->img->PopColor();਀                紀ഊ਀                戀爀攀愀欀㬀ഊ਀            挀愀猀攀 ㈀㨀 ⼀⼀ 䰀漀眀攀爀 爀椀最栀琀ഊ                // Top line, Top left & Top right arc਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䰀椀渀攀⠀␀砀琀⬀␀爀Ⰰ␀礀琀Ⰰ␀砀爀ⴀ␀爀Ⰰ␀礀琀⤀㬀ഊ                $this->img->Arc($xt+$r,$yt+$r,$r*2,$r*2,180,270);਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䄀爀挀⠀␀砀爀ⴀ␀爀Ⰰ␀礀琀⬀␀爀Ⰰ␀爀⨀㈀Ⰰ␀爀⨀㈀Ⰰ㈀㜀　Ⰰ㌀㘀　⤀㬀ഊ਀                ⼀⼀ 䰀攀昀琀 氀椀渀攀Ⰰ 䈀漀琀琀漀洀 氀攀昀琀 愀爀挀ഊ                $this->img->Line($xt,$yt+$r,$xt,$yl-$r);਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䄀爀挀⠀␀砀琀⬀␀爀Ⰰ␀礀氀ⴀ␀爀Ⰰ␀爀⨀㈀Ⰰ␀爀⨀㈀Ⰰ㤀　Ⰰ㄀㠀　⤀㬀ഊ਀                ⼀⼀ 䈀漀琀琀漀洀 氀椀渀攀Ⰰ 䈀漀琀琀漀洀 爀椀最栀琀 愀爀挀ഊ                $this->img->Line($xt+$r,$yl,$xr-$iw-$r,$yl);਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䄀爀挀⠀␀砀爀ⴀ␀椀眀ⴀ␀爀Ⰰ␀礀氀ⴀ␀爀Ⰰ␀爀⨀㈀Ⰰ␀爀⨀㈀Ⰰ　Ⰰ㤀　⤀㬀ഊ਀                ⼀⼀ 刀椀最栀琀 氀椀渀攀ഊ                $this->img->Line($xr,$yt+$r,$xr,$yl-$ih-$r);਀                 ഊ                // Indent horizontal, Lower right arc਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䰀椀渀攀⠀␀砀爀ⴀ␀爀Ⰰ␀礀氀ⴀ␀椀栀Ⰰ␀砀爀ⴀ␀椀眀⬀␀爀Ⰰ␀礀氀ⴀ␀椀栀⤀㬀ഊ                $this->img->Arc($xr-$r,$yl-$ih-$r,$r*2,$r*2,0,90);਀ഊ                // Indent vertical, Indent arc਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䰀椀渀攀⠀␀砀爀ⴀ␀椀眀Ⰰ␀礀氀ⴀ␀爀Ⰰ␀砀爀ⴀ␀椀眀Ⰰ␀礀氀ⴀ␀椀栀⬀␀爀⤀㬀ഊ                $this->img->Arc($xr-$iw+$r,$yl-$ih+$r,$r*2,$r*2,180,270);਀ഊ                if( $aFillColor != '' ) {਀                    ␀戀挀 㴀 ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀挀甀爀爀攀渀琀开挀漀氀漀爀开渀愀洀攀㬀ഊ                    $this->img->PushColor($aFillColor);਀                    ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䘀椀氀氀吀漀䈀漀爀搀攀爀⠀␀砀琀⬀␀爀Ⰰ␀礀琀⬀␀爀Ⰰ␀戀挀⤀㬀ഊ                    $this->img->PopColor();਀                紀ഊ਀                戀爀攀愀欀㬀ഊ਀            挀愀猀攀 ㌀㨀 ⼀⼀ 䰀漀眀攀爀 氀攀昀琀ഊ                // Top line, Top left & Top right arc਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䰀椀渀攀⠀␀砀琀⬀␀爀Ⰰ␀礀琀Ⰰ␀砀爀ⴀ␀爀Ⰰ␀礀琀⤀㬀ഊ                $this->img->Arc($xt+$r,$yt+$r,$r*2,$r*2,180,270);਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䄀爀挀⠀␀砀爀ⴀ␀爀Ⰰ␀礀琀⬀␀爀Ⰰ␀爀⨀㈀Ⰰ␀爀⨀㈀Ⰰ㈀㜀　Ⰰ㌀㘀　⤀㬀ഊ਀                ⼀⼀ 刀椀最栀琀 氀椀渀攀Ⰰ 䈀漀琀琀漀洀 爀椀最栀琀 愀爀挀ഊ                $this->img->Line($xr,$yt+$r,$xr,$yl-$r);਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䄀爀挀⠀␀砀爀ⴀ␀爀Ⰰ␀礀氀ⴀ␀爀Ⰰ␀爀⨀㈀Ⰰ␀爀⨀㈀Ⰰ　Ⰰ㤀　⤀㬀ഊ਀                ⼀⼀ 䈀漀琀琀漀洀 氀椀渀攀Ⰰ 䈀漀琀琀漀洀 氀攀昀琀 愀爀挀ഊ                $this->img->Line($xt+$iw+$r,$yl,$xr-$r,$yl);਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䄀爀挀⠀␀砀琀⬀␀椀眀⬀␀爀Ⰰ␀礀氀ⴀ␀爀Ⰰ␀爀⨀㈀Ⰰ␀爀⨀㈀Ⰰ㤀　Ⰰ㄀㠀　⤀㬀ഊ਀                ⼀⼀ 䰀攀昀琀 氀椀渀攀ഊ                $this->img->Line($xt,$yt+$r,$xt,$yl-$ih-$r);਀                 ഊ                // Indent horizontal, Lower left arc਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䰀椀渀攀⠀␀砀琀⬀␀爀Ⰰ␀礀氀ⴀ␀椀栀Ⰰ␀砀琀⬀␀椀眀ⴀ␀爀Ⰰ␀礀氀ⴀ␀椀栀⤀㬀ഊ                $this->img->Arc($xt+$r,$yl-$ih-$r,$r*2,$r*2,90,180);਀ഊ                // Indent vertical, Indent arc਀                ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䰀椀渀攀⠀␀砀琀⬀␀椀眀Ⰰ␀礀氀ⴀ␀椀栀⬀␀爀Ⰰ␀砀琀⬀␀椀眀Ⰰ␀礀氀ⴀ␀爀⤀㬀ഊ                $this->img->Arc($xt+$iw-$r,$yl-$ih+$r,$r*2,$r*2,270,360);਀ഊ                if( $aFillColor != '' ) {਀                    ␀戀挀 㴀 ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀挀甀爀爀攀渀琀开挀漀氀漀爀开渀愀洀攀㬀ഊ                    $this->img->PushColor($aFillColor);਀                    ␀琀栀椀猀ⴀ㸀椀洀最ⴀ㸀䘀椀氀氀吀漀䈀漀爀搀攀爀⠀␀砀爀ⴀ␀爀Ⰰ␀礀琀⬀␀爀Ⰰ␀戀挀⤀㬀ഊ                    $this->img->PopColor();਀                紀ഊ਀                戀爀攀愀欀㬀ഊ        }਀    紀ഊ}਀ഊ਀⼀⼀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀ഊ// CLASS RectangleText਀⼀⼀ 䐀攀猀挀爀椀瀀琀椀漀渀㨀 䐀爀愀眀猀 愀 琀攀砀琀 瀀愀爀愀最爀愀瀀栀 椀渀猀椀搀攀 愀ഊ// rounded, possible filled, rectangle.਀⼀⼀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀㴀ഊclass CanvasRectangleText {਀    瀀爀椀瘀愀琀攀 ␀椀砀Ⰰ␀椀礀Ⰰ␀椀眀Ⰰ␀椀栀Ⰰ␀椀爀㴀㐀㬀ഊ    private $iTxt,$iColor='black',$iFillColor='',$iFontColor='black';਀    瀀爀椀瘀愀琀攀 ␀椀倀愀爀愀䄀氀椀最渀㴀✀挀攀渀琀攀爀✀㬀ഊ    private $iAutoBoxMargin=5;਀    瀀爀椀瘀愀琀攀 ␀椀匀栀愀搀漀眀圀椀搀琀栀㴀㌀Ⰰ␀椀匀栀愀搀漀眀䌀漀氀漀爀㴀✀✀㬀ഊ਀    昀甀渀挀琀椀漀渀 开开挀漀渀猀琀爀甀挀琀⠀␀愀吀砀琀㴀✀✀Ⰰ␀砀氀㴀　Ⰰ␀礀琀㴀　Ⰰ␀眀㴀　Ⰰ␀栀㴀　⤀ 笀ഊ        $this->iTxt = new Text($aTxt);਀        ␀琀栀椀猀ⴀ㸀椀砀 㴀 ␀砀氀㬀ഊ        $this->iy = $yt;਀        ␀琀栀椀猀ⴀ㸀椀眀 㴀 ␀眀㬀ഊ        $this->ih = $h;਀    紀ഊ਀    昀甀渀挀琀椀漀渀 匀攀琀匀栀愀搀漀眀⠀␀愀䌀漀氀漀爀㴀✀最爀愀礀✀Ⰰ␀愀圀椀搀琀栀㴀㌀⤀ 笀ഊ        $this->iShadowColor = $aColor;਀        ␀琀栀椀猀ⴀ㸀椀匀栀愀搀漀眀圀椀搀琀栀 㴀 ␀愀圀椀搀琀栀㬀ഊ    }਀ഊ    function SetFont($FontFam,$aFontStyle,$aFontSize=12) {਀        ␀琀栀椀猀ⴀ㸀椀吀砀琀ⴀ㸀匀攀琀䘀漀渀琀⠀␀䘀漀渀琀䘀愀洀Ⰰ␀愀䘀漀渀琀匀琀礀氀攀Ⰰ␀愀䘀漀渀琀匀椀稀攀⤀㬀ഊ    }਀ഊ    function SetTxt($aTxt) {਀        ␀琀栀椀猀ⴀ㸀椀吀砀琀ⴀ㸀匀攀琀⠀␀愀吀砀琀⤀㬀ഊ    }਀ഊ    function ParagraphAlign($aParaAlign) {਀        ␀琀栀椀猀ⴀ㸀椀倀愀爀愀䄀氀椀最渀 㴀 ␀愀倀愀爀愀䄀氀椀最渀㬀ഊ    }਀ഊ    function SetFillColor($aFillColor) {਀        ␀琀栀椀猀ⴀ㸀椀䘀椀氀氀䌀漀氀漀爀 㴀 ␀愀䘀椀氀氀䌀漀氀漀爀㬀ഊ    }਀ഊ    function SetAutoMargin($aMargin) {਀        ␀琀栀椀猀ⴀ㸀椀䄀甀琀漀䈀漀砀䴀愀爀最椀渀㴀␀愀䴀愀爀最椀渀㬀ഊ    }਀ഊ    function SetColor($aColor) {਀        ␀琀栀椀猀ⴀ㸀椀䌀漀氀漀爀 㴀 ␀愀䌀漀氀漀爀㬀ഊ    }਀ഊ    function SetFontColor($aColor) {਀        ␀琀栀椀猀ⴀ㸀椀䘀漀渀琀䌀漀氀漀爀 㴀 ␀愀䌀漀氀漀爀㬀ഊ    }਀ഊ    function SetPos($xl=0,$yt=0,$w=0,$h=0) {਀        ␀琀栀椀猀ⴀ㸀椀砀 㴀 ␀砀氀㬀ഊ        $this->iy = $yt;਀        ␀琀栀椀猀ⴀ㸀椀眀 㴀 ␀眀㬀ഊ        $this->ih = $h;਀    紀ഊ਀    昀甀渀挀琀椀漀渀 倀漀猀⠀␀砀氀㴀　Ⰰ␀礀琀㴀　Ⰰ␀眀㴀　Ⰰ␀栀㴀　⤀ 笀ഊ        $this->ix = $xl;਀        ␀琀栀椀猀ⴀ㸀椀礀 㴀 ␀礀琀㬀ഊ        $this->iw = $w;਀        ␀琀栀椀猀ⴀ㸀椀栀 㴀 ␀栀㬀ഊ    }਀ഊ    function Set($aTxt,$xl,$yt,$w=0,$h=0) {਀        ␀琀栀椀猀ⴀ㸀椀吀砀琀ⴀ㸀匀攀琀⠀␀愀吀砀琀⤀㬀ഊ        $this->ix = $xl;਀        ␀琀栀椀猀ⴀ㸀椀礀 㴀 ␀礀琀㬀ഊ        $this->iw = $w;਀        ␀琀栀椀猀ⴀ㸀椀栀 㴀 ␀栀㬀ഊ    }਀ഊ    function SetCornerRadius($aRad=5) {਀        ␀琀栀椀猀ⴀ㸀椀爀 㴀 ␀愀刀愀搀㬀ഊ    }਀ഊ    function Stroke($aImg,$scale) {਀ഊ        // If coordinates are specifed as negative this means we should਀        ⼀⼀ 琀爀攀愀琀 琀栀攀洀 愀猀 愀戀漀氀猀甀琀攀 ⠀瀀椀砀攀氀猀⤀ 挀漀漀爀搀椀渀愀琀攀猀ഊ        if( $this->ix > 0 ) {਀            ␀琀栀椀猀ⴀ㸀椀砀 㴀 ␀猀挀愀氀攀ⴀ㸀吀爀愀渀猀氀愀琀攀堀⠀␀琀栀椀猀ⴀ㸀椀砀⤀ 㬀ഊ        }਀        攀氀猀攀 笀ഊ            $this->ix = -$this->ix;਀        紀ഊ਀        椀昀⠀ ␀琀栀椀猀ⴀ㸀椀礀 㸀 　 ⤀ 笀ഊ            $this->iy = $scale->TranslateY($this->iy) ;਀        紀ഊ        else {਀            ␀琀栀椀猀ⴀ㸀椀礀 㴀 ⴀ␀琀栀椀猀ⴀ㸀椀礀㬀ഊ        }਀         ഊ        list($this->iw,$this->ih) = $scale->Translate($this->iw,$this->ih) ;਀ഊ        if( $this->iw == 0 )਀        ␀琀栀椀猀ⴀ㸀椀眀 㴀 爀漀甀渀搀⠀␀琀栀椀猀ⴀ㸀椀吀砀琀ⴀ㸀䜀攀琀圀椀搀琀栀⠀␀愀䤀洀最⤀ ⬀ ␀琀栀椀猀ⴀ㸀椀䄀甀琀漀䈀漀砀䴀愀爀最椀渀⤀㬀ഊ        if( $this->ih == 0 ) {਀            ␀琀栀椀猀ⴀ㸀椀栀 㴀 爀漀甀渀搀⠀␀琀栀椀猀ⴀ㸀椀吀砀琀ⴀ㸀䜀攀琀吀攀砀琀䠀攀椀最栀琀⠀␀愀䤀洀最⤀ ⬀ ␀琀栀椀猀ⴀ㸀椀䄀甀琀漀䈀漀砀䴀愀爀最椀渀⤀㬀ഊ        }਀ഊ        if( $this->iShadowColor != '' ) {਀            ␀愀䤀洀最ⴀ㸀倀甀猀栀䌀漀氀漀爀⠀␀琀栀椀猀ⴀ㸀椀匀栀愀搀漀眀䌀漀氀漀爀⤀㬀ഊ            $aImg->FilledRoundedRectangle($this->ix+$this->iShadowWidth,਀            ␀琀栀椀猀ⴀ㸀椀礀⬀␀琀栀椀猀ⴀ㸀椀匀栀愀搀漀眀圀椀搀琀栀Ⰰഊ            $this->ix+$this->iw-1+$this->iShadowWidth,਀            ␀琀栀椀猀ⴀ㸀椀礀⬀␀琀栀椀猀ⴀ㸀椀栀ⴀ㄀⬀␀琀栀椀猀ⴀ㸀椀匀栀愀搀漀眀圀椀搀琀栀Ⰰഊ            $this->ir);਀            ␀愀䤀洀最ⴀ㸀倀漀瀀䌀漀氀漀爀⠀⤀㬀ഊ        }਀ഊ        if( $this->iFillColor != '' ) {਀            ␀愀䤀洀最ⴀ㸀倀甀猀栀䌀漀氀漀爀⠀␀琀栀椀猀ⴀ㸀椀䘀椀氀氀䌀漀氀漀爀⤀㬀ഊ            $aImg->FilledRoundedRectangle($this->ix,$this->iy,਀            ␀琀栀椀猀ⴀ㸀椀砀⬀␀琀栀椀猀ⴀ㸀椀眀ⴀ㄀Ⰰഊ            $this->iy+$this->ih-1,਀            ␀琀栀椀猀ⴀ㸀椀爀⤀㬀ഊ            $aImg->PopColor();਀        紀ഊ਀        椀昀⠀ ␀琀栀椀猀ⴀ㸀椀䌀漀氀漀爀 ℀㴀 ✀✀ ⤀ 笀ഊ            $aImg->PushColor($this->iColor);਀            ␀愀䤀洀最ⴀ㸀刀漀甀渀搀攀搀刀攀挀琀愀渀最氀攀⠀␀琀栀椀猀ⴀ㸀椀砀Ⰰ␀琀栀椀猀ⴀ㸀椀礀Ⰰഊ            $this->ix+$this->iw-1,਀            ␀琀栀椀猀ⴀ㸀椀礀⬀␀琀栀椀猀ⴀ㸀椀栀ⴀ㄀Ⰰഊ            $this->ir);਀            ␀愀䤀洀最ⴀ㸀倀漀瀀䌀漀氀漀爀⠀⤀㬀ഊ        }਀ഊ        $this->iTxt->Align('center','center');਀        ␀琀栀椀猀ⴀ㸀椀吀砀琀ⴀ㸀倀愀爀愀最爀愀瀀栀䄀氀椀最渀⠀␀琀栀椀猀ⴀ㸀椀倀愀爀愀䄀氀椀最渀⤀㬀ഊ        $this->iTxt->SetColor($this->iFontColor);਀        ␀琀栀椀猀ⴀ㸀椀吀砀琀ⴀ㸀匀琀爀漀欀攀⠀␀愀䤀洀最Ⰰ ␀琀栀椀猀ⴀ㸀椀砀⬀␀琀栀椀猀ⴀ㸀椀眀⼀㈀Ⰰ ␀琀栀椀猀ⴀ㸀椀礀⬀␀琀栀椀猀ⴀ㸀椀栀⼀㈀⤀㬀ഊ਀        爀攀琀甀爀渀 愀爀爀愀礀⠀␀琀栀椀猀ⴀ㸀椀眀Ⰰ ␀琀栀椀猀ⴀ㸀椀栀⤀㬀ഊ਀    紀ഊ਀紀ഊ਀ഊ?>�