# Puihaha Build Log

## Milestone 2

- Built the role/member account foundation, append-only audit logs, member-owned task relationship, role-scoped reset tables, legacy test-data flag, and environment-based initial administrator seeding.
- Disabled the existing Test User without deleting its row.
- Added tests for same-email role separation, disabled accounts, audit-log redaction, and append-only behavior.
- Known environment issue: the project storage and Pest result-cache folders are not writable by the current process. Tests pass when Blade compiled views use a writable temporary path.

## Preflight and Milestone 3a

- Corrected authentication eligibility so password plus role authenticates pending and unverified accounts, while state-aware middleware controls access.
- Added member/admin login pages, logout, state screens, role middleware, protected routes, per-role theme styling, and successful-login/logout audit events.
- Moved initial-admin environment reads into `config/membership.php` and removed protected account fields from `User::$fillable`.
- Updated the public-directory test and added state-aware authentication tests.

## Milestone 3b

- Added member and administrator registration, pending account states, email-verification tokens, queued mail notification, verification screen, and administrator approval/rejection pages.
- Registration does not require documents, as decided.
- Mail uses Laravel’s configured mailer; the local log mailer remains a fallback when Mailpit is unavailable.

## Milestone 3c

- Added separate member and administrator password-reset screens and brokers, role-isolated reset tests, Remember Me support, and Google Maps configuration.
- Member registration keeps the typed address usable when the Maps key is missing; a configured key enables optional coordinate capture.

## Milestone 4

- Scoped tasks to the authenticated member through `tasks.member_id`; members can create, complete, undo, and remove only their own tasks.
- Added the member portal shell and member transaction-history entry point without connecting deferred features.
- Verification: 11 tests passed with 48 assertions; protected member routes and public auth routes were smoke-tested.

## Milestone 5

- Added normalized `membership_transactions` and `payments` tables with foreign keys, unique references, one-payment-per-transaction enforcement, and additive migration `2026_11_21_000007_create_membership_transactions_and_payments_tables.php`.
- Added transaction and payment models, service rules, administrator dashboard, transaction register, payment screens, member history, and printable receipt.
- Enforced full payment equal to the selected level fee, Paid-only approval, valid transaction ownership for payments, computed expiry, and archival instead of hard deletion when transaction history exists.
- New member registration creates a pending application transaction; approval now requires confirmed email and a recorded full payment.
- Verification: 16 tests passed with 65 assertions; route list and guest/admin page smoke tests completed successfully.
- Known issue: Pest still attempts to write its result cache under a vendor folder without permission; the tests pass with the temporary writable compiled-view path and `--do-not-cache-result`.

## Milestone 6

- Added member renewal requests and level-change/upgrade requests as transaction types, with member-only forms and administrator approval through the existing paid-transaction workflow.
- A level change updates the member’s selected level only after approval; a renewal preserves the current level and begins after the current approved period when time remains.
- Prevented a member from creating a second open request while another transaction is pending payment or review.
- Verification: 19 tests passed with 84 assertions; all 64 application routes listed successfully; guest and administrator smoke tests completed for authentication, dashboard, member directory, transaction register, categories, and levels.

## Final regression pass

- Added a regression test proving members with transaction history are archived rather than hard-deleted.
- Added member-profile synchronization: the `members` record remains the profile source of truth, while linked member login credentials are updated explicitly; an admin-changed email returns the member to verification.
- Final verification: 21 tests passed with 96 assertions; all 64 routes listed successfully; all migrations are applied; production assets compiled successfully with `npm run build`.

## Fix at the end

- Resolve the existing storage/framework/views and Pest cache write permissions so the ordinary test command runs without the temporary compiled-view workaround.
- Review the current production-facing text, accessibility labels, and final documentation screenshots after the core milestones are complete.

## Round 2 — Batch A

