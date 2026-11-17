# Change.md — Frontend Redesign, Branding & UI Polish for `delox`

## 1. Objective
Overhaul the visual identity and user interface of **delox** while **preserving 100% of existing backend logic, database schema, Zustand state management, `@dnd-kit` drag-and-drop behavior, and Laravel Reverb real-time synchronization**.

The current default Breeze/Laravel styling must be completely replaced with a refined, calm, editorial SaaS aesthetic built around the **Manrope** typeface, the custom **`DELOX.png`** logo, and an earthy-pastel color palette (`#efefef`, `#d8b9a9`, `#9dbeb5`).

---

## 2. Branding, App Name & Logo Integration
1. **Application Name (`delox`):**
   * Update `APP_NAME=delox` in `.env` (and `.env.example`).
   * Update the Inertia title resolver in `resources/js/app.tsx` so browser tabs display `<Page Title> — delox` (and `delox` when no page title is set).
   * Remove all remaining references to "Laravel" in UI copy, footers, headers, and meta tags.
2. **Logo Asset (`resources/image/DELOX.png`):**
   * Use the existing logo located at `resources/image/DELOX.png`.
   * Update `resources/js/Components/ApplicationLogo.tsx` to import `resources/image/DELOX.png` directly via Vite (`import deloxLogo from '../../image/DELOX.png';`) so Vite bundles and hashes it reliably in both `npm run dev` and `npm run build`.
   * Render `<img src={deloxLogo} alt="delox" ... />` with clean sizing (`object-contain`) across the top navigation bar, auth screens, and entry screen.
   * Update `resources/views/app.blade.php` to use `{{ Vite::asset('resources/image/DELOX.png') }}` as the page favicon (`<link rel="icon" type="image/png" href="...">`).

---

## 3. Typography — Manrope
1. **Load Font:**
   * In `resources/views/app.blade.php`, remove the `Figtree` font link and load **Manrope** (weights `400`, `500`, `600`, `700`) from Bunny Fonts or Google Fonts (`https://fonts.bunny.net/css?family=manrope:400,500,600,700&display=swap`).
2. **Tailwind CSS v4 Configuration (`resources/css/app.css`):**
   * Remember: this project uses **Tailwind CSS v4** (`@import 'tailwindcss';`). **Do NOT create `tailwind.config.js` or `postcss.config.js`.**
   * Register `Manrope` as the default sans font inside `@theme` in `resources/css/app.css`:
```css
@theme {
  --font-sans: 'Manrope', ui-sans-serif, system-ui, sans-serif;
}
```
   * Apply `antialiased` rendering, smooth font feature settings, and `tabular-nums` for task keys (`DLX-1`), counters, and dates.

---

## 4. Color Palette & Design System
Build the entire UI around the user-mandated palette (`#efefef`, `#d8b9a9`, `#9dbeb5`) paired with high-contrast deep charcoal ink for effortless readability:

* **Primary Canvas Background (`#efefef`):**
  * Use `#efefef` (`--color-canvas: #efefef`) as the main application page background instead of stark `#ffffff` or cold gray.
* **Elevated Surfaces:**
  * Primary cards, task cards, modals, auth containers, and slide-over drawers: crisp `#ffffff` (`--color-surface: #ffffff`) with a subtle hairline border `#e2dfda` (`--color-border: #e2dfda`) and soft diffused elevation (`shadow-[0_2px_12px_rgba(31,36,33,0.04)]`).
  * Kanban columns & secondary containers: soft warm off-white `#f7f6f4` (`--color-surface-subtle: #f7f6f4`) with `#e5e2dc` borders so white task cards stand out naturally.
* **Primary Brand Accent — Muted Sage (`#9dbeb5`):**
  * Use `#9dbeb5` (`--color-sage: #9dbeb5`, hover `#89afa5`) for primary buttons, active sidebar/tab indicators, focus rings, online/sync badges, and drag-and-drop dropzone highlights.
  * Pair `#9dbeb5` backgrounds with deep charcoal text (`#1f2421`, `font-semibold`) to guarantee high contrast and visual elegance.
* **Secondary Brand Accent — Warm Terracotta Sand (`#d8b9a9`):**
  * Use `#d8b9a9` (`--color-sand: #d8b9a9`, hover `#cca794`) for secondary highlights, workspace/avatar badges, active filter pills, subtle decorative accents on auth/welcome screens, and medium/high priority accents.
* **Typography Ink Hierarchy:**
  * Primary headings & task titles: `#1f2421` (`--color-ink: #1f2421`).
  * Body copy: `#373d3a` (`--color-ink-body: #373d3a`).
  * Secondary metadata, timestamps, placeholders: `#6b726e` (`--color-ink-muted: #6b726e`).
* **Harmonized Task Priority Badges:**
  * `low`: Soft sage tint (`bg-[#9dbeb5]/25 text-[#25443c] border-[#9dbeb5]/50`)
  * `medium`: Warm neutral stone (`bg-[#efefef] text-[#373d3a] border-[#d6d3cd]`)
  * `high`: Warm sand-terracotta tint (`bg-[#d8b9a9]/35 text-[#5a3626] border-[#d8b9a9]/70`)
  * `urgent`: Muted earthy rust (`bg-[#c96a50]/15 text-[#8f3822] border-[#c96a50]/40`)

---

## 5. Screen-by-Screen Redesign Requirements

