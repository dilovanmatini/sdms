---
paths:
  - 'app/**'
---

# App

## Use Laravel 13 APIs only
This app runs Laravel 13 (laravel/framework ^13). When building features, use only Laravel 13 APIs, Artisan generators, scaffolding, and docs. Never fall back to Laravel 10/11/12 patterns, removed helpers, or outdated training knowledge. Confirm APIs with search-docs or the installed framework before writing code.

## System currency
Single system-wide currency on `system_settings.currency` (`App\Enums\Currency`: `usd` / `iqd`, default `usd`). Not multi-currency and not stored per document. Change only via general settings. Display amounts with `App\Support\MoneyDisplay` (appends `$` or `د.ع`). Keep ledger/`bc*` storage at scale 2 without symbols. Share `{ code, symbol, label }` from `HandleInertiaRequests` for the React UI.

## Arabic DomPDF
DomPDF does not shape Arabic or reverse table columns for RTL. Always build downloadable PDFs via `App\Support\ArabicPdf::fromView()` (Ar-PHP glyph reshape + IBM Plex via `PrintFont`). In print Blade views, when `$forPdf` is true, reverse table column / label-value cell order so the visual layout matches RTL; browser print keeps natural `dir="rtl"` order. Do not call `Pdf::loadView` directly for Arabic documents.
