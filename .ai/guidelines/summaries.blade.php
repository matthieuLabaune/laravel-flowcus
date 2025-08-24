{{-- guideline: Daily Summaries | version:1.0 --}}
<h2>Daily Summaries</h2>
<p>Generate once per (user, date). If missing on view, generate on-demand.</p>
<ol>
  <li>Collect all focus sessions where started_at between day start/end (user TZ) and type = focus.</li>
  <li>Sum actual_seconds -> total_focus_seconds.</li>
  <li>Completed tasks = tasks.completed_at within date.</li>
  <li>Aggregate session notes + task notes created that day.</li>
  <li>Build markdown (header, totals, completed list, progress, notes).</li>
  <li>Persist: daily_summaries row (unique index) with markdown_body + stats.</li>
</ol>
<p>Idempotent: re-run replaces markdown_body + stats (upsert-like). Avoid regenerating if record exists unless force flag.</p>
<p>Exports: Markdown (raw) initial; CSV sessions (task title, type, planned_seconds, actual_seconds, interruptions_count).</p>