- Added transaction-specific payment selection: Card, Bank transfer, GCash, Maya, or Cash at front desk. Online choices record only a method and generated reference; front-desk requests remain pending until an administrator records the exact level fee.
- Added member-owned receipt access, transaction-specific Pay now links, cancellation reasons, refund-review notes for paid cancellations, and atomic level-change approval.
- Added shared phone/password/address validation, gender fields, separated address columns, optional Maps pin capture with typed-address fallback, and administrator profiles.
- Added archive/restore and typed-name permanent deletion policy. A member with any transaction history is archived rather than hard-deleted; linked tasks and unpaid/cancelled transactions are only removed for an eligible permanent deletion.
- Added the centered member/admin navigation, profile menu, sign-out confirmation, theme control, toasts, validation summary, task checkmark/delete affordances, completed timestamps, and task insights cards/charts without a chart CDN.
- Set the example queue connection to `sync` so the project works without a worker; the log mailer remains the local mail fallback.
- Verification: 21 tests passed with 96 assertions; all 74 routes listed; public auth pages returned 200, protected guest pages redirected, administrator dashboard/member directory/transaction/profile pages returned 200, and production assets compiled successfully.
- Known environment issue: the ordinary Laravel log and Pest result-cache locations remain unwritable in this session. Checks used the existing temporary compiled-view path, `LOG_CHANNEL=stderr`, emergency log level, and `--do-not-cache-result`; this does not change application configuration.

## Round 2 — Batch B

- Added the standard Laravel `notifications` table, database notifications with a bell panel and 60-second polling, unread/read controls, registration/payment/approval/request notifications, and the renewal reminder check.
- Added inactive-member login routing, the reactivation screen, reactivation requests, administrator review outcomes, and archived-account blocking. Inactive members see only the reactivation state and cannot enter the member portal.
- Added the generic `member_requests` table for reactivation, category changes, and profile changes, including online/front-desk source values, prior/proposed JSON values, consent, reviewer information, and email re-verification fields.
- Added private `member_documents` records and a non-public `member_private` disk. Files use random names, PDF/JPG/PNG MIME and extension checks, a 5 MB limit, privacy consent, administrator-only role middleware, and a `MemberDocumentPolicy`.
- Added online category-change and profile-change screens. Name/birth-date changes require supporting documents; category changes require eligibility evidence; an email change must be verified before an administrator can approve it. Administrator edits remain the front-desk path and are audited.
- Added the administrator activity-log page with actor, role, action, and date filters, pagination, printing, and the dashboard’s ten most recent audit entries.
- Verification: 25 tests passed with 127 assertions; all 91 routes listed; production assets compiled successfully; public authentication pages, protected guest redirects, administrator dashboard/member directory/profile/approval/request/activity/transaction pages, and the notifications endpoint returned expected responses.
- Known environment issue: Laravel log/storage and Pest result-cache folders are not writable by this session. Tests use a writable temporary compiled-view path and, for document tests only, a temporary private-disk root; this does not alter production storage configuration.

## Fix at the end — Round 2 handoff

- Repair the Windows write permissions for `storage`, `storage/framework/views`, `storage/logs`, and Pest’s vendor result-cache folder so the ordinary test command can run without temporary paths.
- Review final wording, keyboard/screen-reader behavior, and documentation screenshots after the professor’s final presentation flow is confirmed.
- Hosting, mobile-specific polish, and the deferred PuiXD rename remain intentionally outside this build.

## UI layout cleanup — portal and receipts

- Rebuilt the shared member/admin header as a stable three-column layout: brand on the left, navigation centered, and notifications/profile/theme controls grouped on the right.
- Added the supplied light/dark profile and bell SVG assets under `public/assets/icons/` and wired them to the theme switch.
- Fixed the profile dropdown spacing and sign-out confirmation modal positioning, including dark-mode styling.
- Added a screen-only top-left `Go back` link to the shared receipt view; it is hidden by print CSS for both member and administrator receipt routes.
- Centered login labels, shortened the role-switch link, separated Register and Forgot password links, compacted the login card, and replaced Show/Hide text with an accessible eye icon.
- Limited the client-side password-complexity rule to new-password forms so existing login credentials are submitted normally. This fixes the red validation state that previously prevented the administrator login form from being submitted.
- Granted the local Windows account modify access to the existing Laravel storage and Pest cache folders. Normal Windows Laravel tests now pass without the prior view-compilation failure.
- Verification: 25 tests passed with 127 assertions; 91 routes listed; `npm run build` passed; public login and registration pages returned HTTP 200 and guest-protected portal pages redirected as expected.

## Final local completion — weather, supplied branding, responsive UI, and reports

