# Flowcus Product Specification & Technical Plan

Version: 0.1 (Living Document)
Last Updated: 2025-08-24

---
## 1. Vision
A focused productivity companion combining modern Pomodoro workflow, lightweight task/project management, contextual notes, and automatic daily knowledge consolidation (daily summaries + export). Differentiates via integrations (Notion / Taiga / Calendar), frictionless UX, and PKM-friendly exports.

---
## 2. Scope Phases
### MVP (Foundations)
Core personal productivity loop: configure focus cycle → run sessions on tasks → record outcomes → nightly synthesis visible next morning.

### V1 (Connectors & Insights)
Add data ingestion (project mgmt + calendars) + richer analytics + export options.

### V2 (Premium / Scale)
Team features, mobile surfaces, real-time presence, freemium gates, push & widgets.

---
## 3. Domain Model (Initial)
| Entity                  | Purpose                                  | Key Fields                                                                                                                                                       | Relations                                                                        |
| ----------------------- | ---------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------- |
| User                    | Auth + settings                          | name, email, password, pomodoro_length, short_break_length, long_break_length, long_break_interval                                                               | hasMany(Task), hasMany(PomodoroSession), hasMany(DailySummary)                   |
| Project                 | Logical grouping                         | name, color(optional), user_id                                                                                                                                   | belongsTo(User), hasMany(Task)                                                   |
| Task                    | Unit of planned work                     | title, description, deadline_at (nullable), status(enum: pending, in_progress, done), project_id (nullable), user_id                                             | belongsTo(User), belongsTo(Project), hasMany(PomodoroSession), hasMany(TaskNote) |
| TaskNote                | Rich note about task (manual)            | body (markdown), task_id, user_id                                                                                                                                | belongsTo(Task), belongsTo(User)                                                 |
| PomodoroSession         | Focus or break segment actually executed | task_id (nullable if free-form), user_id, type(enum: focus, short_break, long_break), planned_seconds, actual_seconds, interruptions_count, started_at, ended_at | belongsTo(User), belongsTo(Task)                                                 |
| SessionNote             | Post-session reflection                  | pomodoro_session_id, body                                                                                                                                        | belongsTo(PomodoroSession)                                                       |
| DailySummary            | Cached synthesized report                | user_id, summary_date, total_focus_seconds, completed_task_ids(json), markdown_body, generated_at                                                                | belongsTo(User)                                                                  |
| IntegrationAccount (V1) | External connector linkage               | user_id, provider(enum: notion, trello,...), access_token(encrypted), refresh_token, expires_at, meta(json)                                                      | belongsTo(User)                                                                  |
| ImportedItem (V1)       | External imported artifact mapping       | integration_account_id, external_id, type(task                                                                                                                   | project                                                                          | calendar_event), payload(json), mapped_task_id | belongsTo(IntegrationAccount) |

### Enums (PHP backed Enums)
- TaskStatus: Pending, InProgress, Done
- SessionType: Focus, ShortBreak, LongBreak

### Indexes
- tasks: (user_id, status), (project_id), (deadline_at)
- pomodoro_sessions: (user_id, started_at), (task_id, started_at)
- daily_summaries: (user_id, summary_date unique)

---
## 4. Database Migration Sketches (MVP)
(Names start with sequential timestamps when generated.)
```
users               -> add settings columns (pomodoro_length, short_break_length, long_break_length, long_break_interval INT small defaults)
projects            (id, user_id FK, name, color nullable, timestamps)
tasks               (id, user_id FK, project_id FK nullable, title, description longtext nullable, deadline_at nullable, status tinyint, completed_at nullable, timestamps)
task_notes          (id, task_id FK, user_id FK, body mediumtext, timestamps)
pomodoro_sessions   (id, user_id FK, task_id FK nullable, type tinyint, planned_seconds int, actual_seconds int default 0, interruptions_count smallint default 0, started_at, ended_at nullable, timestamps)
session_notes       (id, pomodoro_session_id FK, body mediumtext, timestamps)
daily_summaries     (id, user_id FK, summary_date date unique with user, total_focus_seconds int, completed_task_ids json, markdown_body longtext, generated_at datetime, timestamps)
```

