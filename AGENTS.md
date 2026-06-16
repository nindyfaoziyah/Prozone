# Prozone — Agent Memory

## Goal
Beautify and fix all courses menu pages (courses.php, course.php, lesson.php) with light theme, proper dashboard layout, and polished visuals.

Fix all PHP fatal errors and broken file paths in the Prozone student dashboard and student pages after moving files into the `student/` folder.

## Progress

### Done
- **learning-path.php** — Created `student/playground.php` as coding playground:
  - Three-tab editor (HTML/CSS/JS) with monospace dark-themed textareas
  - Live preview via iframe srcdoc, auto-updates on input
  - Requirements/validation system: quest-specific criteria shown in side panel ✓/✕
  - Validation checks CSS, HTML, JS for required patterns (e.g. class selectors, color, flexbox properties)
  - Submit button validates all requirements before showing success overlay with XP reward
  - Code auto-saved to localStorage per quest; cleared on successful submit
  - Result overlay with "Lanjut Belajar" and "Kembali ke Peta" buttons
- **learning-path.php** — Quest items made clickable: clicking any quest opens material viewer at that specific slide index
- **learning-path.php** — Updated `openMateri()` to accept optional `startIdx` parameter; defaults to 0
- **learning-path.php** — Button text logic finalized: "📖 Mulai Belajar" (new), "🚀 Lanjutkan Belajar" (in-progress), "📖 Selesaikan Quest" (completed)
- **learning-path.php** — Material viewer "Mulai Praktik Coding" button now navigates to `playground.php` with `course_id`, `level`, `quest`, `skill`, `xp` params
- **lesson.php** — Fixed layout overflow (3-panel practice mode was overflowing page-wrapper because `height: calc(100vh - 120px)` didn't account for dashboard grid overhead):
  - Overrode `.lesson-wrapper` height to `calc(100vh - 280px)` (accounts for body grid padding/gap/header + page-wrapper padding + lesson-header)
  - Only applies when `body.dashboard-layout` is present
  - Added `margin-bottom: 12px` to `.lesson-header` for visual spacing
  - Added `max-width: 1600px` to `.dashboard-content` so 3-panel layout has room
- **lesson-enhanced.css** — Instructions panel scroll fix:
  - `.instructions-panel`: `overflow-y: auto` → `overflow: hidden` (defers scroll to content)
  - Added `.instructions-content`: `flex: 1; min-height: 0; overflow-y: auto` (content scrolls independently)
  - Added `.instructions-footer`: `flex-shrink: 0` + top border separator (stays at bottom)
  - Header/footer stay fixed while middle content scrolls → editor/preview panels get full height
- **courses.php** — Course catalog page polished:
  - Gradient top border (`::before`) on card hover.
  - Randomized gradient thumbnails per course (10 preset gradients via `crc32` hash).
  - Student count added to meta (`users` icon from `total_students`).
  - Card reveal animation with `IntersectionObserver`.
- **course.php** — Converted to dashboard layout:
  - Uses `includes/head.php` with `$page_css` + `$page_title`.
  - Body class: `dashboard-layout` + theme class.
  - Removed `margin-top: 70px` from header (handled by sidebar layout).
  - Added `lesson-count-badge` showing lesson count.
  - Completed lessons get green left border + `rgba(34,197,94,0.03)` bg.
  - Enroll section now has icon + redesigned `btn-enroll-cta` with larger padding/shadow.
  - Added `enroll-icon` container for visual cue.
- **course-detail.css** — Light-theme compatible via CSS variable fallbacks.
- **course-detail.css** — Added `.lesson-count-badge`, `.btn-enroll-cta`, `.enroll-icon`, `.lesson-item.completed` bg.
- **lesson.php** — Full light theme conversion (from previous sessions):
  - CSS variables changed from dark to light values.
  - Text colors darkened for readability.
  - Added `dashboard-layout` class + dashboard CSS files.
  - Fixed quiz inline styles for light theme.
  - Fixed UTF-8 mojibake (→, ←, •, ✓ double-encoding).
- **lesson.php** — Quiz mode beautification:
  - Removed all inline styles (extracted to CSS classes with light theme variables)
  - Redesigned quiz container, question cards, option labels with clean light theme
  - Custom radio buttons with purple accent (`var(--primary-purple)`)
  - Option hover effect: translateX + purple border glow
  - Clean result display with score/message styling
  - Submit button uses dedicated `btn-quiz-submit` class
- **lesson.php** — Theory mode polish:
  - Changed teal accent (`#14B8A6`, `rgba(20,184,166,…)`) to purple accent (`var(--primary-purple)`, `rgba(14,165,233,…)`) on nav buttons, shadows
  - `.btn-finish` gets green gradient (distinct from nav buttons)
  - `.theory-navigation` converted from dark gradient to light theme bg
  - `.theory-nav-btn` uses purple gradient; `.secondary` variant uses light card style
- **lesson.php** — General polish:
  - Scrollbar uses purple gradient + light theme colors (was dark/teal)
  - Selection highlight uses purple (was teal)
  - Body background gradients updated to purple/indigo tones (was teal/cyan)
  - Progress bar shadow softened for light theme
- **quest.php** — Light theme + CodeMirror workspace (from previous sessions).

### Done (New — Quest Progress System)
- **user_quest_progress** — New DB table tracking per-quest completion per user:
  - `user_id`, `level_id`, `quest_idx`, `course_id`, `status` (not_started/in_progress/completed)
- **api/complete-quest.php** — API endpoint for saving quest completion:
  - Upserts `user_quest_progress` with status='completed'
  - Awards XP to `users.total_xp`
  - Checks if entire level is completed (all quests done)
  - Updates `enrollments` if level is 100%
  - Updates `leaderboard_solo`
- **learning-path.php** — Dynamic progress from DB:
  - PHP queries `user_quest_progress` to count completed quests per level
  - Progress = `(completed_quests / total_quests) * 100`
  - Level status determined sequentially: all 100% → 'completed', first non-100% → 'in-progress', rest → 'locked'
  - Progress circle colors: 0% gray, 25% light blue, 50% purple, 75% cyan, 100% green glow
  - Node progress bar color changes per percentage bracket
  - Detail panel shows quest completion (✓/📖/🔒) per quest item
  - "SELESAI" badge on completed levels, "🔒 Terkunci" on locked, "📖 Mulai Belajar"/"🚀 Lanjutkan Belajar" on active
  - Button disabled with unlock message for locked levels
- **course-viewer.php** — Marks quest as 'in_progress' when user views material (ON DUPLICATE KEY preserves existing status)
- **playground.php** — Calls `api/complete-quest.php` on successful submit via AJAX (POST with JSON payload: level_id, quest_idx, course_id, xp, total_quests)
- Auto-unlock: Once all quests in a level reach 100%, the next level automatically becomes 'in-progress' (sequential unlock)

## Progress Circle Colors
| Progress | Color | CSS Class |
|----------|-------|-----------|
| 0% | Gray (#CBD5E1) | pg-0 |
| 25% | Light Blue (#93C5FD) | pg-25 |
| 50% | Purple (#6366F1) | pg-50 |
| 75% | Cyan (#22D3EE) | pg-75 |
| 100% | Green glow (#22C55E + glow) | pg-100 |

## How Progress Saves
1. User opens material → `course-viewer.php` → INSERT/UPDATE `user_quest_progress` status='in_progress'
2. User finishes slides, clicks "Mulai Praktik Coding" → goes to `playground.php`
3. User writes code, clicks Submit → validates requirements → calls `api/complete-quest.php` → status='completed' + XP awarded
4. Next page load of learning-path.php → reads from `user_quest_progress` → displays real progress

## Key Decisions
- Separate table `user_quest_progress` used (not `user_progress`) because quests don't have lesson_ids
- XP awarded once per quest (checked via existing status='completed' before awarding)
- Level unlock is sequential: level N must be 100% before level N+1 becomes accessible
- `enrollments` table updated only when a level reaches 100% (keeps enrollments in sync)

## Key Decisions (Path Fixes)
- Files moved into `student/` need `../` prefix for all requires/includes to root-level dirs (`config/`, `models/`, `includes/`, `assets/`, `api/`).
- Same-directory sibling references (e.g., `include 'navbar.php'` from `dashboard.php`) are valid without `../`.
- Used `replaceAll` for bulk patterns (`file_exists('assets/')` → `file_exists('../assets/')`).

## Relevant Files (Path Fixes)
- `student/*.php` (all 14 files) — require/include paths, asset paths, fetch URLs, and link hrefs corrected.
- Fixed 60+ broken paths across all 14 files.
- Session 1 fixes: `dashboard.php` SQL queries, `User.php::readOne()`, RPG system, sidebar/footer/profile links.
- Session 2 fixes: `achievements.php`, `certificates.php`, `characters.php`, `clan.php`, `courses.php`, `ai-mentor.php`, `leaderboard.php`, `multiplayer.php`, `dashboard.php` (admin content added later), `learning-path.php`.

## Key Decisions
- `lesson.php` practice mode: 3-panel layout uses CSS grid internally, but outer sizing controlled by flex chain from dashboard grid.
- `page-wrapper` and `dashboard-content` are flex columns so the lesson-wrapper can `flex: 1` to fill remaining height (fixes overflow).
- `lesson.php` keeps dark editor/preview panels (IDE-like), rest of page is light via CSS variable overrides.
- All dashboard pages follow same flex/grid hybrid approach: CSS Grid for outer page layout (`sidebar | header + main`), Flex for inner content stacking.
- Text colors use high-contrast light values: `--text-primary: #0F172A`, `--text-secondary: #1E293B`, `--text-muted: #475569`.

## Course Detail (New)
- `course.php` never existed at root → all links in `courses.php`, `dashboard.php`, `lesson.php`, `courses-public.php` to `../course.php?id=` were broken
- Fixed `student/courses.php` — changed all `../course.php?id=` → `course-detail.php?id=` (same-directory reference)
- Fixed `student/dashboard.php` — same fix for 2 links
- Created `student/course-detail.php` — course detail page with lesson material viewer:
  - Fetches course by ID, fetches lessons ordered by `urutan`
  - Slide viewer: shows `konten` HTML content per lesson with prev/next navigation
  - Lesson list sidebar with status (active/completed/in-progress)
  - Enroll CTA for non-enrolled users, progress bar for enrolled
  - Marks lesson as `in_progress` in `user_progress` on view via API
  - Keyboard nav (ArrowLeft/Right), lesson list click to jump
  - Redirects to `lesson.php?course_id=X&lesson_id=Y` for practice/quiz lessons
- Created `api/enroll.php` — enrolls user in course (inserts enrollments, increments total_students)
- Created `api/track-lesson-progress.php` — upserts user_progress with ON DUPLICATE KEY, preserves 'completed' status

## Relevant Files (Course Detail)
- `student/courses.php` — Link fixes
- `student/dashboard.php` — Link fixes
- `student/course-detail.php` — New course detail + lesson material viewer
- `api/enroll.php` — New enrollment API
- `api/track-lesson-progress.php` — New lesson progress tracking API

## Database Seeder
- `database/seeder.php` — Comprehensive seeder (run with `php database/seeder.php`, add `RESET=1` to truncate first)
- Creates 13 courses across all 11 categories, 5 lessons each (3 theory + 1 practice + 1 quiz) = 65 lessons total
- Each lesson has rich HTML konten: theory lessons have paragraphs + code examples, practice lessons have numbered instructions + hints, quiz lessons have MCQ with marked answer
- Covers: HTML & CSS (2 courses), JS (2), PHP (1), Python (1), SQL/DB (1), React (1), Laravel (1), REST API (1), Data Science (1), Java (1), C++ (1)
- Creates `user_quest_progress` table (was missing — referenced by PHP code but no CREATE TABLE existed)
- Fixes `Lesson::create()` bug: was missing `bindParam()` for `:hints` and `:xp_reward` (SQL had them in SET clause but never bound)
- Enrolls student1 in 4 demo courses (HTML & CSS Fundamentals, JS Dasar, PHP Dasar, Python untuk Pemula) with HTML & CSS at 40% progress
- Updates `total_students` and `total_lessons` counts on courses
- Quiz konten includes correct answer marker (✅) for reference — visible in slide viewer
- Course-detail viewer renders slide-number/type/title from PHP template; konten should only contain body content (paragraphs, code, lists)

## Bug Fix Session (June 2026)
### Critical Fixes
- **student/course-detail.php:417** — Fixed `array_map()` paren placement: moved `)` after `$theory_lessons` so it becomes the second arg to `array_map` instead of a flags param to `json_encode`. In PHP 8.1, `array_map(callback)` with only 1 arg throws `ArgumentCountError`, killing the page mid-render and preventing the game IIFE from running (theory blank, next button broken).
- **student/course-detail.php** — Fixed `array_map()` paren placement: moved `)` after `$theory_lessons` so it becomes the second arg to `array_map` instead of a flags param to `json_encode`. In PHP 8.1, `array_map(callback)` with only 1 arg throws `ArgumentCountError`, killing the page mid-render and preventing the game IIFE from running (theory blank, next button broken).
- **student/course-detail.php** — Added flex layout chain (`display:flex;flex-direction:column;flex:1;min-height:0`) to `.game-wrapper`, `.game-main`, `.game-phase.active`, `.materi-card` (plus `.quiz-card`) so `.materi-body`/`.quiz-body` scrolls properly when content overflows.
- **student/course-detail.php** — Changed `.game-wrapper` from `min-height:100vh;overflow:hidden` to `height:100vh` (fixed viewport height). The old `min-height` let the wrapper grow with content, defeating flex constraints — `height:100vh` keeps it pinned to viewport, so `flex:1;min-height:0` children properly shrink and `overflow-y:auto` on scrollable areas actually triggers.
- **student/course-detail.php** — Added scrollbar visibility for `.game-wrapper *` (overrides `dashboard-override.css`'s global `*{scrollbar-width:none}` which hid ALL scrollbars). Custom purple-theme thin scrollbars now visible inside the game.
- **config/config.php** — Fixed login redirect path from `student/` subdirectory: changed `header('Location: login.php')` → `BASE_URL . 'login.php'` (was resolving to `student/login.php` which doesn't exist)
- **student/course-detail.php:1075** — Fixed `theOryData` typo → `theoryData` (caused JS ReferenceError)
- **database/seed-quiz-v2.php** — Fixed error-detect `\n` bug: changed single-quoted strings to double-quoted so `\n` becomes actual newlines (was rendering entire code as one line)
- **student/course-detail.php** — Fixed error-detect validation: now checks exact bug line indices (`q.bugs.every(b => ans.indexOf(b.line) != -1)`) instead of just count (`ans.length >= q.bugs.length`)
- **student/course-detail.php** — Error-detect now shows correct bug lines + fix hints after submission with green/red visual feedback
- **student/course-detail.php** — Replaced mission validation if/else if chain with independent if statements (checks ALL matching keyword categories per requirement, not just first match)
- **student/course-detail.php** — Added 20+ language-specific validation patterns for JS, PHP, Python, SQL, React, Laravel, CSS, Data Science (was falling back to `code.length > 50` for 10/13 courses)
- **student/course-detail.php** — Victory screen XP now shows `cr.xp_awarded` only (was compounding with `S.totalXp`, showing inflated value)
- **student/course-detail.php** — Victory screen lesson count now dynamic via `totalLessons` JS variable (was hardcoded `'5/5'`)
- **student/course-detail.php** — Drag-drop and code-arrange now show correct order + disable dragging on submission
- **api/complete-course.php** — `$coins_reward` cast to `(int)` (was float from division)
- **api/complete-course.php** — Added DB transaction with `FOR UPDATE` to prevent race conditions on double XP/coins award
- **api/complete-phase.php** — Added DB transaction with `FOR UPDATE` to prevent race condition on double XP award
- **student/course-detail.php** — Added XHR `onreadystatechange` handler for quiz phase API call (was silently ignoring server response)
- **models/Course.php:115** — Fixed `$row['instructor_id']` → `$row['admin_id']` (column name was wrong, causing `Undefined array key` warning)
- **student/course-detail.php** — Full light mode conversion: CSS variables changed from dark (`#0F172A`, `#1E293B`, `#334155`) to light (`#F8FAFC`, `#FFFFFF`, `#E2E8F0`), all card/header/zone backgrounds and text colors converted to light theme, editor/console kept dark (IDE-like)
