RSMS (Reconext Service Management System) — Laravel SaaS for IT field-service management (clients, assets, scheduled visits, findings, quotations, invoices), with a web UI (Livewire, server-rendered) AND a parallel mobile API (Sanctum) consumed by an external mobile app.

Source map:
- `app/Models/` — Eloquent models: Client, Asset, Schedule, VisitReport, Finding, Recommendation, Quotation(+Item), Invoice(+Item, InvoiceSendLog), ChecklistTemplate, AssetChecklist, NetworkChecklist, CompanySetting, DeviceToken, User.
- `app/Livewire/<Domain>/` — web UI, one folder per domain (Clients, Assets, Schedules, Reports, Findings, Quotations, Invoices, Technicians, Settings, Profile, Notifications). Each domain follows `*List`, `*Form` (shared create/edit), `*Show`.
- `app/Http/Controllers/Api/` + `app/Http/Resources/` — versionless JSON API for the mobile app, mirrors the Livewire domains 1:1 plus its own concerns (DeviceToken push registration, QuotationApproval).
- `app/Http/Controllers/` (non-Api) — traditional controllers only for: Auth, Dashboard, PdfController (signed public PDF downloads), QuotationApprovalController (public client-facing approval page).
- `app/Services/` — domain logic pulled out of controllers/Livewire: PdfService, QuotationService, InvoiceService, FcmService (push notifications via kreait/laravel-firebase).
- `app/Policies/` — authorization for Client, Schedule, Quotation, Invoice, registered manually in `mem:conventions`-documented `AppServiceProvider::boot()` (no policy auto-discovery relied upon).
- `app/Console/Commands/` — scheduled jobs: GenerateMonthlyInvoices (retainer auto-billing), SendPaymentReminders, SendScheduleReminders.
- `routes/web.php` — session-auth Livewire routes. `routes/api.php` — Sanctum routes, ALL names prefixed `api.` (see `mem:conventions`).

Roles: admin (full access) vs technician (own schedules/reports/findings/assets only) — via Spatie Permission, enforced in Policies.

Further reading:
- `mem:tech_stack` — framework/library versions and why they matter.
- `mem:conventions` — naming/structural conventions, the api. route-prefix invariant, frontend (Tailwind v4 + Alpine) patterns.
- `mem:suggested_commands` — dev server, build, artisan commands actually used in this project.
- `mem:task_completion` — what "done" means here (no real automated test suite yet).