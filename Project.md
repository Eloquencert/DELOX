# Delox — Real-Time Collaborative Project & Task Tracker

## 1. Project Overview
**Delox** is a modern, high-performance team collaboration and task management web application inspired by **Linear** and **Trello**. It allows teams to organize workspaces, manage projects, track tasks on an interactive Kanban board with instant drag-and-drop reordering, and collaborate in real time via WebSockets.

---

## 2. Tech Stack & Runtime Environment
* **Environment:** Docker (Laravel Sail) running inside WSL 2 (Ubuntu).
* **Backend:** PHP 8.5, Laravel 13, Laravel Octane (FrankenPHP), Laravel Reverb (WebSockets).
* **Database & Cache:** PostgreSQL (primary relational database), Redis (queues, broadcasting pub/sub, cache).
* **Frontend:** Inertia.js, React, TypeScript (strict mode), Tailwind CSS v4 (`@tailwindcss/vite`).
* **Frontend Libraries:**
  * `zustand` — client-side Kanban board state & optimistic UI updates.
  * `@dnd-kit/core`, `@dnd-kit/sortable`, `@dnd-kit/utilities` — accessible drag-and-drop for columns and tasks.
  * `lucide-react` — crisp UI iconography.
  * `axios` — silent background API calls (e.g., drag-and-drop persistence without full Inertia page reloads).

---

## 3. Core Architectural Principles

### A. Fractional Indexing (O(1) Board Reordering)
* Columns and tasks **must not** use sequential integer `order` columns (`1, 2, 3...`), which require updating multiple database rows on every drag-and-drop action.
* Instead, `columns.position` and `tasks.position` store **lexicographically sortable strings** (e.g., `"a0"`, `"a1"`, `"a2"`).
* When an item is moved between two neighbors (`prevPos` and `nextPos`), a dedicated `FractionalIndexingService` computes a midpoint string (e.g., between `"a0"` and `"a1"` -> `"a0V"`). Only **one** row is updated in PostgreSQL per drag event.

### B. Optimistic UI & Real-Time Synchronization
* **Optimistic Updates:** When a user drags a task or column, Zustand updates the UI immediately (`0ms` perceived latency), then sends an Axios `PATCH` request to the backend. If the request fails, Zustand rolls back to the previous snapshot and shows an error toast.
* **WebSockets (Laravel Reverb):** Backend mutations broadcast events (`TaskCreated`, `TaskUpdated`, `TaskMoved`, `TaskDeleted`, `ColumnCreated`, `ColumnMoved`, `ColumnDeleted`) on private project channels (`private-project.{projectId}`).
* Broadcasts **must** use `->toOthers()` so the user who triggered the optimistic update does not apply the event twice.

### C. Multi-Tenant Workspace RBAC
* Users belong to one or more **Workspaces** with roles: `owner`, `admin`, `member`.
* Every project belongs to a workspace. All data mutations are strictly guarded by Laravel Policies (`WorkspacePolicy`, `ProjectPolicy`, `ColumnPolicy`, `TaskPolicy`).

---

## 4. Database Schema (PostgreSQL)

1. **`users`** *(already exists via Breeze)*: `id`, `name`, `email`, `password`, `timestamps`.
2. **`workspaces`**:
   * `id` (PK)
   * `name` (string)
   * `slug` (string, unique)
   * `owner_id` (FK -> `users.id`, cascadeOnDelete)
   * `timestamps`
3. **`workspace_user`** (pivot):
   * `id` (PK)
   * `workspace_id` (FK -> `workspaces.id`, cascadeOnDelete)
   * `user_id` (FK -> `users.id`, cascadeOnDelete)
   * `role` (string: `owner` | `admin` | `member`, default `member`)
   * `timestamps`
   * Unique index on `['workspace_id', 'user_id']`
4. **`projects`**:
   * `id` (PK)
   * `workspace_id` (FK -> `workspaces.id`, cascadeOnDelete)
   * `name` (string)
   * `key` (string, max 6 chars, e.g., `DLX`, uppercase)
   * `description` (text, nullable)
   * `timestamps`
   * Unique index on `['workspace_id', 'key']`
5. **`columns`**:
   * `id` (PK)
   * `project_id` (FK -> `projects.id`, cascadeOnDelete)
   * `name` (string)
   * `position` (string, indexed)
   * `timestamps`
6. **`tasks`**:
   * `id` (PK)
   * `project_id` (FK -> `projects.id`, cascadeOnDelete)
   * `column_id` (FK -> `columns.id`, cascadeOnDelete)
   * `creator_id` (FK -> `users.id`, cascadeOnDelete)
   * `assignee_id` (FK -> `users.id`, nullOnDelete, nullable)
   * `number` (unsigned integer — sequential task number per project, e.g., `1` -> `DLX-1`)
   * `title` (string)
   * `description` (text, nullable)
   * `priority` (string: `low` | `medium` | `high` | `urgent`, default `medium`)
   * `position` (string, indexed)
   * `due_date` (date, nullable)
   * `timestamps`
   * Index on `['column_id', 'position']`
7. **`task_comments`**:
   * `id` (PK)
   * `task_id` (FK -> `tasks.id`, cascadeOnDelete)
   * `user_id` (FK -> `users.id`, cascadeOnDelete)
   * `body` (text)
   * `timestamps`

---

## 5. Development Roadmap

### Phase 1: Foundation, Database & Fractional Indexing
* Create migrations, Eloquent models with strict relationships, factories, and database seeders (demo workspace, project `DLX`, 4 columns: `Backlog`, `In Progress`, `In Review`, `Done`, and sample tasks).
* Implement `App\Services\FractionalIndexingService` with unit tests covering edge cases (prepend, append, insert between, narrow gaps).
* Configure automatic workspace creation when a new user registers.

### Phase 2: Workspaces, Projects & Authorization
* Implement Laravel Policies for Workspaces, Projects, Columns, and Tasks.
* Build Workspace switcher, Workspace member invitation/management, and Project creation/listing UI.
* Replace default Breeze `/dashboard` with the Delox Workspace/Projects hub.

### Phase 3: Interactive Kanban Board & Drag-and-Drop
* Build the `ProjectBoard` Inertia page with a clean, Linear-inspired dark/light UI.
* Create a typed Zustand store (`useBoardStore`) holding columns, tasks, active drag state, and optimistic rollback logic.
* Implement `@dnd-kit` horizontal column reordering and cross-column task dragging backed by `PATCH /columns/{column}/move` and `PATCH /tasks/{task}/move`.

### Phase 4: Task Details, Filtering & Comments
* Build a slide-over Task Detail Drawer for editing title, markdown/plain description, priority badge, assignee selector, due date, and activity comments.
* Add instant client-side board filtering (by search query, priority, and assignee).

### Phase 5: Real-Time Collaboration (Laravel Reverb)
* Configure private channel authorization in `routes/channels.php` for `project.{projectId}`.
* Broadcast all task and column mutations with `->toOthers()`.
* Subscribe to Echo events inside `ProjectBoard` to merge remote changes seamlessly into Zustand in real time.