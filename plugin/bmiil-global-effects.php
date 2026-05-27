<?php
/**
 * BMIIL Global Planetary + Gold Dust Effect
 * Fades out on scroll, visible only in header area
 */

// CSS in <head>
add_action('wp_head', function() {
    echo '<style>
#bmiil-fx{position:fixed;top:0;left:0;right:0;height:200px;pointer-events:none;z-index:2;overflow:hidden;opacity:1;transition:opacity 0.35s ease;}
#bmiil-fx-ring{position:absolute;right:-160px;top:-260px;width:700px;height:700px;border-radius:50%;border:1px solid rgba(196,154,42,0.20);animation:bfxSpin 30s linear infinite;}
#bmiil-fx-ring-dot{position:absolute;top:12px;left:50%;width:7px;height:7px;margin-left:-3.5px;border-radius:50%;background:rgba(196,154,42,0.6);}
#bmiil-fx-ring2{position:absolute;inset:70px;border-radius:50%;border:1px solid rgba(196,154,42,0.10);animation:bfxSpinR 20s linear infinite;}
#bmiil-fx-ring2-dot{position:absolute;top:9px;left:50%;width:5px;height:5px;margin-left:-2.5px;border-radius:50%;background:rgba(196,154,42,0.35);}
#bmiil-fx-ring3{position:absolute;inset:140px;border-radius:50%;border:1px solid rgba(196,154,42,0.06);animation:bfxSpin 12s linear infinite;}
#bmiil-fx-pulse{position:absolute;right:24%;top:70px;width:10px;height:10px;border-radius:50%;background:#C49A2A;animation:bfxPulse 3s ease-in-out infinite;}
#bmiil-fx-canvas{position:absolute;inset:0;width:100%;height:100%;opacity:0.5;}
@keyframes bfxSpin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
@keyframes bfxSpinR{from{transform:rotate(0deg)}to{transform:rotate(-360deg)}}
@keyframes bfxPulse{0%,100%{box-shadow:0 0 0 0 rgba(196,154,42,0.65),0 0 0 0 rgba(196,154,42,0.25)}50%{box-shadow:0 0 0 14px rgba(196,154,42,0),0 0 0 28px rgba(196,154,42,0)}}
</style>';
}, 5);

// HTML + JS before </body>
add_action('wp_footer', function() {
    echo '
<div id="bmiil-fx" aria-hidden="true">
  <div id="bmiil-fx-ring">
    <div id="bmiil-fx-ring-dot"></div>
    <div id="bmiil-fx-ring2">
      <div id="bmiil-fx-ring2-dot"></div>
    </div>
    <div id="bmiil-fx-ring3"></div>
  </div>
  <div id="bmiil-fx-pulse"></div>
  <canvas id="bmiil-fx-canvas"></canvas>
</div>
<script>
(function(){
  var el=document.getElementById("bmiil-fx");
  if(!el)return;
  var FADE=100;
  function upd(){var y=window.scrollY||window.pageYOffset||0;el.style.opacity=y>=FADE?"0":(1-y/FADE).toFixed(3);}
  window.addEventListener("scroll",upd,{passive:true});
  upd();
  var cv=document.getElementById("bmiil-fx-canvas");
  if(!cv||!cv.getContext)return;
  var cx=cv.getContext("2d");
  function rsz(){cv.width=cv.offsetWidth||window.innerWidth;cv.height=cv.offsetHeight||200;}
  rsz();
  window.addEventListener("resize",rsz,{passive:true});
  var N=38,P=[];
  function rnd(a,b){return Math.random()*(b-a)+a;}
  function mk(){return{x:rnd(0,cv.width),y:rnd(0,cv.height),r:rnd(0.5,2),dx:rnd(-0.15,0.15),dy:rnd(-0.28,-0.06),a:rnd(0.08,0.5),da:rnd(-0.003,0.003)};}
  for(var i=0;i<N;i++)P.push(mk());
  function draw(){
    cx.clearRect(0,0,cv.width,cv.height);
    P.forEach(function(p){
      cx.beginPath();cx.arc(p.x,p.y,p.r,0,Math.PI*2);
      cx.fillStyle="rgba(196,154,42,"+Math.max(0,Math.min(1,p.a)).toFixed(2)+")";cx.fill();
      p.x+=p.dx;p.y+=p.dy;p.a+=p.da;
      if(p.x<-4)p.x=cv.width+4;
      if(p.x>cv.width+4)p.x=-4;
      if(p.y<-4){p.y=cv.height+4;p.x=rnd(0,cv.width);p.a=rnd(0.08,0.5);}
      if(p.a>0.5||p.a<0.08)p.da*=-1;
    });
    requestAnimationFrame(draw);
  }
  draw();
})();
</script>';
}, 20);
