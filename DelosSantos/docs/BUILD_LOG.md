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