- Corrected the real Laravel password-reset notification so every member/admin reset email generates a role-specific URL. Added regression coverage for notification URL payloads, same-email role isolation, and cross-role destination blocking.
- Added the server-side OpenWeather endpoint with validated coordinates, configuration-only key access, ten-minute coordinate caching, normalized responses, and graceful missing-key/provider-error handling. Added member saved-location weather and administrator configured office-location weather cards without exposing the provider key to Blade or JavaScript.
- Integrated the supplied Puihaha light/dark SVG logos and full authentication background. Auth views now use shared logo/head components, the legacy brand-mark CSS was removed, the supplied background is runtime-resolved from `public/assets/backgrounds/`, and receipt/task branding uses the same component.
- Added an accessible sliding light/dark switch, keyboard-friendly mobile navigation dropdown, reduced-motion support, supplied profile/bell theme assets, and consistent eye-icon password controls. Receipt Go Back remains outside the printable card and is hidden by print CSS.
- Added administrator Reports for members, transactions, payments, and expiring memberships with category/level/status/date/search filters and CSV export. Added administrator-only access and a member-isolation regression test.
- No migration was needed for this completion batch. `.env`, passwords, API keys, SQL exports, and private data were not modified or displayed.
- Final verification: 30 tests passed with 143 assertions; 94 routes listed; focused reports routes registered; Vite build passed; Blade/application caches cleared; login, registration, forgot/reset pages and supplied logo/background assets returned HTTP 200; guest access to `/admin/reports` redirected as expected; `git diff --check` reported no whitespace errors.

## Known issues / fix at the end

- The Vite build keeps the absolute `/assets/backgrounds/auth-background.svg` URL unresolved at build time by design; it is served correctly at runtime and returned HTTP 200 locally.
- The supplied logo ZIP did not contain the three separately named favicon files referenced in its prompt. The shared head component uses the supplied light-mode 512 PNG as the favicon and Apple touch icon fallback; no invented logo asset was added.
- A second live responsive browser tab could not be opened by the local browser-preview helper before timeout. The current login page rendered in the live browser accessibility tree, and HTTP plus automated feature checks passed. Final visual checks at 390px should still be performed manually in Edge before presentation.

## Permanent member deletion with immutable transaction history

- Created additive migration `2026_11_23_000001_add_transaction_history_snapshots.php`. Existing transactions receive member name, membership number, category, and level snapshots; new transactions capture these values at creation time.
- Changed the transaction member foreign key to allow the member profile to be deleted while retaining transaction and payment rows. The deleted profile is represented in administrator history by its immutable snapshot rather than a live member relationship.
- Permanent member deletion now removes the member profile, member login, member-owned tasks, notifications, change requests, and private document files. Append-only audit logs, transactions, payments, references, amounts, and receipts remain preserved for administrators.
- Prevented approval, cancellation, and payment recording for transactions whose member profile has already been deleted. Administrator reports, dashboard activity, transaction register, and receipts fall back to the stored historical snapshots.
- Baseline was pushed before this change as commit `111cb47`; the live local database has not been migrated.
- Verification: 30 tests passed with 150 assertions; route list generated successfully; `php artisan migrate:status` shows only the new snapshot migration pending; `npm run build` passed.

## Fix at the end

- Before running the pending migration on the local MySQL database, make a fresh phpMyAdmin Custom export of `tw31_tasks` containing structure and data.
- Manually test permanent deletion with a member who has an approved transaction, payment, task, change request, and private document: confirm only the non-financial records disappear, while the administrator transaction register, receipt, report, and audit entry remain available.

## QA feedback — visual and interaction refinements

- Centered in-app success/error/info notices so they no longer compete with the portal navigation or browser chrome.
- Added a plain-language Remember me explanation; browser password-manager prompts remain browser-controlled and are not styled by the Laravel app.
- Normalized the eye icon path in every password field at runtime, including registration and reset-password confirmation fields.
- Improved the member-directory filter spacing and mobile grouping, with a clearer Archived control and stronger Clear action.
- Reflowed the permanent-delete confirmation form so the exact-name instruction, input, and bold action are visibly separated.
- Deferred registration proof documents and the View Documents profile page to the next schema-backed batch; no partial upload flow was added here.

## QA feedback — expired-session recovery

- Added exception handling for Laravel 419 CSRF/session-expiry responses: browser requests return to the member login form with a session-expired message.
- Added a defensive unauthenticated role-middleware redirect to the member login form.
- Preserved genuine wrong-role 403 responses so authorization boundaries are not hidden.
- Added regression tests for expired-session recovery and real role mismatch behavior.
- Verification: 32 tests passed with 154 assertions; all 94 routes listed; Vite build passed; a real browser-style POST without a CSRF token returned a 302 redirect to the member login route.

## QA feedback — toast alignment

