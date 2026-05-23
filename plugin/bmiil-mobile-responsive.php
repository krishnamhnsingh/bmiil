<?php
/**
 * BMIIL — Global Mobile Responsive CSS
 * Add via Code Snippets → Add New → PHP snippet → Run everywhere
 */
add_action('wp_head', function() { ?>
<style id="bmiil-mobile-responsive">

/* ══════════════════════════════════════════════════════
   TABLET (≤ 1024px)
══════════════════════════════════════════════════════ */
@media (max-width: 1024px) {
  /* Reduce hero padding */
  [style*="min-height:100vh"] > div,
  [style*="min-height: 100vh"] > div { padding: 2rem 1.5rem !important; }

  /* 3-column grids → 2-column */
  [style*="grid-template-columns:repeat(3,1fr)"],
  [style*="grid-template-columns: repeat(3, 1fr)"] {
    grid-template-columns: repeat(2,1fr) !important;
  }

  /* 4-column grids → 2-column */
  [style*="grid-template-columns:repeat(4,1fr)"],
  [style*="grid-template-columns: repeat(4, 1fr)"] {
    grid-template-columns: repeat(2,1fr) !important;
  }
}

/* ══════════════════════════════════════════════════════
   MOBILE (≤ 767px)
══════════════════════════════════════════════════════ */
@media (max-width: 767px) {

  /* ── GLOBAL ───────────────────────────────────────── */
  * { box-sizing: border-box; }

  /* All inner max-width containers — full width with safe padding */
  [style*="max-width:1220px"],
  [style*="max-width: 1220px"],
  [style*="max-width:860px"],
  [style*="max-width:900px"] {
    max-width: 100% !important;
    padding-left: 1.25rem !important;
    padding-right: 1.25rem !important;
  }

  /* ── ALL GRIDS → SINGLE COLUMN ─────────────────────── */
  [style*="display:grid"],
  [style*="display: grid"] {
    grid-template-columns: 1fr !important;
    gap: 1.5rem !important;
  }

  /* ── FLEX ROWS → COLUMN ─────────────────────────────── */
  /* Only target layout flex containers, not nav/inline flex */
  [style*="display:flex"][style*="gap:3rem"],
  [style*="display:flex"][style*="gap:4rem"],
  [style*="display:flex"][style*="gap:5rem"],
  [style*="display:flex"][style*="gap:2rem"]:not(nav):not([class*="nav"]):not([id*="nav"]) {
    flex-direction: column !important;
    gap: 1.5rem !important;
  }

  /* ── HERO SECTIONS ───────────────────────────────────── */
  [style*="min-height:100vh"],
  [style*="min-height: 100vh"] {
    min-height: 90vw !important;
    padding-top: 140px !important;
    padding-bottom: 3rem !important;
  }

  /* Section top/bottom padding */
  [style*="padding:7rem 0"],
  [style*="padding: 7rem 0"] { padding: 4rem 0 !important; }
  [style*="padding:5rem 0"],
  [style*="padding: 5rem 0"] { padding: 3rem 0 !important; }
  [style*="padding:160px 0"],
  [style*="padding: 160px 0"] { padding-top: 130px !important; padding-bottom: 3rem !important; }
  [style*="padding:140px 0"],
  [style*="padding: 140px 0"] { padding-top: 120px !important; padding-bottom: 3rem !important; }

  /* ── TYPOGRAPHY ──────────────────────────────────────── */
  h1, [style*="font-size:clamp(2.8rem"],
  [style*="font-size:clamp(2.5rem"],
  [style*="font-size:clamp(2.2rem"] {
    font-size: clamp(2rem, 8vw, 3rem) !important;
    line-height: 1.1 !important;
  }

  h2, [style*="font-size:clamp(2rem"] {
    font-size: clamp(1.6rem, 6vw, 2.2rem) !important;
  }

  /* Stats bar text */
  [style*="font-size:2.2rem"],
  [style*="font-size:2rem"] { font-size: 1.5rem !important; }

  /* ── CARDS ───────────────────────────────────────────── */
  /* Program cards, team cards, job cards — full width */
  [style*="border-radius:4px"][style*="border:1px solid"],
  [style*="border-radius:4px"][style*="border: 1px solid"] {
    width: 100% !important;
  }

  /* ── BUTTONS ─────────────────────────────────────────── */
  /* Button rows → stack */
  [style*="display:flex"][style*="gap:1rem"] a[style*="padding:14px"],
  [style*="display:flex"][style*="gap:1.5rem"] a[style*="padding:14px"] {
    width: 100% !important;
    text-align: center !important;
    justify-content: center !important;
  }

  /* ── TEAM SECTION ────────────────────────────────────── */
  /* Team member cards - show as single column */
  [style*="grid-template-columns:repeat(3,1fr)"],
  [style*="grid-template-columns:repeat(4,1fr)"],
  [style*="grid-template-columns:1fr 1fr"],
  [style*="grid-template-columns: 1fr 1fr"],
  [style*="grid-template-columns:1fr 2fr"],
  [style*="grid-template-columns:2fr 1fr"],
  [style*="grid-template-columns:1.5fr 1fr"],
  [style*="grid-template-columns:1fr auto"] {
    grid-template-columns: 1fr !important;
  }

  /* ── STICKY SIDEBAR ──────────────────────────────────── */
  [style*="position:sticky"][style*="top:120px"] {
    position: relative !important;
    top: auto !important;
  }

  /* ── INVESTOR / CTA DARK BOXES ───────────────────────── */
  [style*="padding:3rem 3.5rem"],
  [style*="padding:3.5rem"] {
    padding: 2rem 1.5rem !important;
  }

  /* ── FORMS ───────────────────────────────────────────── */
  input, select, textarea {
    width: 100% !important;
    font-size: 16px !important; /* prevents iOS zoom on focus */
  }
  form [style*="grid-template-columns:1fr 1fr"] {
    grid-template-columns: 1fr !important;
  }

  /* ── ANNOUNCEMENT BAR ────────────────────────────────── */
  #bmiil-ann, #bmiil-announce {
    flex-direction: column !important;
    gap: 0.5rem !important;
    padding: 0.6rem 1rem !important;
    text-align: center !important;
  }
  #bmiil-ann p, #bmiil-announce p {
    white-space: normal !important;
    font-size: 11px !important;
  }

  /* ── NAV ─────────────────────────────────────────────── */
  #bmiil-nav { padding: 0 1.25rem !important; }

  /* ── CONTACT PAGE 2-col → 1-col ─────────────────────── */
  [style*="grid-template-columns:1fr 1fr"],
  [style*="grid-template-columns:1fr 2fr"] {
    grid-template-columns: 1fr !important;
  }

  /* ── HORIZONTAL SCROLL FIX ───────────────────────────── */
  .elementor-section,
  .elementor-container,
  .elementor-widget-wrap {
    max-width: 100vw !important;
    overflow-x: hidden !important;
  }

  /* ── PROGRAMS 3-COLUMN ───────────────────────────────── */
  [style*="1fr 1fr 1fr"] { grid-template-columns: 1fr !important; }
}

/* ══════════════════════════════════════════════════════
   SMALL MOBILE (≤ 480px)
══════════════════════════════════════════════════════ */
@media (max-width: 480px) {
  [style*="max-width:1220px"],
  [style*="max-width: 1220px"] {
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }

  /* Logo area — shrink text on very small screens */
  #bmiil-nav a[href] span,
  #bmiil-nav a[href] strong {
    font-size: 0.85rem !important;
  }
  #bmiil-nav img { width: 36px !important; height: 36px !important; }

  /* Smaller section padding */
  [style*="padding:4rem 0"] { padding: 2.5rem 0 !important; }
  [style*="padding:3rem 0"] { padding: 2rem 0 !important; }

  /* Stats bar - 2 col */
  [style*="display:flex"][style*="overflow-x:auto"] {
    flex-wrap: wrap !important;
    overflow-x: visible !important;
  }
}
</style>
<?php }, 5);
