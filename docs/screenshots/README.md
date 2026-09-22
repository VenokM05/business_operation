# Screenshots

PNG captures of the running application, referenced from the **Screenshots** section of the root
`README.md`. The nine files below are committed and render in the README.

## How to (re)capture

The images were captured with a headless browser (Playwright driving the system Edge channel) against
the seeded demo, signed in as `admin@boms.test` / `password`, at a **1440×900** viewport.

1. Build assets and start the app: `npm run build` then `php artisan serve`.
2. Ensure demo data is present: `php artisan db:seed`.
3. Drive each screen and save it with the exact filename below (full-page for lists/details).

| File | Screen |
| --- | --- |
| `01-login.png` | `/login` |
| `02-dashboard.png` | `/dashboard` |
| `03-clients.png` | `/clients` |
| `04-client-detail.png` | `/clients/{id}` |
| `05-project.png` | `/projects/{id}` |
| `06-service-request.png` | `/service-requests/{id}` |
| `07-request-timeline.png` | Request detail, activity timeline |
| `08-reports.png` | `/reports` |
| `09-activity-logs.png` | `/activity-logs` |

> Use only the fake, local demo data from `php artisan db:seed` — never capture real customer data.
