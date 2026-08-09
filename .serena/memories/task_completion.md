There is no meaningful automated test suite yet — `tests/Feature/ExampleTest.php` and `tests/Unit/ExampleTest.php` are still the unmodified Laravel scaffolding. `composer test` will pass trivially regardless of real changes; do not treat it as a correctness signal for feature work.

Until real tests exist, treat a task as done when:
1. `./vendor/bin/pint` run clean (or at least not worsened) for style.
2. Manual verification via `composer dev` + browser (web UI) and/or the Postman collection `RSMS.postman_collection.json` at repo root (API endpoints) — this collection is the closest thing to an API contract/reference in the repo.
3. For anything touching billing (`InvoiceService`, `GenerateMonthlyInvoices`, `QuotationService`) or auth/policies, double-check role-based access (admin vs technician) manually since there's no policy test coverage.