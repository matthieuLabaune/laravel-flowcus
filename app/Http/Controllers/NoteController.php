<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Notes\StoreNoteRequest;
use App\Http\Requests\Notes\UpdateNoteRequest;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NoteController extends Controller
{
    public function store(StoreNoteRequest $request)
    {
        $note = Note::create([
            'user_id' => $request->user()->id,
            'content' => $request->validated('content'),
            'noteable_type' => $request->validated('noteable_type'),
            'noteable_id' => $request->validated('noteable_id'),
        ]);

        return back()->with('success', 'Note ajoutée avec succès');
    }

    public function update(UpdateNoteRequest $request, Note $note)
    {
        $this->authorize('update', $note);

        $note->update([
            'content' => $request->validated('content'),
        ]);

        return back()->with('success', 'Note mise à jour avec succès');
    }

    public function destroy(Request $request, Note $note)
    {
        $this->authorize('delete', $note);

        $note->delete();

        return back()->with('success', 'Note supprimée avec succès');
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'noteable_type' => 'required|string',
            'noteable_id' => 'required|integer',
        ]);

        $notes = Note::query()
            ->where('user_id', $request->user()->id)
            ->where('noteable_type', $request->get('noteable_type'))
            ->where('noteable_id', $request->get('noteable_id'))
            ->with('user')
            ->latest()
            ->get();

        return response()->json([
            'notes' => $notes,
        ]);
    }

    public function indexPage(Request $request): Response
    {
        $user = $request->user();

        // Get all notes grouped by type
        $sessionNotes = Note::query()
            ->where('user_id', $user->id)
            ->where('noteable_type', 'App\\Models\\PomodoroSession')
            ->with('noteable')
            ->latest()
            ->get();

        $taskNotes = Note::query()
            ->where('user_id', $user->id)
            ->where('noteable_type', 'App\\Models\\Task')
            ->with('noteable')
            ->latest()
            ->get();

        $projectNotes = Note::query()
            ->where('user_id', $user->id)
            ->where('noteable_type', 'App\\Models\\Project')
            ->with('noteable')
            ->latest()
            ->get();

        return Inertia::render('Notes/Index', [
            'sessionNotes' => $sessionNotes,
            'taskNotes' => $taskNotes,
            'projectNotes' => $projectNotes,
        ]);
    }

    public function show(Request $request, Note $note): JsonResponse
    {
        $this->authorize('view', $note);

        return response()->json([
            'note' => $note->load('user'),
        ]);
    }
}
