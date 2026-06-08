<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700&amp;family=Inter:wght@400;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                "colors": {
                    "on-secondary-fixed": "#111c2d",
                    "inverse-surface": "#213145",
                    "primary-fixed-dim": "#4ae183",
                    "on-background": "#0b1c30",
                    "tertiary-container": "#d7ae00",
                    "tertiary-fixed": "#ffe084",
                    "on-tertiary-fixed-variant": "#574500",
                    "on-primary": "#ffffff",
                    "outline-variant": "#bbcbbb",
                    "surface-container-highest": "#d3e4fe",
                    "primary": "#006d37",
                    "on-primary-fixed-variant": "#005228",
                    "on-tertiary": "#ffffff",
                    "error-container": "#ffdad6",
                    "on-tertiary-container": "#544300",
                    "primary-container": "#2ecc71",
                    "error": "#ba1a1a",
                    "surface": "#f8f9ff",
                    "on-secondary": "#ffffff",
                    "surface-variant": "#d3e4fe",
                    "primary-fixed": "#6bfe9c",
                    "on-secondary-container": "#586377",
                    "surface-dim": "#cbdbf5",
                    "secondary-fixed": "#d8e3fb",
                    "on-tertiary-fixed": "#231b00",
                    "on-error": "#ffffff",
                    "surface-container-lowest": "#ffffff",
                    "surface-container-low": "#eff4ff",
                    "surface-tint": "#006d37",
                    "surface-container": "#e5eeff",
                    "on-surface": "#0b1c30",
                    "on-surface-variant": "#3d4a3e",
                    "on-primary-fixed": "#00210c",
                    "background": "#f8f9ff",
                    "tertiary-fixed-dim": "#eec209",
                    "surface-container-high": "#dce9ff",
                    "secondary-container": "#d5e0f8",
                    "on-secondary-fixed-variant": "#3c475a",
                    "inverse-on-surface": "#eaf1ff",
                    "on-error-container": "#93000a",
                    "inverse-primary": "#4ae183",
                    "outline": "#6c7b6d",
                    "secondary": "#545f73",
                    "secondary-fixed-dim": "#bcc7de",
                    "tertiary": "#735c00",
                    "surface-bright": "#f8f9ff",
                    "on-primary-container": "#005027"
                },
                "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
                },
                "spacing": {
                    "xs": "4px",
                    "md": "16px",
                    "sm": "8px",
                    "gutter": "16px",
                    "xl": "32px",
                    "margin_mobile": "20px",
                    "lg": "24px"
                },
                "fontFamily": {
                    "h2": ["Lexend"],
                    "button": ["Inter"],
                    "body-sm": ["Inter"],
                    "h3": ["Lexend"],
                    "label-caps": ["Inter"],
                    "h1": ["Lexend"],
                    "body-md": ["Inter"],
                    "body-lg": ["Inter"]
                },
                "fontSize": {
                    "h2": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                    "button": ["14px", {"lineHeight": "20px", "fontWeight": "600"}],
                    "body-sm": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                    "h3": ["20px", {"lineHeight": "28px", "fontWeight": "500"}],
                    "label-caps": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "700"}],
                    "h1": ["30px", {"lineHeight": "36px", "letterSpacing": "-0.02em", "fontWeight": "600"}],
                    "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                    "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}]
                }
            },
        },
    }
</script>
<style>
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
    .bento-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        grid-auto-rows: minmax(100px, auto);
        gap: 16px;
    }
    body {
        min-height: max(884px, 100dvh);
    }
</style>
