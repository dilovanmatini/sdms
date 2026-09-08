---
paths:
  - 'app/Actions/**'
  - 'app/Http/Controllers/**'
  - 'app/Http/Requests/**'
  - 'resources/js/pages/**'
  - 'routes/**'
---

# Section CRUD convention (Category pattern)

Use Categories as the template for every maintainable section.

## Backend
- Thin controllers: authorize, then delegate to Action classes under `app/Actions/{Domain}/`.
- CRUD Actions: `IndexAction`, `CreateEditAction`, `StoreUpdateAction`, `DestroyAction`. Documents also get `PostAction`.
- Merge create+edit into `createEdit(?Model $model)` and store+update into `storeUpdate(StoreUpdateXRequest, ?Model $model)`.
- One Form Request (`StoreUpdate{Model}Request`) with `authorize()` branching on `$this->route('x')?->exists`. Use `Rule::unique(...)->ignore($model?->id)` when needed.
- After store/update: `Inertia::flash('toast', ...)` then redirect to `*.create-edit` with the model. Destroy redirects to index.
- Edit-only settings use `EditAction` / `UpdateAction` (no create merge). Read-only sections use Actions per method without create-edit merge.
- Prefer custom route groups over `Route::resource` for CRUD: `index`, `create-edit/{model?}`, `POST {model?}` store-update, `DELETE {model}` destroy.

## Frontend
- Pages use `FormCard` (+ `FormActions` on forms), not `PageHeader` / bare `Heading` shells.
- `FormActions`: `justify-between` with two groups — `children` for primary/normal actions (save, post, print); `secondary` for back and sensitive actions (رجوع for navigate-away; document cancel stays إلغاء …). Do not mix back/document-cancel into the primary group. On draft documents, put تأكيد نهائي beside save in the primary group (actions sit outside the field `<form>` via `form="…"` so the post Inertia `<Form>` is not nested).
- Index: FormCard with Lucide icon, title, description, primary CTA (`Plus`) in `actions`; body has SearchFilter, bordered RTL table (`text-start`/`text-end`), PaginationLinks.
- Index Actions resolve page size via `ResolvesDatagridPerPage` (`->paginate($this->perPage($request, 'grid-key'))`). Allowed sizes: 5, 10, 20, 30, 50, 100; default 10. Preference is stored per datagrid in the `datagrid_per_page` cookie (and mirrored in localStorage by `PaginationLinks`).
- `PaginationLinks` requires `storageKey` matching the backend grid key. Show pagination whenever `total > 0` (even a single record); hide only when empty.
- Single `create-edit.tsx` page; Wayfinder `storeUpdate.form(id?)`; create icon vs `Edit` for edit; Arabic copy; logical Tailwind.
- `Page.layout` breadcrumbs: list → إضافة/تعديل.
- If regenerating Wayfinder via Artisan (not Vite), always use `php artisan wayfinder:generate --with-form --no-interaction`. Plain `wayfinder:generate` drops `.form` helpers and breaks Inertia `<Form {...x.form()}>` pages. Vite's `wayfinder({ formVariants: true })` already includes forms.

## Out of scope
- Fortify/auth pages and Fortify Actions.