- Centered the message text inside every shared toast notification.
- Moved every toast close button to the far-right edge and vertically centered it.
- Verification: 32 tests passed with 154 assertions; all 94 routes listed; Vite build passed; login pages returned HTTP 200.

## QA feedback — location guidance and member documents

- Removed the Remember me helper sentence from the login pages.
- Changed the shared Maps script to a single deferred loader so the map callback can initialize reliably without competing async loads.
- Updated the member weather card to distinguish a saved typed address from the latitude/longitude pin required by OpenWeather, with a direct Edit details link when coordinates are missing.
- Added an administrator-only Member Documents page and inline private-file viewer. The existing `MemberDocumentPolicy` still protects both the list and file route; members cannot access either route.
- Added a View documents action to the member profile, keeping Archive/Restore below it.
- Added regression coverage for document listing, member isolation, and inline administrator viewing.
- Current database check: member records currently have no latitude/longitude pairs, while an administrator profile has coordinates. Existing member addresses therefore need a map pin captured and approved before member weather can load.

## Batch 1 — administrator-owned learning tasks

- Reworked tasks so administrators assign, edit, and archive member learning tasks; members can only view their own active assignments and toggle completion.
- Added additive migration `2026_11_24_000001_add_admin_task_fields_to_tasks_table.php` for task creator, status, deadline, publication, and archive metadata. Existing completed task rows were preserved and marked completed.
- Added additive migration `2026_11_24_000002_create_task_extension_requests_table.php` for member deadline-extension requests, administrator decisions, notes, and review timestamps.
- Added `AdminTaskController`, `TaskExtensionRequestController`, `TaskExtensionRequest` model, three Form Requests, and queued `TaskPortalNotification` with after-commit dispatch.
- Added administrator task register, assignment form, edit form, archive action, and extension-request approval/rejection controls. Added the member “i” extension modal and removed member task creation/deletion controls.
- Added a visible Insights section with three chart cards in one column and three rows: completion status, recent assignments, and recent completions.
- Added audit entries for task creation, updates, archive, completion/reopen, and extension-request decisions.
- Verification: migrations applied additively; 37 tests passed with 183 assertions; `php artisan route:list` shows 103 routes; `npm run build` passed; public login/registration pages returned HTTP 200; guest access to `/tasks` and `/admin/tasks` redirected to login.
- Known issue: the existing Pest result-cache directory still reports a Windows permission warning, but the full suite completes successfully with `--do-not-cache-result`.
- Manual smoke URLs: `/admin/tasks`, `/admin/tasks/create`, `/tasks`, and `/tasks#insights`.

## Batch 1 hotfixes and Batch 2 — payer snapshots and redacted receipts

- Added an `All active members` assignment option. One task record is created for each active, non-archived member inside one database transaction, with a task notification queued for each member after commit.
- Simplified member extension requests to a reason-only form. The requested deadline is now nullable, while the administrator must choose the new deadline when approving the request.
- Switched task and extension database notifications to immediate after-commit delivery so they appear without a local queue worker; the notification class still follows Laravel's queued/after-commit convention for environments that use a worker.
- Redesigned the three Insights rows, added the authenticated role-aware 403 recovery page, improved auth split-screen alignment/sticky hero behavior, and replaced raw profile-request JSON with a readable changed-field comparison.
- Added additive migration `2026_11_24_000003_make_task_extension_deadline_optional.php`.
- Added additive migration `2026_11_24_000004_add_payer_and_email_snapshots.php`. Existing transaction email snapshots are backfilled from the member profile; payer fields are nullable for existing payments and are captured for new checkout flows.
- Added a two-step payment checkout for online and front-desk methods. Payer name, account/contact number, address, and optional map coordinates are stored on the payment only and never overwrite the member profile.
- Added front-desk pending-payment handling, administrator cash recording, immutable member-email snapshots, and receipt redaction that displays payer account/contact values as first-three/last-two digits with six masking characters.
- Prevented pending front-desk placeholders from appearing as paid or exposing a receipt before the cash payment is recorded.
- Verification: 41 tests passed with 207 assertions; 103 application routes listed; both additive migrations applied; PHP syntax checks passed for 90 files; `git diff --check` passed; Vite production build passed; public auth/branding/background smoke URLs returned HTTP 200; guest access to protected task and transaction pages redirected with HTTP 302. The only remaining local warning is Pest's unwritable vendor result-cache folder; it does not change the passing result.
- Manual smoke URLs: `/admin/tasks/create`, `/admin/tasks`, `/tasks#insights`, `/admin/requests/{id}`, `/member/transactions`, `/member/transactions/{transaction}/payment`, `/admin/transactions`, and `/admin/transactions/{transaction}/receipt`.

