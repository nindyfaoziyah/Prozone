# Prozone — Agent Memory

## Goal
Beautify and fix all courses menu pages (courses.php, course.php, lesson.php) with light theme, proper dashboard layout, and polished visuals.

## Progress

### Done
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

### In Progress
- (none)

### Blocked
- (none)

## Key Decisions
- `lesson.php` practice mode: 3-panel layout uses CSS grid internally, but outer sizing controlled by flex chain from dashboard grid.
- `page-wrapper` and `dashboard-content` are flex columns so the lesson-wrapper can `flex: 1` to fill remaining height (fixes overflow).
- `lesson.php` keeps dark editor/preview panels (IDE-like), rest of page is light via CSS variable overrides.
- All dashboard pages follow same flex/grid hybrid approach: CSS Grid for outer page layout (`sidebar | header + main`), Flex for inner content stacking.
- Text colors use high-contrast light values: `--text-primary: #0F172A`, `--text-secondary: #1E293B`, `--text-muted: #475569`.
