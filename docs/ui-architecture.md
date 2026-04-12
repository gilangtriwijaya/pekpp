# PEKPPP UI — Architecture Decisions

Status: LOCKED (TAHAP 1)

Decisions:

- Tailwind: layout, spacing, typography, grid, and utilities.
- Custom CSS: UI components requiring stable, namespaced styles (sidebar, topbar, card, profile menu, buttons).
- Namespace: All custom CSS must be under the `.pekppp-ui` root class; targeting `body` or `html` is forbidden.
- JavaScript: Vanilla JS only, event-driven, no inline scripts. All UI state toggles must be explicit in JS.

Namespace example:

    <body class="pekppp-ui"> ... </body>

Files added:

- `resources/css/pekppp-ui.css` — namespaced component styles and design tokens.
- `resources/js/ui.js` — event-driven UI state management.

Validation checklist (required before merge):

1. No custom CSS rules targeting `html` or `body` outside `.pekppp-ui`.
2. No inline JavaScript in Blade templates.
3. Tailwind utilities used for layout/spacing; custom CSS only for component specifics.
4. All new files are imported by `resources/js/app.js` and available to Vite.