---
## 5. Application Layers
### Backend (Laravel 12)
- Controllers: Thin resource + action controllers.
- Service Classes: `SummaryGenerator`, `PomodoroSessionService`, `Integration\NotionImporter` (future), `CalendarSyncService` (future).
- Jobs: `GenerateDailySummaryJob`, `ImportNotionWorkspaceJob`, `SyncCalendarEventsJob`.
- Events: `PomodoroSessionFinished`, `TaskCompleted`.
- Listeners: `QueueDailySummaryGeneration`, `UpdateTaskCompletionStats`.
- Policies: Task, Project (user ownership).
- Schedules (Console Kernel): Daily summary generation (22:59 local per user; initial global run then refine per user timezone), stale import retries.

### Frontend (Inertia + Vue 3 + shadcn-vue)
Pages (MVP):
- `/dashboard` (default) → Today focus stats + quick start timer + tasks for today.
- `/tasks` (list + create inline) & `/tasks/{id}` (detail: notes, sessions history).
- `/projects` (list + tasks nested).
- `/settings` (user pomodoro config).
- `/summaries` (list of days) & `/summaries/{date}` (markdown view + export buttons).

Shared Components:
- `TimerPanel.vue`: current cycle, progress ring, controls.
- `TaskList.vue`, `TaskItem.vue`.
- `SessionHistory.vue`.
- `StatsDonut.vue`, `StatsBar.vue`.
- `MarkdownViewer.vue` (shiki/remark later, start simple textarea / preview toggle).
- `ExportButtonGroup.vue`.

Composables:
- `useTimer()` (state machine: idle → running_focus → running_break → paused; emits events; uses `requestAnimationFrame` fallback setInterval 1s).
- `useTasks()` (fetch, optimistic updates).
- `useSummary()` (fetch/day, caching).

State: Keep local per-page; no global store initially (Inertia page props + small composables). Avoid over-engineering.

---
## 6. API / Routes (MVP)
```
GET    /dashboard                       DashboardController@index
Resource /tasks (index, store, show, update, destroy)
POST   /tasks/{task}/complete           TaskCompleteController@store
Resource /projects (index, store, update, destroy)
POST   /sessions/start                  PomodoroSessionController@start
POST   /sessions/{session}/finish       PomodoroSessionController@finish
POST   /sessions/{session}/interrupt    PomodoroSessionController@interrupt
POST   /sessions/{session}/notes        SessionNoteController@store
POST   /tasks/{task}/notes              TaskNoteController@store
GET    /summaries                       SummaryController@index
GET    /summaries/{date}                SummaryController@show
POST   /summaries/{date}/export         SummaryExportController@store (format param: markdown|csv)
PUT    /settings/pomodoro               Settings\PomodoroController@update
```
All web routes behind auth middleware. Later: `/api/v1/...` for mobile.

---
## 7. Timer Logic (MVP)
Config fields per user:
- focus: default 25 min
- short: default 5 min
- long: default 15 min
- interval: every 4 focus blocks => long break

State Machine (simplified):
```
Idle -> StartFocus -> (Pause/Resume) -> FocusFinished -> DecideBreak(short|long) -> StartBreak -> BreakFinished -> (Next cycle or Idle)
```
Persistence: Session created at StartFocus (type=focus, planned_seconds), updated on finish with `actual_seconds`, interruptions_count. Breaks also stored (type=short_break/long_break). This allows analytics of ratio planned/actual.

Interruptions: increment counter when user presses an 'Interrupt' button (or navigates away and returns) — simple at first.

---
## 8. Daily Summary Generation
Trigger: Scheduler nightly or on-demand if user views next morning and entry missing.
Process:
1. Gather sessions for date (00:00→23:59 user timezone). Sum focus sessions actual_seconds.
2. Identify tasks completed that day (tasks.completed_at within date).
3. Pull session notes + task notes created that day.
4. Generate Markdown template:
```
# Summary YYYY-MM-DD

Total Focus: HH:MM (X sessions)

## Completed Tasks
- [x] Task A
- [x] Task B

## Active Progress
- Task C (2 focus blocks)

## Session Notes
> Note snippet...

---
Generated at HH:MM.
```
5. Store to `daily_summaries.markdown_body` + stats fields. Cache avoids re-computation.

