{{-- guideline: Tasks CRUD | version:1.0 --}}
<h2>Tasks CRUD</h2>
<ul>
    <li>Fields: title (required<=255), description nullable long, deadline_at nullable datetime, status enum pending|in_progress|done.</li>
    <li>Create: default status=pending.</li>
    <li>Complete action: sets status=done + completed_at=now (atomic) unless already done.</li>
    <li>Deleting a project should nullify or cascade tasks? Current design: cascade via FK (project deletion removes tasks) — reconsider if we want archival later.</li>
    <li>Scopes: forUser(User), pending(), dueToday(user TZ), completed().</li>
    <li>Validation uses Form Request classes; never inline in controller.</li>
    <li>Eager load counts when listing (avoid N+1).</li>
</ul>
