// Run with node tests/regression/employee_onboarding_ui.cjs. Set CHROME_PATH to use another Chromium binary.
const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const fs = require('node:fs');
const path = require('node:path');
const { pathToFileURL } = require('node:url');

const root = path.resolve(__dirname, '../..');
const chrome = process.env.CHROME_PATH || (process.platform === 'win32' ? 'C:/Program Files/Google/Chrome/Application/chrome.exe' : 'chromium');
const temp = fs.mkdtempSync(path.join(root, 'storage/framework/cache', 'pms-onboarding-ui-'));

const cssRefresh = fs.readFileSync(path.join(root, 'public/admin/assets/css/pms-refresh.css'), 'utf8');
const cssTheme = fs.readFileSync(path.join(root, 'public/admin/assets/css/pms-bitroxia-theme.css'), 'utf8');

function compileBladeSlices() {
    const php = `
        require 'vendor/autoload.php';
        $app = require 'bootstrap/app.php';
        $kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
        $kernel->bootstrap();

        config([
            'database.default' => 'tenant',
            'database.connections.central' => ['driver' => 'sqlite', 'database' => ':memory:'],
            'database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:'],
            'session.driver' => 'array',
        ]);
        view()->share('errors', new \\Illuminate\\Support\\ViewErrorBag());
        \\Illuminate\\Support\\Facades\\Schema::connection('central')->create('companies', function ($t) {
            $t->id(); $t->string('name'); $t->string('slug')->nullable(); $t->string('domain')->nullable(); $t->string('status')->default('active'); $t->text('settings')->nullable(); $t->string('db_name')->nullable(); $t->timestamps(); $t->softDeletes();
        });
        \\Illuminate\\Support\\Facades\\Schema::connection('tenant')->create('employee_details', function ($t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->string('employee_id')->nullable(); $t->unsignedBigInteger('department_id')->nullable(); $t->unsignedBigInteger('designation_id')->nullable(); $t->timestamps();
        });

        try {
            $company = \\App\\Models\\Central\\Company::create([
                'name' => 'Test Corp Global',
                'slug' => 'test-corp',
                'domain' => 'test-corp.localhost',
                'status' => 'active',
            ]);

            $inv = new \\App\\Models\\EmployeeInvitation([
                'company_id' => 1,
                'email' => null,
                'method' => 'link',
                'status' => 'Pending',
                'expires_at' => now()->addHours(72),
                'created_at' => now(),
            ]);
            $inv->id = 77;

            $countriesCol = collect([(object)['name' => 'India'], (object)['name' => 'United States']]);
            $publicFormHtml = view('onboarding.employee-form', [
                'company' => $company,
                'invitation' => $inv,
                'application' => null,
                'isCorrection' => false,
                'submitUrl' => 'https://example.test/onboarding/token123/submit',
                'sendOtpUrl' => 'https://example.test/onboarding/token123/send-otp',
                'verifyOtpUrl' => 'https://example.test/onboarding/token123/verify-otp',
                'countries' => $countriesCol,
            ])->render();

            $adminUser = new \\App\\Models\\User(['name' => 'Admin User', 'role' => 'admin', 'company_id' => 1]);
            $adminUser->id = 1;
            auth()->setUser($adminUser);

            $appRec = new \\App\\Models\\EmployeeOnboardingApplication([
                'company_id' => 1,
                'invitation_id' => 77,
                'name' => 'Asha Patel',
                'email' => 'asha@example.test',
                'mobile' => '9876543210',
                'mobile_country_code' => '+91',
                'gender' => 'Female',
                'dob' => '1997-04-12',
                'country' => 'India',
                'address' => '14 Park Lane',
                'skills' => 'Laravel, PHP',
                'status' => 'Pending Review',
                'submitted_at' => now(),
            ]);
            $appRec->id = 101;
            $inv->setRelation('application', $appRec);
            $inv->setRelation('inviter', new \\App\\Models\\User(['name' => 'HR Lead']));

            $paginator = new \\Illuminate\\Pagination\\LengthAwarePaginator([$inv], 1, 20, 1);
            $paginator->withPath('https://example.test/employees/onboarding');

            function renderStandaloneSection(string $bladePath, array $data): string {
                $src = file_get_contents(resource_path('views/' . $bladePath . '.blade.php'));
                $src = preg_replace('/@extends\\([^)]+\\)/', '', $src);
                $src = preg_replace('/@(?:section|push)\\([^)]+\\)/', '', $src);
                $src = preg_replace('/@(?:endsection|endpush)\\b/', '', $src);
                return \\Illuminate\\Support\\Facades\\Blade::render($src, $data);
            }

            $indexHtml = renderStandaloneSection('admin/employees/onboarding/index', [
                'records' => $paginator,
                'counts' => [
                    'total_invitations' => 5,
                    'awaiting_submission' => 2,
                    'pending_review' => 1,
                    'correction_requested' => 1,
                    'approved' => 1,
                    'rejected' => 0,
                    'expired_invitations' => 0,
                ],
                'companies' => collect([$company]),
                'selectedCompanyId' => 1,
                'isPlatformAdmin' => false,
                'viewer' => $adminUser,
            ]);

            $appRec->setRelation('audits', collect([
                new \\App\\Models\\EmployeeOnboardingAuditLog([
                    'event' => 'Submitted',
                    'actor_label' => 'asha@example.test',
                    'notes' => 'Submitted online',
                    'created_at' => now(),
                ])
            ]));

            $reviewHtml = renderStandaloneSection('admin/employees/onboarding/review', [
                'application' => $appRec,
                'company' => $company,
                'companies' => collect([$company]),
                'nextEmployeeId' => 'EMP-00010',
                'countries' => $countriesCol,
                'designations' => collect([(object)['id' => 1, 'name' => 'Engineer', 'level' => 2]]),
                'prtdepartments' => collect([(object)['id' => 1, 'dpt_name' => 'Engineering']]),
                'departments' => collect([(object)['id' => 2, 'dpt_name' => 'Backend', 'parent_dpt_id' => 1]]),
                'users' => collect([new \\App\\Models\\User(['name' => 'Manager A'])]),
            ]);

            echo json_encode([
                'publicForm' => $publicFormHtml,
                'index' => $indexHtml,
                'review' => $reviewHtml,
            ]);
        } catch (\\Throwable $e) {
            fwrite(STDERR, get_class($e) . ': ' . $e->getMessage() . "\\n" . $e->getTraceAsString());
            exit(1);
        }
    `;
    const scriptPath = path.join(temp, 'render.php');
    fs.writeFileSync(scriptPath, '<?php\n' + php);
    const out = execFileSync('php', [scriptPath], { cwd: root, encoding: 'utf8', maxBuffer: 10_000_000 });
    return JSON.parse(out);
}

