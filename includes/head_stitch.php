<?php
// includes/head_stitch.php
// Shared head section: Tailwind, fonts, color tokens for all pages
?>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<!-- Material Icons -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<!-- Tailwind CDN -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
  tailwind.config = {
    darkMode: "class",
    theme: {
      extend: {
        colors: {
          "on-primary": "#ffffff",
          "on-background": "#0b1c30",
          "error-container": "#ffdad6",
          "error": "#ba1a1a",
          "on-secondary-container": "#fefcff",
          "surface-bright": "#f8f9ff",
          "surface": "#f8f9ff",
          "on-error-container": "#93000a",
          "on-tertiary": "#ffffff",
          "surface-container": "#e5eeff",
          "on-secondary": "#ffffff",
          "surface-dim": "#cbdbf5",
          "surface-container-highest": "#d3e4fe",
          "surface-container-low": "#eff4ff",
          "surface-variant": "#d3e4fe",
          "on-primary-container": "#005324",
          "primary": "#006d32",
          "outline-variant": "#bbcbb9",
          "primary-container": "#00d166",
          "inverse-primary": "#30e375",
          "secondary": "#0059bb",
          "inverse-on-surface": "#eaf1ff",
          "surface-container-lowest": "#ffffff",
          "tertiary": "#565e74",
          "secondary-fixed-dim": "#adc7ff",
          "surface-container-high": "#dce9ff",
          "primary-fixed-dim": "#30e375",
          "outline": "#6c7b6c",
          "on-surface-variant": "#3c4a3d",
          "background": "#f8f9ff",
          "inverse-surface": "#213145",
          "on-secondary-fixed-variant": "#004493",
          "primary-fixed": "#64ff92",
          "secondary-container": "#0070ea",
          "on-tertiary-container": "#3f475c",
          "on-surface": "#0b1c30",
          "tertiary-container": "#aeb5cf",
          "on-secondary-fixed": "#001a41",
          "on-error": "#ffffff"
        },
        fontFamily: {
          "headline": ["Space Grotesk", "sans-serif"],
          "display": ["Space Grotesk", "sans-serif"],
          "body": ["Inter", "sans-serif"],
          "label": ["Inter", "sans-serif"]
        }
      }
    }
  }
</script>
<style>
  .material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    display: inline-block; vertical-align: middle;
  }
  body { font-family: 'Inter', sans-serif; }
  .font-headline, .font-display { font-family: 'Space Grotesk', sans-serif; }
  ::-webkit-scrollbar { width: 6px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: #e5eeff; border-radius: 10px; }
  ::-webkit-scrollbar-thumb:hover { background: #d3e4fe; }
  .glass-nav { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
  .no-scrollbar::-webkit-scrollbar { display: none; }
</style>
