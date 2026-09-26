---
paths:
  - 'tests/Browser/**'
---

# Browser

## Verify UI/frontend changes with Pest browser tests inside Sail, not host Playwright
This project has `pestphp/pest-plugin-browser` installed, which drives headless Chromium via Playwright from PHP. Sail's `laravel.test` container already has every OS dependency Playwright's Chromium needs — no setup required. See `tests/Browser/HomepageTest.php` for the `visit()` API pattern.

To manually verify a UI change (e.g. after editing a Vue component), write a throwaway Pest browser test and run it with `vendor/bin/sail artisan test tests/Browser/YourTest.php --compact`, then delete the file if it isn't meant to be a permanent regression test.

Do NOT try to run Playwright/Chromium directly on the host machine outside Sail — a bare host typically lacks required shared libraries (`libnspr4`, `libnss3`, etc.), and fixing that needs `sudo npx playwright install-deps chromium`, which requires root the agent sandbox usually doesn't have. Sail already solves this, so there's no reason to attempt it on the host.
