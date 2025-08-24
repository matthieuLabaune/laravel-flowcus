{{-- guideline: Pomodoro Sessions | version:1.0 --}}
<h2>Pomodoro Sessions</h2>
<p>State machine (linear, no overlap for a user):</p>
<pre>idle -> running_focus -> finished_focus -> running_break(short|long) -> finished_break -> (next cycle or idle)
(Pause optional later; not persisted yet)</pre>
<ul>
    <li>Exactly one active session (focus or break) per user at any time.</li>
    <li>Break sessions persisted with <code>type=short_break|long_break</code>.</li>
    <li><strong>Start Focus</strong>: create row (planned_seconds, started_at=now, actual_seconds=0, interruptions_count=0).</li>
    <li><strong>Interrupt</strong>: increments interruptions_count (only when running_focus) — no time recalculation yet.</li>
    <li><strong>Finish</strong>: ended_at=now; actual_seconds = ended_at - started_at (clamped >=0). Cannot finish twice.</li>
    <li><strong>Validation</strong>:
        <ul>
            <li>planned_seconds: int 60..7200</li>
            <li>task_id nullable but must belong to user if provided</li>
            <li>No new focus start if an unfished session exists (any type)</li>
        </ul>
    </li>
    <li>Long break: occurs after user completes N (long_break_interval) focus sessions in a cycle.</li>
</ul>
<p>Errors: throw domain exceptions (e.g. <code>CannotStartSessionException</code>) -> convert to 422 JSON.</p>