## Task workspace, navigation, and authentication refinement

- Added additive migration `2026_11_24_000005_add_priority_to_tasks_table.php`. Existing task rows remain intact and use the database default `medium` priority.
- Added High, Medium, and Low priority validation, administrator assignment/edit controls, administrator search and priority filters, and theme-aligned priority badges on member and administrator task surfaces.
- Split the member portal into Home (`/tasks`) and the dedicated My Tasks workspace (`/my-tasks`). Home now shows weather, live summary counters, the five highest-priority/nearest-deadline incomplete tasks, and Insights; My Tasks contains the full searchable, filterable, paginated task list.
- Added member-readable My Membership, Categories, and Levels pages and synchronized the member navigation to Home, My Tasks, My Membership, Categories, Levels, and History. Mobile navigation continues to use the accessible menu dropdown.
- Added AJAX task completion with JSON fallback behavior: completion status, summary counters, completion percentage, overdue pill, and completion chart points update without a full page reload; the original form remains available if JavaScript or the request fails.
- Shortened the administrator registration hero to “Guide the learning journey.”, retained “Begin your next chapter.” for member registration, and made role eyebrow labels larger with the role word emphasized.
- Updated execution order: Batch 2 payer snapshots and redacted receipts remain complete; all later task, navigation, and member-portal work must treat `/tasks` as Home and `/my-tasks` as the full task workspace.
- Verification during this refinement: priority migration applied successfully; focused regression tests passed; full regression, route inventory, asset build, and smoke checks are recorded below after completion.

### Final verification for this refinement

- Verification: 45 tests passed with 244 assertions; 107 application routes listed; priority migration `2026_11_24_000005_add_priority_to_tasks_table` is applied; PHP syntax checks passed for 92 files; `git diff --check` passed; and `npm run build` passed.
- HTTP smoke checks: `/login/member`, `/login/admin`, `/register/member`, `/register/admin`, the supplied logo asset, and the supplied authentication background returned HTTP 200. Guest requests to `/tasks`, `/my-tasks`, `/my-membership`, `/membership/categories`, `/membership/levels`, `/admin/tasks`, `/admin/tasks/create`, and `/my-transactions` correctly returned HTTP 302 to authentication.
- Known environment warning: Pest still cannot write its vendor result-cache file in this Windows session; the suite completes successfully with `--do-not-cache-result`. Vite keeps the authentication background URL for runtime resolution by design.

## Batch 3 completion and Batch 4 — self-service wiring, notifications, admin workspace, and print views

- Finished level-change prefill: `/membership/levels` now sends `?level={id}`, the selected level is restored in the request form, and a live benefits, fee, and validity card updates on initial load and selection changes.
- Finished category-change prefill: `/membership/categories` now sends `?category={id}` and the request remains a pending administrator review without changing the member record immediately. Current category/level cards are labeled instead of offering an invalid self-change action.
- Expanded `/my-requests` to include profile, category, reactivation, and task-extension requests, with request status, administrator notes, and the count/privacy status of supporting documents.
- Added `NotificationDestinationResolver` and applied it to database notifications and notification JSON responses. Destinations are constrained to the recipient’s role, same-application routes only, and fall back to `/tasks` for members or `/admin/dashboard` for administrators. Notification links are marked read before navigation; profile and notification dropdowns are mutually exclusive and close on outside click or Escape.
- Added administrator dashboard sub-tabs for Latest transactions and Audit trail, spaced the weather card from the activity section, and displayed immutable `member_email_snapshot` values on the dashboard and transaction register.
- Removed the legacy-test-data badge from the user-facing member directory/profile and moved the member status pill into a dedicated row below the email so mobile profiles do not overlap.
- Replaced inline cancellation inputs with accessible centered cancellation/reason modals, added clean pagination arrows, and formatted activity-log details as labeled key/value data rather than raw JSON.
- Added a Print Report control, generated filter/timestamp summaries, and print CSS for reports and activity logs; navigation, filters, controls, and pagination are hidden in print output.
- Added `BatchFourTest.php` for self-service prefill, task-extension request history, notification fallback isolation, and historical email snapshot display.
- No migration was required for this batch; the existing additive schema already contained the required request, document, task-extension, notification, audit, and transaction snapshot tables.
- Verification: 49 tests passed with 264 assertions; 107 application routes listed; `git diff --check` passed; PHP syntax and Blade rendering checks passed; `npm run build` passed. The only local warning is Pest’s unwritable vendor result-cache file, so the suite was run with `--do-not-cache-result`.
- Manual smoke URLs: `/membership/levels`, `/membership/level-change?level={id}`, `/membership/categories`, `/membership/category-change?category={id}`, `/my-requests`, `/admin/dashboard`, `/admin/transactions`, `/admin/reports`, `/admin/activity`, and `/admin/requests/{id}`.

