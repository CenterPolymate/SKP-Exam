# Agentic Development Guidelines & Context

This project follows the guidelines, business rules, and technical constraints documented in [`document/PROJECT_CONTEXT.md`](document/PROJECT_CONTEXT.md).

## Critical Developer Constraints

1. **PHP 8.4+ & Laravel 13**:
   - Do NOT downgrade PHP requirements or dependencies.
   - Use modern Laravel 13 syntax (Volt SFC, Model `casts()`, Anonymous migrations).

2. **Frontend Architecture**:
   - UI pages are built with **Livewire 3 + Volt** (`Volt::route()` in `routes/web.php` and Volt SFCs in `resources/views/livewire/pages/`).
   - Do NOT create traditional Blade controllers for UI pages unless strictly required for binary file downloads/streams (e.g., `SecureFileController`).
   - Styling uses **Tailwind CSS 3**. Client-side interactions use **Alpine.js 3**.

3. **Authentication & Multi-Guards**:
   - Admin guard: `web` (`users` model, role `admin`, bcrypt hash).
   - Candidate guard: `exam_applicant` (`exam_assignments` model, two-way encrypted `password_encrypted`). Never convert candidate password to one-way hash as HR needs to share it with candidates.
   - Candidate deadline: Guarded by `exam.not_expired` middleware.

4. **Private Storage & Security**:
   - All applicant identity documents (ID card, Transcript, Resume) and exam essay answers MUST be stored in private storage (`storage/app/private/`).
   - Access only through `SecureFileController`.

5. **Anti-Cheating Engine**:
   - `ExamAttempt` tracks `suspicious_activity_count` and `suspicious_activity_log`.
   - Never disable the client-side visibility/fullscreen change reporting to `/exam/attempts/{attempt}/suspicious-activity`.

6. **Testing**:
   - Run `php artisan test` to verify existing Pest tests before and after code changes.

For full technical specifications, guidelines, and procedures, refer to:
- [`document/PROJECT_CONTEXT.md`](document/PROJECT_CONTEXT.md)
- [`document/SYSTEM_SPECIFICATION.md`](document/SYSTEM_SPECIFICATION.md)
- [`document/DATABASE_SCHEMA.md`](document/DATABASE_SCHEMA.md)
- [`document/USER_MANUAL.md`](document/USER_MANUAL.md)
- [`document/DEPLOYMENT_GUIDE.md`](document/DEPLOYMENT_GUIDE.md)
- [`document/TESTING_CHECKLIST.md`](document/TESTING_CHECKLIST.md)
- [`document/PDPA_AND_SECURITY.md`](document/PDPA_AND_SECURITY.md)
- [`document/CHANGELOG.md`](document/CHANGELOG.md)

