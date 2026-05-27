<?php
/**
 * BMIIL — Global Planetary Rings + Gold Dust Effects
 * Add via: Code Snippets → Add New → PHP → Run everywhere → Save & Activate
 *
 * Injects the animated planetary rings and gold particle canvas
 * seen on the homepage — across ALL pages of the site.
 */
add_action('wp_footer', function() { ?>

<!-- ══ BMIIL GLOBAL EFFECTS ════════════════════════════════════════ -->
<div id="bmiil-global-fx" aria-hidden="true" style="
  position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden;transition:opacity 0.4s ease;
">

  <!-- Radial gold glow (top-right) -->
  <div style="position:absolute;inset:0;background:
    radial-gradient(ellipse 80% 60% at 75% 20%,rgba(196,154,42,0.05) 0%,transparent 60%),
    radial-gradient(ellipse 50% 70% at 10% 90%,rgba(15,31,69,0.4) 0%,transparent 70%);
  "></div>

  <!-- Outer planetary ring -->
  <div style="
    position:absolute;right:-180px;top:-140px;
    width:750px;height:750px;border-radius:50%;
    border:1px solid rgba(196,154,42,0.18);
    animation:bmiilSpin 30s linear infinite;
  ">
    <!-- Orbiting dot on outer ring -->
    <div style="position:absolute;top:10px;left:50%;
      width:7px;height:7px;margin-left:-3.5px;border-radius:50%;
      background:rgba(196,154,42,0.55);"></div>
    <!-- Middle ring (counter-spin) -->
    <div style="position:absolute;inset:70px;border-radius:50%;
      border:1px solid rgba(196,154,42,0.1);
      animation:bmiilSpinReverse 20s linear infinite;">
      <div style="position:absolute;top:8px;left:50%;
        width:5px;height:5px;margin-left:-2.5px;border-radius:50%;
        background:rgba(196,154,42,0.35);"></div>
    </div>
    <!-- Inner ring (same direction) -->
    <div style="position:absolute;inset:140px;border-radius:50%;
      border:1px solid rgba(196,154,42,0.07);
      animation:bmiilSpin 12s linear infinite;"></div>
  </div>

  <!-- Pulsing gold dot (centre-right) -->
  <div style="
    position:absolute;right:25%;top:45%;
    width:10px;height:10px;border-radius:50%;
    background:#C49A2A;
    animation:bmiilPulse 3s ease-in-out infinite;
  "></div>

  <!-- Gold dust canvas -->
  <canvas id="bmiil-dust-canvas" style="position:absolute;inset:0;width:100%;height:100%;opacity:0.55;"></canvas>

</div><!-- #bmiil-global-fx -->

<style>
/* ── Keyframe animations ──────────────────────────────────────── */
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
@keyframes bmiilGridShimmer {
  0%   { background-position: 100% 100%; }
  50%  { background-position: 0% 0%; }
  100% { background-position: 100% 100%; }
}
/* ── Keep FX behind all page content ─────────────────────────── */
#bmiil-global-fx { z-index: 0 !important; }
/* Ensure Theme Builder header stays on top */
#bmiil-hdr, #bmiil-nav { z-index: 9999 !important; }
</style>

<script>
(function() {
  /* Gold dust particle canvas */
  var canvas = document.getElementById('bmiil-dust-canvas');
  if (!canvas || !canvas.getContext) return;
  var ctx = canvas.getContext('2d');

  function resize() {
    canvas.width  = window.innerWidth;
    canvas.height = window.innerHeight;
  }
  resize();
  window.addEventListener('resize', resize, { passive: true });

  /* Particle config */
  var GOLD   = 'rgba(196,154,42,';
  var COUNT  = 55;
  var particles = [];

  function rand(min, max) { return Math.random() * (max - min) + min; }

  function mkParticle() {
    return {
      x:     rand(0, canvas.width),
      y:     rand(0, canvas.height),
      r:     rand(0.5, 2.2),
      dx:    rand(-0.18, 0.18),
      dy:    rand(-0.35, -0.08),   /* drift upward */
      alpha: rand(0.08, 0.55),
      da:    rand(-0.003, 0.003),  /* twinkle */
    };
  }

  for (var i = 0; i < COUNT; i++) particles.push(mkParticle());

  function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    particles.forEach(function(p) {
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = GOLD + Math.max(0, Math.min(1, p.alpha)).toFixed(2) + ')';
      ctx.fill();

      /* Move */
      p.x     += p.dx;
      p.y     += p.dy;
      p.alpha += p.da;

      /* Boundary: wrap horizontally, reset when off top */
      if (p.x < -4)              p.x = canvas.width + 4;
      if (p.x > canvas.width + 4) p.x = -4;
      if (p.y < -4) {
        p.y     = canvas.height + 4;
        p.x     = rand(0, canvas.width);
        p.alpha = rand(0.08, 0.55);
      }
      /* Twinkle clamp */
      if (p.alpha > 0.55 || p.alpha < 0.08) p.da *= -1;
    });
    requestAnimationFrame(draw);
  }
  draw();

  /* ── Scroll: fade out when past header ── */
  var fx = document.getElementById('bmiil-global-fx');
  var HEADER_H = 130; /* px — header height + announcement bar */
  function updateFx() {
    var y = window.scrollY || window.pageYOffset || 0;
    if (fx) {
      fx.style.opacity = y >= HEADER_H ? '0' : String(1 - y / HEADER_H);
    }
  }
  window.addEventListener('scroll', updateFx, { passive: true });
  updateFx();

})();
</script>
<!-- ══ END BMIIL GLOBAL EFFECTS ═════════════════════════════════ -->
<?php }, 20);
