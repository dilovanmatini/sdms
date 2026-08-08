---
paths:
  - 'resources/js/**'
  - 'resources/views/**'
  - 'lang/**'
---

# Arabic RTL UI

The application UI is Arabic-only and RTL (`lang="ar"` `dir="rtl"` on the root HTML). Put user-facing copy in Arabic. Prefer logical CSS (`ps`/`pe`/`ms`/`me`/`start`/`end`/`text-start`) over physical left/right. Prefer `gap-*` over `space-x-*` or icon `mr-*` margins. Keep Flowbite React components and lucide-react icons; use Wayfinder (`@/actions`, `@/routes`) for backend URLs.

Flowbite hardcodes many LTR utilities. Override them in `resources/js/theme/flowbite.ts` with `ThemeProvider` + `applyTheme: replace` on changed keys (see `resources/js/app.tsx`). Select chevron `background-position` stays physical `left` (no logical equivalent), with an unlayered CSS fallback in `resources/css/app.css`. Breadcrumb separators use Flowbite's hardcoded `ChevronRightIcon` — flip with `rotate-180` on `breadcrumb.item.chevron` for RTL. Main app sidebar sits on the inline-start side in RTL; use `border-e` (not `border-s`) for the content divider, keep sidebar items `w-full justify-start` when expanded and `justify-center` when collapsed, and prefer a white sidebar surface against the gray page background. Do not put `md:flex` or a hardcoded `w-64` on the Flowbite `<Sidebar>` root — `md:flex` shrinks the inner panel to content width, and `w-64` overrides the collapsed `w-16` width from the theme. Let theme `root.collapsed` control width. Placeholder nav links that reuse `/dashboard` must not all mark active — only the real dashboard item should.