## Batch 5 — weather/maps sync, private-request validation, and responsive polish

- Connected the Member Home weather card to the member’s saved latitude/longitude and the Administrator Dashboard weather card to the authenticated administrator’s saved office coordinates. Both use the configured safe fallback location when a profile has no pin; an administrator never inherits a member’s private location.
- Hardened the server-side OpenWeather request with a connection timeout, request timeout, retry, caching, normalized weather icon data, and graceful provider/key failure responses. The API key remains server-side and is never sent to Blade or JavaScript.
- Corrected payer checkout map initialization so the payer address, map click/drag pin, and hidden payer coordinate fields share one initialized scope. Editing typed address fields clears stale coordinates before geocoding can replace them.
- Moved profile, category-change, and reactivation document validation into dedicated Form Request classes. Name/birth-date changes, category eligibility, and reactivation require supporting files as appropriate; ordinary profile edits remain document-free; file limits, MIME types, and privacy consent remain enforced.
- Kept documents on the private `member_private` local disk and preserved administrator-only policy-protected viewing. No public storage route was added.
- Added a redesigned weather surface for light/dark themes, a mobile portal header/drawer layout below 768px, tighter mobile control sizing, improved weather typography, and a consistent “Puihaha E-Learning Platform · © 2026” footer across platform and authentication pages.
- No additive migration was required: members, admin profiles, and payments already contained the required coordinate columns; existing task and transaction records were untouched.
- Added `BatchFiveWeatherAndDocumentsTest.php` covering saved/fallback weather coordinates, conditional document validation, and private document storage configuration.
- Verification: 53 tests passed with 283 assertions; 107 application routes listed; PHP syntax checks passed for the Batch 5 controllers, requests, and feature test; `git diff --check` passed; `php artisan view:clear` passed; and `npm run build` passed. The only local warnings are Pest’s unwritable vendor result-cache file (use `--do-not-cache-result`) and Vite’s intentional runtime resolution notice for the authentication background asset.
- HTTP smoke checks: the public auth pages, forgot-password pages, supplied logo/icon assets, and authentication background returned HTTP 200. Guest requests to `/tasks`, `/my-tasks`, `/my-membership`, `/membership/categories`, `/membership/levels`, `/my-requests`, `/admin/dashboard`, `/admin/tasks`, `/admin/reports`, and `/admin/activity` correctly returned HTTP 302 to authentication.

## Batch 6 — final regression, documentation, and Alwaysdata free-plan hardening

- Added production-only HTTPS URL forcing and configurable trusted-proxy handling. Local development remains HTTP; production-generated verification, password-reset, notification, and receipt links use the configured HTTPS application URL.
- Added named rate limiters for login, registration, verification resend, and password-reset endpoints. Existing role-aware controller throttles remain in place as a second protection layer.
- Added safe `.env.production.example` placeholders for Alwaysdata, including PHP 8.3/MariaDB values, secure cookies, database sessions/cache, synchronous queues, HTTPS URL, private API placeholders, and error-level logging. Updated `.env.example` for the local `tw31_tasks` MySQL setup and synchronous queues.
- Added themed 404, 419, 429, and 500 views while preserving the existing 419 session-expiry redirect behavior for browser form requests and the role-aware 403 page.
- Confirmed private supporting documents remain on the non-public `member_private` disk and are only served through authenticated administrator policy routes. No public document URL was added.
- Replaced the default Laravel README with the Puihaha system overview, role boundaries, local XAMPP setup, seed configuration, Maps/OpenWeather setup, mail/queue behavior, testing, privacy, and deployment references.
- Added `docs/DEPLOYMENT.md` with the Alwaysdata layout, PHP 8.3/MariaDB deployment sequence, HTTPS/SMTP/Maps guidance, production migration safeguards, and a PowerShell clean-package script that excludes secrets, private local uploads, development dependencies, tests, docs, SQL, ZIPs, Git metadata, and caches.
- No migration was required in Batch 6, and no database records were changed. Existing additive migrations remain untouched.