Exports: Markdown raw download; CSV of sessions (columns: task, type, planned, actual, interruptions).

---
## 9. Analytics (MVP → V1)
MVP: Totals (today/week/month), distribution (time per project), velocity (focus blocks per day last 7 days).
V1: Trend lines, retention graph (days with ≥1 focus), best focus hours (histogram).
Storage Strategy: Start with on-demand SQL + small cached aggregates (daily summaries table). Introduce `focus_metrics` table only if needed for performance.

---
## 10. Security & Privacy
- All sensitive integration tokens encrypted (Laravel encrypt casts or custom Attribute + config:key).
- CSRF via standard middleware (Inertia forms).
- Authorization: policies restrict tasks/projects to owning user. No multi-tenant complexity yet.
- Rate limiting for API (future mobile) via `api` guard + `ThrottleRequests`.

---
## 11. Performance & Scaling Early Choices
- DB queries kept O(1) per page: eager load task counts, sessions summary counts.
- Indexes per section 3.
- Use DB transactions in session start/finish to ensure atomic actual_seconds calculation if we later track overlapping sessions.

---
## 12. Testing Strategy
Pest Tests:
- Unit: Timer state machine, Summary markdown generator.
- Feature: Task CRUD, Pomodoro start/finish, Daily summary generation command/job.
- Browser (later): Timer continuity (# of seconds increments) using Pest Browser or Dusk alt.
Factories augmentations: Add states: `completed`, `withSessions`.

---
## 13. Frontend Component Details (MVP)
`TimerPanel.vue`:
- Props: currentTask (Task|null)
- Emits: start(taskId), finish(sessionId), interrupt(sessionId)
- Shows radial progress (use CSS conic-gradient first; later canvas).

`TaskList.vue`:
- Inline new task form.
- Filter: all / today (deadline today) / completed.

`StatsDonut.vue`:
- Input dataset: array<{ label, seconds, color }>
- Computes percentages; accessible legend.

`MarkdownViewer.vue`:
- Simple: two-panel toggle (Edit / Preview) using `marked` or minimal parser; later upgrade.

---
## 14. Connectors (V1 Design Preview)
Abstraction Interface:
```
interface ExternalProvider {
  public function fetchProjects(User $user): Collection;
  public function fetchTasks(User $user): Collection;
  public function import(User $user): ImportReport; // orchestrates
}
```
Providers implement throttle + pagination. Store raw payload JSON for traceability.

Cron Strategy: Nightly incremental sync; user-triggered manual import.

---
## 15. Export Architecture
Strategy objects per format: `MarkdownSummaryExporter`, `CsvSessionsExporter`. Controller resolves by requested format, returns `StreamedResponse`.

---
## 16. Roadmap Backlog (Prioritized)
### P0 (MVP Must)
- Migrations & models (User settings, Projects, Tasks, PomodoroSessions, Notes, DailySummaries)
- Timer service + session start/finish endpoints
- Task CRUD + completion endpoint
- Daily summary job + on-demand generation
- Dashboard initial (today stats + current timer + quick tasks)
- Summaries list & detail (markdown view + export MD)
- Settings (pomodoro config)
- Basic analytics (time today/week, distribution donut)
- Tests (core flows)

### P1 (Nice-to-have within MVP if time)
- Interrupt tracking UI
- CSV export of sessions
- Rich markdown editor improvements (hotkeys)
- Dark mode toggle

### V1 Core
- Notion import (tasks & databases mapping)
- Calendar read (Google) -> show upcoming events overlapping focus blocks
- Advanced analytics (trend chart, productivity streak)
- Export: CSV + Notion push-back or folder writing

### V2
- Team workspaces (org_id, invitations)
- Real-time presence (WebSockets for running sessions)
- Mobile apps & push notifications
- Freemium gating (Stripe + Subscription model)

---
## 17. TODO Task Breakdown (Execution Friendly)
### Setup / Infra
- [ ] Add missing migrations (projects, tasks, task_notes, pomodoro_sessions, session_notes, daily_summaries, user settings update)
- [ ] Add model classes + relationships + casts
- [ ] Register policies (TaskPolicy, ProjectPolicy)
- [ ] Add enums (TaskStatus, SessionType)
- [ ] Seed minimal demo data (Project Demo + 3 tasks)

### Timer & Sessions
- [ ] Create PomodoroSessionService (start, finish, interrupt)
- [ ] Events + Listeners wiring
- [ ] Controller endpoints & Inertia actions
- [ ] Vue composable `useTimer()` + integration in `Dashboard` page

### Tasks & Projects
- [ ] TaskController (index/filter, store, update, destroy, complete)
- [ ] ProjectController
- [ ] Task notes endpoint + UI inline

### Daily Summaries
- [ ] SummaryGenerator service
- [ ] Job + schedule registration
- [ ] Controller (index/show) + Inertia pages
- [ ] Export (markdown) action

### Analytics Components
- [ ] Query helpers (aggregate focus time)
- [ ] Donut + bar components

### Settings
- [ ] Form request + update endpoint
- [ ] Settings page UI

### Testing
- [ ] Factories enhancements (project, task note, sessions)
- [ ] Unit tests: SummaryGenerator, PomodoroSessionService
- [ ] Feature tests: Task CRUD, session lifecycle, summary generation

### Polish
- [ ] Authorization checks everywhere
- [ ] Validation messages i18n
- [ ] Basic responsive layout polish
- [ ] Loading & empty states

---
## 18. Risks & Mitigations
| Risk                             | Impact                   | Mitigation                                                                    |
| -------------------------------- | ------------------------ | ----------------------------------------------------------------------------- |
| Timer drift in browser tabs      | Inaccurate session times | Use server finish timestamp & compute delta; reconcile heartbeat occasionally |
| Large session aggregation later  | Slow analytics           | Pre-compute daily summaries now                                               |
| Integration rate limits (future) | Failed syncs             | Backoff + queue jobs with retry & provider-specific pacing                    |
| Timezone correctness             | Wrong daily boundaries   | Store user timezone & compute ranges centrally                                |

---
## 19. Competitive Notes (Focus To-Do vs Flowcus)
| Aspect                  | Focus To-Do                     | Flowcus (Target)                                    |
| ----------------------- | ------------------------------- | --------------------------------------------------- |
| Daily Automatic Summary | No native consolidated markdown | Built-in structured markdown export                 |
| Integrations            | Limited / closed                | Open APIs (Notion, Taiga, Trello, Calendar)         |
| PKM Exports             | Minimal                         | 1-click Markdown / CSV / (future) Notion write-back |
| UX                      | Legacy look                     | Modern shadcn-vue + minimal friction                |
| Team visibility         | N/A                             | Planned (V2) live presence / collaboration          |

Modernisation Focus: frictionless timer start (1 click), inline editable tasks, zero modal flows, accessible UI (keyboard + ARIA), dark mode.

---
## 20. Open Questions
- Do we need optimistic updates for session start, or wait for server ack? (Lean: optimistic then rollback if failure.)
- Should breaks be mandatory records, or synthetic? (Store them: analytics clarity.)
- Multi-timezone (user travels) — store timezone per session start.

---
## 21. Next Immediate Actions (Concrete)
1. Implement migrations & enums.
2. Implement models + relationships.
3. Build PomodoroSessionService + tests.
4. Create Task + Project controllers/views.
5. Summary generator + nightly schedule.
6. Frontend timer composable + dashboard integration.

---
## 22. Appendix: Example Summary Markdown (Generated)
```
# Summary 2025-08-24
Total Focus: 3h 15m (7 focus sessions)

## Completed Tasks
- [x] Implement SummaryGenerator
- [x] Add TimerPanel component

## Active Progress
- Refactor TaskList (2 focus blocks)

## Session Notes
> Refined timer drift logic.
> Need better error boundary for summary failure.

---
Generated at 22:59 local.
```

---
## 23. Maintenance
Document updates happen after each implemented vertical slice. Keep TODO list pruned: remove shipped, re-prioritize remaining.

---
(End of Document)
