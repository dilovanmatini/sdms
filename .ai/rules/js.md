---
paths:
  - 'resources/js/**'
---

# Js

## Use Flowbite React for UI components
shadcn/Radix UI has been removed. For React UI in resources/js, use Flowbite React only. Treat https://flowbite-react.com/llms-full.txt as the canonical API/docs source before inventing components. Prefer Flowbite React primitives (Modal, Table, Button, Badge, Card, Alert, Dropdown, Tabs, Sidebar, Navbar, Select, TextInput, Textarea, Checkbox, ToggleSwitch, Pagination, Tooltip, Accordion, etc.) over custom equivalents or other UI kits. Do not reintroduce shadcn, Radix UI, or `@/components/ui`.

## Flowbite Button colors ≠ Badge/Alert colors
`Button` uses palette names: `default`, `alternative`, `light`, `dark`, `gray`, `green`, `red`, `blue`, … — not state names. Use `color="green"` for ترحيل / positive actions and `color="red"` for destructive. `success` / `failure` / `warning` / `info` are valid on `Badge` and `Alert` only; on `Button` they apply no color classes and look unstyled.

## Use Lucide React for icons
Use lucide-react icons only. Pick icon names from https://lucide.dev/icons/. Import named icons from lucide-react (e.g. import { Search } from "lucide-react"). Do not add other icon libraries (Heroicons, Font Awesome, react-icons, etc.) for new work.

## Searchable selects
Flowbite React has no Combobox yet. For option lists expected to exceed ~5 values, use `@/components/searchable-select` (local filter) or `@/components/async-searchable-select` (AJAX via `/lookups/*`). Do not add react-select or other select libraries. Keep plain Flowbite `Select` only for short static enums (e.g. user roles, payment methods). Catalogs that grow from the database (products, distributors, suppliers, categories, units, open invoices) must use `AsyncSearchableSelect` + the lookup routes — never preload the full list into Inertia props.

## Confirmation dialogs
Never use `window.confirm()` / `confirm()`. For irreversible or sensitive actions use Flowbite Modal confirmations: `@/components/delete-button` for deletes, `@/components/confirm-action-button` for post/cancel and other confirmed POSTs.

## Soft focus styles
Do not use Flowbite’s thick `focus:ring-2` / `focus:ring-4` halos or colored `focus:border-primary-*` on default fields. Prefer `focus:ring-0` plus `shadow-focus` (`--shadow-focus` in `resources/css/app.css` — mid-opacity black). Keep the resting gray border on focus (`focus:border-gray-300`). Buttons/icon controls: `focus-visible:shadow-focus`. Override via `resources/js/theme/flowbite.ts` so twMerge kills default ring/primary focus colors.

## Money display
System currency is shared as `usePage().props.currency` (`code`, `symbol`, `label`). Use `@/lib/money` (`useFormatMoney` / `formatMoney` / `useCurrency`) to show `$` or `د.ع` beside displayed amounts. Keep editable amount inputs numeric-only; put the symbol in labels or read-only totals. Index/report amounts usually already include the symbol from the server.