### Batch 6 manual QA checklist

#### Member flow

- [ ] Open `/login/member` over HTTPS; confirm the themed page loads without mixed-content warnings.
- [ ] Submit empty, invalid, and repeated login attempts; confirm validation, generic credential errors, and throttling behave correctly.
- [ ] Register a member, confirm the email, and verify the account reaches the correct pending/approval state.
- [ ] Use Forgot password from the member login; confirm the reset link contains the member role and cannot open the administrator flow.
- [ ] Sign in and verify `/tasks`, `/my-tasks`, membership pages, history, requests, notifications, weather, and receipts are member-scoped.
- [ ] Save a map pin and a typed address; verify coordinates persist and weather uses the saved location or safe fallback.
- [ ] Submit a profile/category/reactivation request and verify document requirements, consent, private storage, and request status.
- [ ] Open a receipt, use the back control, print it, and confirm navigation controls do not appear in print output.
- [ ] Expire a form/session and confirm the user returns to member login with a helpful message rather than a raw error.
- [ ] Check mobile navigation, light/dark theme switching, footer placement, typography, and 404/419/429/500 pages.

#### Administrator flow

- [ ] Open `/login/admin`; confirm the administrator login, generic errors, remember-me behavior, and throttling.
- [ ] Register/approve an administrator where applicable, then confirm administrator-only routes remain isolated from members.
- [ ] Use administrator Forgot password and confirm the reset link stays in the administrator broker and destination.
- [ ] Review the dashboard weather fallback and saved office coordinates without exposing a member’s private location.
- [ ] Create, assign, edit, prioritize, complete/reopen, archive, and extend tasks; verify notifications and role-safe destinations.
- [ ] Review members, categories, levels, approvals, transactions, reports, activity, and print views.
- [ ] Open a member’s private documents and confirm an administrator can view them while a member receives an authorization response.
- [ ] Test cancellation/reason modals, immutable email/payer snapshots, redacted receipts, pagination, mobile layout, and theme contrast.
- [ ] Confirm production error pages contain no debug stack traces and that config caching succeeds after the real server `.env` is configured.

### Batch 6 verification record

- Full feature suite: 53 tests passed with 283 assertions before hardening changes.
- Route inventory before hardening: 107 application routes. Batch 6 adds no routes.
- Final verification: 57 tests passed with 369 assertions; 107 application routes listed; `npm run build` passed; `php artisan optimize` completed successfully with normal Windows write permissions and was cleared afterward; `composer validate --strict` passed; `composer install --no-dev --dry-run --no-interaction` completed without changing the local installation; runtime `env()` audit found no calls in `app/` or `routes/`; and `git diff --check` passed.
- Known local environment note: Pest’s Windows vendor result-cache warning is avoided with `--do-not-cache-result`; Vite’s runtime authentication-background resolution notice is expected.
- HTTP smoke checks: `/login/member` and `/login/admin` returned HTTP 200, the protected portal remains authentication-gated, and an unknown URL rendered the themed 404 page. Batch 6 completes the local deployment-preparation scope. Live Alwaysdata deployment and production SMTP activation remain a separate user-controlled pass.

### Batch 7A

- Fixed approval workflow selection so administrator approval targets the member's application transaction instead of a newer renewal or level-change transaction. A fully paid application may be approved from paid, an already-approved application remains valid and idempotent, and the amount is checked against the selected level fee.
- Initial application transaction approval now returns the administrator to /admin/approvals with next-step feedback. Level-change request creation also provides explicit payment-next feedback.
- Removed the duplicate administrator task toast include, raised portal/auth toast stacking above content while keeping it below the sticky navigation, centered the verification/approval/inactive state cards, and added password/select/map spacing refinements.
- Added dynamic minimum-age validation: 18 for member/admin registration and administrator member create/edit, and 60 when the Senior Citizen category is selected. Added a required terms/privacy acceptance checkbox to member and administrator registration.
- No database migration was needed for Batch 7A; no migration command was run.
- Batch-specific regression coverage: tests/Feature/BatchSevenATest.php covers paid/already-approved application behavior, newer pending transaction isolation, initial-application redirect, registration consent, dynamic age validation, and administrator member create/edit age rules.
- Verification: php artisan test --do-not-cache-result passed 61 tests with 392 assertions; php artisan route:list reported 103 routes; npm run build passed after the required local Windows process-permission retry; and git diff --check passed. The existing Pest Windows result-cache permission warning remains when the cache is enabled, so the no-cache test flag is the reliable local command.