### A. Remove Default Laravel Welcome Page (`routes/web.php` & `Welcome.tsx`)
* Completely eliminate the default Laravel boilerplate page (all Laravel documentation, Laracasts, News, and default SVG illustrations).
* **Behavior for `/`:**
  * If the user is authenticated, redirect directly to `/dashboard` (the **delox** Workspace & Projects Hub).
  * If the user is a guest, render a minimalist, bespoke **delox** entry page (`resources/js/Pages/Welcome.tsx`) on `#efefef` featuring:
    * Top minimal bar with the `DELOX.png` logo and **Sign in** / **Get started** buttons.
    * Centered, high-craft hero section introducing **delox** as a calm, real-time workspace for focused teams, accented with subtle `#9dbeb5` and `#d8b9a9` visual elements, and a clean preview card mockup of a Kanban board.

### B. Authentication Pages (`GuestLayout.tsx`, `Login.tsx`, `Register.tsx`, `ForgotPassword.tsx`)
* Style `GuestLayout.tsx` with the `#efefef` canvas, subtle `#9dbeb5`/`#d8b9a9` ambient corner accents, centered `DELOX.png` logo, and a refined `#ffffff` card (`rounded-2xl`, `border-[#e2dfda]`, generous padding `p-8`).
* Style form inputs (`TextInput.tsx`) with clean `#faf9f7` / `#ffffff` backgrounds, `#dcd9d3` borders, and `#9dbeb5` focus rings (`focus:border-[#89afa5] focus:ring-[#9dbeb5]/40`).
* Style `PrimaryButton.tsx` with `#9dbeb5` background, `#1f2421` semibold text, `rounded-xl`, smooth hover transition (`hover:bg-[#89afa5]`), and subtle active press state.

### C. Authenticated Shell (`AuthenticatedLayout.tsx`)
* Replace the default Breeze top bar with a sleek, low-profile navigation header (`bg-[#ffffff]/90 backdrop-blur-md border-b border-[#e2dfda]`):
  * Left: `DELOX.png` logo, Workspace switcher dropdown pill (styled with subtle `#d8b9a9`/20 hover state), and breadcrumb navigation (`Workspace / Project`).
  * Right: Real-time connection status dot (`#9dbeb5` pulse when Reverb is connected), quick action button, and user profile menu with initials avatar tinted in `#d8b9a9` or `#9dbeb5`.

### D. Workspace & Projects Hub (`Dashboard.tsx`)
* Present a clean, well-proportioned overview on the `#efefef` canvas:
  * Header with greeting, current workspace title, member count/invite button (`#d8b9a9` accent), and **New Project** primary CTA (`#9dbeb5`).
  * Project cards (`bg-[#ffffff] rounded-2xl border border-[#e2dfda] hover:border-[#9dbeb5] transition-all`) displaying the project key badge (`DLX` in `#d8b9a9`/30), project title, description, task count, and member avatars.
  * Welcoming, illustrated/icon-driven empty state if a workspace has no projects yet.

### E. Kanban Board (`ProjectBoard.tsx`, `BoardColumn.tsx`, `TaskCard.tsx`) & Drawer (`TaskDetailDrawer.tsx`)
* **Project Header & Filter Bar:** Compact toolbar with project title, live search input, priority filter pills, assignee filter, and **+ Add Column** button.
* **Kanban Columns (`BoardColumn.tsx`):**
  * Fixed comfortable width (`w-80 shrink-0`), `bg-[#f7f6f4] rounded-2xl border border-[#e5e2dc]`, clean column header with task counter pill (`bg-[#efefef] text-[#5f6662]`), and subtle `+ Add task` trigger at the bottom.
* **Task Cards (`TaskCard.tsx`):**
  * Crisp `bg-[#ffffff] rounded-xl border border-[#e5e2dc] p-3.5 shadow-[0_1px_4px_rgba(31,36,33,0.03)] hover:border-[#9dbeb5] hover:shadow-[0_4px_12px_rgba(31,36,33,0.06)] transition-all`.
  * Display task key (`DLX-12` in `text-xs font-medium text-[#6b726e] tabular-nums`), concise title (`text-sm font-medium text-[#1f2421]`), priority badge, due date pill, comment counter, and assignee avatar.
  * When actively dragged via `@dnd-kit`, apply `ring-2 ring-[#9dbeb5] shadow-xl rotate-[1deg] opacity-95`.
* **Task Detail Drawer (`TaskDetailDrawer.tsx`):**
  * Smooth slide-over drawer (`bg-[#ffffff] border-l border-[#e2dfda] shadow-2xl`) with clean metadata grid (Status/Column, Priority, Assignee, Due Date), spacious title & description editors, and a refined activity/comments timeline at the bottom.
* **Custom Thin Scrollbars:**
  * Add lightweight, unobtrusive custom scrollbar styles in `resources/css/app.css` matching `#d6d3cd` / `#9dbeb5` so horizontal board scrolling looks polished on Windows and Linux browsers.

---

## 6. Verification Checklist
After applying all changes:
1. Run `sail npm run build` and ensure TypeScript (`tsc`) and Vite complete with **0 errors**.
2. Verify that `resources/image/DELOX.png` renders properly on `/`, `/login`, `/register`, `/dashboard`, and the Kanban board header.
3. Verify that **Manrope** is applied globally across headings, inputs, buttons, and board cards.
4. Confirm that all interactive features (creating workspaces/projects, creating/editing/deleting tasks, drag-and-drop reordering, filtering, comments, and Reverb WebSocket updates) work without regression.