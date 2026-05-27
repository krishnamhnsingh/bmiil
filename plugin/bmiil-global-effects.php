<?php
/**
 * BMIIL — Global Planetary Rings + Gold Dust Effects (header only)
 * Add via: Code Snippets → Add New → PHP → Run everywhere → Save & Activate
 *
 * Effects are fixed to the TOP of the viewport (header area only).
 * They fade out as user scrolls down and reappear when scrolled back to top.
 */
add_action('wp_footer', function() { ?>

<!-- ══ BMIIL GLOBAL EFFECTS ════════════════════════════════════════ -->
<div id="bmiil-global-fx" aria-hidden="true" style="
  position:fixed;
  top:0;left:0;right:0;
  height:180px;
  pointer-events:none;
  z-index:1;
  overflow:hidden;
  transition:opacity 0.4s ease;
">

  <!-- Radial gold glow -->
  <div style="position:absolute;inset:0;background:
    radial-gradient(ellipse 80% 150% at 80% 0%,rgba(196,154,42,0.06) 0%,transparent 70%);
  "></div>

  <!-- Outer planetary ring -->
  <div style="
    position:absolute;right:-180px;top:-280px;
    width:750px;height:750px;border-radius:50%;
    border:1px solid rgba(196,154,42,0.18);
    animation:bmiilSpin 30s linear infinite;
  ">
    <div style="position:absolute;top:10px;left:50%;
      width:7px;height:7px;margin-left:-3.5px;border-radius:50%;
      background:rgba(196,154,42,0.55);"></div>
    <div style="position:absolute;inset:70px;border-radius:50%;
      border:1px solid rgba(196,154,42,0.1);
      animation:bmiilSpinReverse 20s linear infinite;">
      <div style="position:absolute;top:8px;left:50%;
        width:5px;height:5px;margin-left:-2.5px;border-radius:50%;
        background:rgba(196,154,42,0.35);"></div>
    </div>
    <div style="position:absolute;inset:140px;border-radius:50%;
      border:1px solid rgba(196,154,42,0.07);
      animation:bmiilSpin 12s linear infinite;"></div>
  </div>

  <!-- Pulsing gold dot -->
  <div style="
    position:absolute;right:22%;top:65px;
    width:10px;height:10px;border-radius:50%;
    background:#C49A2A;
    animation:bmiilPulse 3s ease-in-out infinite;
  "></div>

  <!-- Gold dust canvas (clipped to header height) -->
  <canvas id="bmiil-dust-canvas" style="
    position:absolute;inset:0;
    width:100%;height:100%;
    opacity:0.5;
  "></canvas>

</div><!-- #bmiil-global-fx -->

<style>
@keyframes bmiilSpin {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}
@keyframes bmiilSpinReverse {
  from { transform: rotate(0deg); }
  to   { transform: rotate(-360deg); }
}
@keyframes bmiilPulse {
  0%,100% { box-shadow: 0 0 0 0 rgba(196,154,42,0.6), 0 0 0 0 rgba(196,154,42,0.25); }
  50%     { box-shadow: 0 0 0 16px rgba(196,154,42,0), 0 0 0 32px rgba(196,154,42,0); }
}
/* Header and fx layer stay on correct z-levels */
#bmiil-hdr, #bmiil-nav, #bmiil-header-root { z-index: 9999 !important; position:relative; }
#bmiil-global-fx { z-index: 1 !important; }
</style>

<script>
(function() {

  /* ── Scroll fade: visible at top, hidden when scrolled down ── */
  var fx      = document.getElementById('bmiil-global-fx');
  var FADE_PX = 120; /* pixels to scroll before fully hidden */

  function onScroll() {
    var y       = window.scrollY || window.pageYOffset;
    var opacity = Math.max(0, 1 - (y / FADE_PX));
    fx.style.opacity = opacity;
    /* Also hide from accessibility/interaction when invisible */
    fx.style.visibility = opacity === 0 ? 'hidden' : 'visible';
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll(); /* run once on load */

  /* ── Gold dust particles (canvas, clipped to header band) ── */
  var canvas = document.getElementById('bmiil-dust-canvas');
  if (!canvas || !canvas.getContext) return;
  var ctx = canvas.getContext('2d');

  function resize() {
    canvas.width  = canvas.offsetWidth  || window.innerWidth;
    canvas.height = canvas.offsetHeight || 180;
  }
  resize();
  window.addEventListener('resize', resize, { passive: true });

  var GOLD  = 'rgba(196,154,42,';
  var COUNT = 40;
  var parts = [];

  function rand(a, b) { return Math.random() * (b - a) + a; }

  function mkP() {
    return {
      x:  rand(0, canvas.width),
      y:  rand(0, canvas.height),
      r:  rand(0.5, 2.0),
      dx: rand(-0.15, 0.15),
      dy: rand(-0.3, -0.06),
      a:  rand(0.08, 0.5),
      da: rand(-0.003, 0.003),
    };
  }

  for (var i = 0; i < COUNT; i++) parts.push(mkP());

  function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    parts.forEach(function(p) {
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = GOLD + Math.max(0, Math.min(1, p.a)).toFixed(2) + ')';
      ctx.fill();

      p.x += p.dx;
      p.y += p.dy;
      p.a += p.da;

      if (p.x < -4)                p.x = canvas.width + 4;
      if (p.x > canvas.width + 4)  p.x = -4;
      if (p.y < -4) {
        p.y = canvas.height + 4;
        p.x = rand(0, canvas.width);
        p.a = rand(0.08, 0.5);
      }
      if (p.a > 0.5 || p.a < 0.08) p.da *= -1;
    });
    requestAnimationFrame(draw);
  }
  draw();

})();
</script>
<!-- ══ END BMIIL GLOBAL EFFECTS ═════════════════════════════════ -->
<?php }, 20);
