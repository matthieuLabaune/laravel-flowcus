{{-- guideline: Pomodoro Domain Design | audience=ai | version=1.0 --}}
<h2>Pomodoro Domain Design</h2>
<p>The Pomodoro domain centers around Users running timed focus sessions (PomodoroSessions) optionally attached to Tasks.</p>
<p><strong>Principles:</strong></p>
<ul>
    <li>Single active focus session per user at any time.</li>
    <li>Break sessions (short/long) are persisted for analytics symmetry.</li>
    <li>Historical sessions immutable; corrections create new sessions.</li>
    <li>Enums: TaskStatus (pending, in_progress, done); SessionType (focus, short_break, long_break).</li>
    <li>Daily summaries aggregate only <code>SessionType::Focus</code> actual_seconds.</li>
    <li>Planned vs actual kept for velocity metrics.</li>
    <li>Interruptions_count increments via explicit interrupt action only.</li>
</ul>
<p><strong>Validation (initial draft):</strong></p>
<pre>
start: task_id nullable|exists:tasks,id (must belong to user); planned_seconds integer 60..7200
finish: session must be running; ended_at set server-side; actual_seconds derived
interrupt: only allowed while running; increments interruptions_count
</pre>
<p>Return concise JSON; derive progress percentage client-side. Avoid overfetching: sessions list paginated, single active session pushed via polling or later websockets.</p>