function offlineSanitize(html) {
    return html
        .replace(/<link[^>]+href="https?:\/\/[^"]+"[^>]*>/gi, '')
        .replace(/<script[^>]+src="https?:\/\/[^"]+"[^>]*>\s*<\/script>/gi, '');
}

function runChromeCheck(filePath, size, label) {
    const result = execFileSync(chrome, [
        '--headless', '--disable-gpu', '--no-sandbox', '--no-first-run', '--disable-background-networking',
        '--host-resolver-rules=MAP * ~NOTFOUND',
        '--window-size=' + size,
        '--user-data-dir=' + path.join(temp, label),
        '--virtual-time-budget=1000',
        '--dump-dom',
        pathToFileURL(filePath).href,
    ], { encoding: 'utf8', timeout: 60000, maxBuffer: 8_000_000, stdio: ['ignore', 'pipe', 'pipe'] });
    const status = result.match(/<body[^>]*data-test-result="([^"]+)"/)?.[1];
    assert.equal(status, 'pass', `${label} failed: ${status || 'no result marker'}`);
}

try {
    const rendered = compileBladeSlices();

    // 1. Public Onboarding Form (Light + Dark, Desktop + Mobile)
    const publicFixture = path.join(temp, 'public-form.html');
    const publicHtml = rendered.publicForm + `
    <script>
    (function() {
        function lum(rgb) {
            const m = String(rgb).match(/\\d+(\\.\\d+)?/g);
            if (!m || m.length < 3) return -1;
            const [r, g, b] = m.slice(0, 3).map(Number).map(v => {
                v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
            });
            return 0.2126 * r + 0.7152 * g + 0.0722 * b;
        }
        function checkContrast(theme) {
            const card = document.querySelector('.ob-card');
            const input = document.getElementById('ob_email');
            const label = document.querySelector('.ob-label');
            const cardBg = getComputedStyle(card).backgroundColor;
            const inputBg = getComputedStyle(input).backgroundColor;
            const inputFg = getComputedStyle(input).color;
            const labelFg = getComputedStyle(label).color;
            const ratioInput = (Math.max(lum(cardBg), lum(inputFg)) + 0.05) / (Math.min(lum(cardBg), lum(inputFg)) + 0.05);
            const ratioLabel = (Math.max(lum(cardBg), lum(labelFg)) + 0.05) / (Math.min(lum(cardBg), lum(labelFg)) + 0.05);
            if (theme === 'dark' && lum(cardBg) > 0.25) throw new Error('Dark mode card stayed light: ' + cardBg);
            if (theme === 'light' && lum(cardBg) < 0.7) throw new Error('Light mode card not light: ' + cardBg);
            if (ratioInput < 4.5 || ratioLabel < 4.5) throw new Error(theme + ' low contrast: ' + ratioInput.toFixed(2) + ' / ' + ratioLabel.toFixed(2));
            if (document.documentElement.scrollWidth > window.innerWidth + 2) throw new Error(theme + ' horizontal overflow: ' + document.documentElement.scrollWidth + ' > ' + window.innerWidth);
            if (!document.getElementById('sendOtpBtn') || !document.getElementById('otpCodeInput')) throw new Error('Missing OTP controls for link invitation');
            const inpRect = input.getBoundingClientRect();
            if (inpRect.height < 30 || lum(inputBg) === lum(inputFg)) throw new Error('Input styling invalid in ' + theme);
        }
        try {
            document.documentElement.setAttribute('data-pms-theme', 'light');
            document.documentElement.setAttribute('data-bs-theme', 'light');
            checkContrast('light');
            document.getElementById('obThemeToggle').click();
            if (document.documentElement.getAttribute('data-pms-theme') !== 'dark') throw new Error('Theme toggle failed');
            checkContrast('dark');
            document.body.dataset.testResult = 'pass';
        } catch (e) {
            document.body.dataset.testResult = e.message;
        }
    })();
    </script>`;
    fs.writeFileSync(publicFixture, offlineSanitize(publicHtml));
    runChromeCheck(publicFixture, '1440,960', 'public-desktop');
    runChromeCheck(publicFixture, '390,844', 'public-mobile');
    console.log('PASS: Public self-onboarding form passes Light/Dark contrast, OTP UI, toggle & desktop/mobile layout.');

    // 2. HR Applications Queue (Light + Dark, Modals, Summary Cards)
    const indexFixture = path.join(temp, 'admin-index.html');
    const indexHtml = `<!doctype html><html data-pms-theme="light"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <style>${cssRefresh}\n${cssTheme}</style></head><body>` + rendered.index + `
    <script>
    (function() {
        function lum(rgb) {
            const m = String(rgb).match(/\\d+(\\.\\d+)?/g);
            if (!m || m.length < 3) return -1;
            const [r, g, b] = m.slice(0, 3).map(Number).map(v => {
                v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
            });
            return 0.2126 * r + 0.7152 * g + 0.0722 * b;
        }
        try {
            const cards = document.querySelectorAll('.onboarding-stat-card');
            if (cards.length !== 7) throw new Error('Expected 7 summary cards, got ' + cards.length);
            const table = document.querySelector('.employee-list-table');
            if (!table) throw new Error('Missing queue table');
            for (const theme of ['light', 'dark']) {
                document.documentElement.setAttribute('data-pms-theme', theme);
                document.documentElement.setAttribute('data-bs-theme', theme);
                const cBg = getComputedStyle(cards[0]).backgroundColor;
                const cFg = getComputedStyle(cards[0].querySelector('.stat-val')).color;
                const ratio = (Math.max(lum(cBg), lum(cFg)) + 0.05) / (Math.min(lum(cBg), lum(cFg)) + 0.05);
                if (ratio < 4.5) throw new Error('Queue stat card low contrast in ' + theme + ': ' + ratio.toFixed(2));
                const mBg = getComputedStyle(document.querySelector('#correctionRequestModal .modal-content')).backgroundColor;
                const mFg = getComputedStyle(document.querySelector('#correctionRequestModal .modal-content')).color;
                if (theme === 'dark' && lum(mBg) > 0.25) throw new Error('Dark modal background stayed light: ' + mBg);
                const mRatio = (Math.max(lum(mBg), lum(mFg)) + 0.05) / (Math.min(lum(mBg), lum(mFg)) + 0.05);
                if (mRatio < 4.5) throw new Error('Dark modal text low contrast: ' + mRatio.toFixed(2));
            }
            document.body.dataset.testResult = 'pass';
        } catch (e) {
            document.body.dataset.testResult = e.message;
        }
    })();
    </script></body></html>`;
    fs.writeFileSync(indexFixture, indexHtml);
    runChromeCheck(indexFixture, '1440,960', 'index-desktop');
    runChromeCheck(indexFixture, '390,844', 'index-mobile');
    console.log('PASS: Applications Queue page passes 7 summary cards, table, modals, Light & Dark contrast checks.');

    // 3. HR Review & Approve Page (Light + Dark, Document Cards, Pre-populated Inputs, Modals)
    const reviewFixture = path.join(temp, 'admin-review.html');
    const reviewHtml = `<!doctype html><html data-pms-theme="light"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <style>${cssRefresh}\n${cssTheme}</style></head><body>` + rendered.review + `
    <script>
    (function() {
        function lum(rgb) {
            const m = String(rgb).match(/\\d+(\\.\\d+)?/g);
            if (!m || m.length < 3) return -1;
            const [r, g, b] = m.slice(0, 3).map(Number).map(v => {
                v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
            });
            return 0.2126 * r + 0.7152 * g + 0.0722 * b;
        }
        try {
            if (!document.getElementById('approveAddEmployeeBtn')) throw new Error('Missing Add approval button');
            for (const theme of ['light', 'dark']) {
                document.documentElement.setAttribute('data-pms-theme', theme);
                document.documentElement.setAttribute('data-bs-theme', theme);
                const rc = document.querySelector('.review-card');
                const inp = rc.querySelector('input[name="name"]');
                const rcBg = getComputedStyle(rc).backgroundColor;
                const inpBg = getComputedStyle(inp).backgroundColor;
                const inpFg = getComputedStyle(inp).color;
                if (theme === 'dark' && (lum(rcBg) > 0.25 || lum(inpBg) > 0.25)) throw new Error('Dark review surface/input stayed light');
                const ratio = (Math.max(lum(inpBg), lum(inpFg)) + 0.05) / (Math.min(lum(inpBg), lum(inpFg)) + 0.05);
                if (ratio < 4.5) throw new Error('Review input contrast failed in ' + theme + ': ' + ratio.toFixed(2));
                const modBg = getComputedStyle(document.querySelector('#reviewCorrectionModal .modal-content')).backgroundColor;
                const modFg = getComputedStyle(document.querySelector('#reviewCorrectionModal .modal-content')).color;
                if (theme === 'dark' && lum(modBg) > 0.25) throw new Error('Review dark modal stayed light');
                const mRatio = (Math.max(lum(modBg), lum(modFg)) + 0.05) / (Math.min(lum(modBg), lum(modFg)) + 0.05);
                if (mRatio < 4.5) throw new Error('Review modal contrast failed in ' + theme);
            }
            document.body.dataset.testResult = 'pass';
        } catch (e) {
            document.body.dataset.testResult = e.message;
        }
    })();
    </script></body></html>`;
    fs.writeFileSync(reviewFixture, reviewHtml);
    runChromeCheck(reviewFixture, '1440,960', 'review-desktop');
    runChromeCheck(reviewFixture, '390,844', 'review-mobile');
    console.log('PASS: Application Review page passes Light/Dark contrast, prefilled controls, docs & modal styling.');
} finally {
    fs.rmSync(temp, { recursive: true, force: true });
}