### Batch 7C locked scope addendum

- Carry forward the role-aware Remember-me audit: verify remember input reaches the role-scoped login attempt, protected URLs restore the correct role session, login forms expose browser autocomplete attributes and a clear Remember-me tooltip, and sign-out invalidates the remember token.
- Carry forward the administrator portal alignment: administrator login lands on /admin/dashboard; navigation is ordered Dashboard, Approvals, Members, Transactions, Tasks, Membership, Reports, and Activity log; Approvals shows a pending-count pill; Membership groups Categories and Levels in an accessible keyboard-friendly dropdown.

### Batch 7B

- Added the member invitation schema with member/user relationships, a hashed one-time token, expiry and consumption timestamps, creator administrator ID, and timestamps. No raw invitation token or plaintext password is persisted.
- Administrator-created members now receive a pending member account, a pending application transaction, and a queued-after-commit invitation email. The invitation acceptance flow sets the member password, confirms the invited email, consumes the token, and leaves payment/administrator approval as the next workflow steps.
- Added regression coverage for valid acceptance, password policy, expired/reused invitation rejection, pending application creation, and token privacy.
- Migration 2026_11_24_000006_create_member_invitations_table ran successfully locally.
- Verification: full suite passed 66 tests with 449 assertions; route list reports 105 routes; npm run build passed with the existing runtime-resolved authentication background notice; git diff --check passed.

### Batch 7C — navigation, remember-me clarity, portal polish, and private document viewer

- Administrator login now lands on /admin/dashboard. The administrator topbar and mobile drawer follow the workflow order Dashboard, Approvals, Members, Transactions, Tasks, Membership, Reports, and Activity log. Membership is an accessible keyboard-friendly Categories/Levels dropdown, closes on outside click or Escape, and shows the pending-account count when applicable.
- Remember-me login now has an explanatory tooltip, and logout clears both the database remember token and the persistent browser recaller cookie. Added regression coverage for the administrator landing route, navigation order, and remember-me invalidation.
- Added mobile drawer slide-down behavior, theme-switch visibility, mutual menu closing, reduced-motion modal transitions, and corrected member Insights smooth scrolling to #insights.
- Refined the member Home greeting, task-workspace spacing and filters, membership/level/category typography, and the administrator member filters, transaction payment-channel layout, activity actor/action display, pagination, and report status select.
- Added a bordered administrator-only inline document viewer with Back navigation, no-store headers, no download control, private-policy reminder, and an empty-state reminder when no documents exist. Existing private storage and policy boundaries remain unchanged.
- No additive migration was needed for Batch 7C. The existing invitation migration remains database/migrations/2026_11_24_000006_create_member_invitations_table.php.
- Verification: php artisan test --do-not-cache-result passed 68 tests with 462 assertions; php artisan route:list --except-vendor reported 106 routes; php artisan view:cache passed with the final Blade templates; npm run build passed; and git diff --check passed. Vite retains the expected runtime-resolution notice for the authentication background asset. Pest's vendor result-cache permission warning remains avoided by the no-cache test flag.

### Batch 7D — targeted visual and responsive hotfix

- Restored the administrator members filter to a single desktop row while retaining compact two-row mobile filters, widened the administrator task search, and kept a visible Clear reset action.
- Increased result counters and membership card metadata, stabilized password eye alignment when validation text appears, aligned the member Home priority heading, and kept My Tasks charts in a desktop row with mobile stacking.
- Preserved the compact mobile theme switch and added overflow-safe workspace sizing. No database migration or data change was needed for Batch 7D.
- Follow-up visual correction: reduced the administrator task search/select widths and prevented the desktop Clear action from wrapping onto a second row.
- Restored the compact desktop member-directory toolbar styling and changed the administrator task filter action to Search with the matching search icon.
- Added an administrator Approvals dropdown for Account approvals and Member requests, renamed Membership setup to Membership without the visible caret, and added padding/wrapping fixes to private supporting-document rows on request review pages.
- Improved dark-mode mobile navigation contrast for highlighted dropdowns and made the theme switch thumb/icon explicit so it remains visible at phone widths.
- Added mobile-safe toast, notification/profile popover, and activity-pagination sizing, plus balanced topbar vertical spacing for the compact header.
