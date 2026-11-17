# GitHub Copilot Instructions — Delox

You are an expert Senior Full-Stack Engineer building **Delox**, a real-time collaborative task tracker (Linear/Trello hybrid). Always treat `Project.md` in the repository root as the authoritative architectural specification.

---

## 1. Environment & CLI Execution Rules
* This project runs inside **Docker via Laravel Sail** in WSL 2.
* Whenever suggesting or running terminal commands, **always** use `./vendor/bin/sail` (or `sail`):
  * PHP/Artisan: `sail artisan ...`
  * Composer: `sail composer ...`
  * Node/NPM: `sail npm ...`
  * Tests: `sail artisan test`
* Never suggest `php artisan` or bare `npm` directly on the host machine.

---

## 2. Backend Coding Standards (Laravel 13 + PHP 8.5)
* **Strict Typing:** Add `declare(strict_types=1);` at the top of every new PHP file. Use explicit parameter types, return types, and typed class properties.
* **Validation & Auth:**
  * Always validate incoming requests using dedicated `FormRequest` classes (`app/Http/Requests/...`).
  * Always authorize actions via Laravel Policies (`$this->authorize(...)` or route middleware `can:...`). Never leave project/task endpoints unprotected. * **Controllers & Responses:** * Keep controllers thin; extract domain logic into `app/Services/...` or `app/Actions/...`. * Use `Inertia::render(...)` for full page navigations (e.g., opening a project board). * Return clean JSON responses (`JsonResponse`) for high-frequency background mutations (e.g., moving a task/column via drag-and-drop, inline task updates, posting comments) so Inertia does not re-render the entire page tree during drag operations. * **Fractional Indexing:** * Never use integer `order` columns. Always compute positions via `App\Services\FractionalIndexingService`. * Always query columns and tasks ordered by `->orderBy('position')`. * **Real-Time Events:** * Broadcasting events must implement `Illuminate\Contracts\Broadcasting\ShouldBroadcastNow` (or `ShouldBroadcast` with Redis queue) on `PrivateChannel('project.' . $projectId)`.
  * Always dispatch with `broadcast(new TaskMoved(...))->toOthers()`.

---

## 3. Frontend Coding Standards (React + TypeScript + Tailwind CSS v4)
* **Tailwind CSS v4 Rules (CRITICAL):**
  * This project uses **Tailwind CSS v4** via `@tailwindcss/vite` and `@import 'tailwindcss';` in `resources/css/app.css`.
  * **Do NOT** create or reference `tailwind.config.js` or `postcss.config.js`. Any custom theme tokens must be defined via `@theme` inside `resources/css/app.css`.
* **TypeScript:**
  * Never use `any`. Define strict interfaces for `User`, `Workspace`, `Project`, `Column`, `Task`, and `TaskComment` in `resources/js/types/delox.ts` and extend `PageProps` in `resources/js/types/index.d.ts`.
* **State & Drag-and-Drop:**
  * Manage active Kanban board state inside a dedicated Zustand store (`resources/js/stores/useBoardStore.ts`).
  * Implement **Optimistic UI**: update Zustand state immediately on drag end, persist via `window.axios.patch(...)`, and revert state on catch/error.
  * Use `@dnd-kit/core` and `@dnd-kit/sortable` with `PointerSensor` (configured with `activationConstraint: { distance: 5 }` so clicking a card opens details instead of triggering a drag).
* **UI/UX Aesthetic:**
  * Follow a clean, crisp, Linear-inspired SaaS aesthetic: subtle borders (`border-slate-200` / `dark:border-slate-800`), compact typography, keyboard-friendly modals/drawers, clear priority indicators, and responsive board scrolling.

---

## 4. Immediate Implementation Tasks (What to Build Next)
Execute these tasks sequentially when asked to build the project:

1. **Task 1 — Database Schema & Models (Phase 1):**
   * Generate migrations and models for `Workspace`, `Project`, `Column`, `Task`, and `TaskComment` (plus `workspace_user` pivot) matching `Project.md`.
   * Add `workspaces()` and `ownedWorkspaces()` relationships to `App\Models\User`.
   * Auto-assign the next sequential `number` per project when creating a `Task` (`DLX-1`, `DLX-2`, etc.).
2. **Task 2 — Fractional Indexing & Seeders (Phase 1):**
   * Implement `App\Services\FractionalIndexingService` (`generateKeyBetween(?string $prev, ?string $next): string`) and comprehensive Pest/PHPUnit tests.
   * Create `DatabaseSeeder` with a realistic sample workspace, project, columns, and tasks, plus a listener/hook to create a default personal workspace upon user registration.
3. **Task 3 — Authorization & Workspace/Project Management (Phase 2):**
   * Create Policies (`WorkspacePolicy`, `ProjectPolicy`, `ColumnPolicy`, `TaskPolicy`).
   * Build `WorkspaceController` and `ProjectController` + React pages for switching workspaces, inviting members, and creating/opening projects.
4. **Task 4 — Kanban Board, Zustand & Drag-and-Drop (Phase 3):**
   * Build `ProjectBoard.tsx`, `BoardColumn.tsx`, `TaskCard.tsx`, and `useBoardStore.ts`.
   * Wire up `@dnd-kit` drag-and-drop for both columns and tasks with optimistic updates and backend `move` endpoints.
5. **Task 5 — Task Detail Drawer & Filters (Phase 4):**
   * Build `TaskDetailDrawer.tsx` for editing task fields, assignees, priorities, due dates, and comments, plus top-bar board filters.
6. **Task 6 — Real-Time Sync with Reverb (Phase 5):**
   * Configure `routes/channels.php`, create broadcast events (`TaskCreated`, `TaskUpdated`, `TaskMoved`, `TaskDeleted`, `ColumnCreated`, `ColumnMoved`), and connect Laravel Echo in `ProjectBoard.tsx`.