<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Notes\StoreNoteRequest;
use App\Http\Requests\Notes\UpdateNoteRequest;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function store(StoreNoteRequest $request): JsonResponse
    {
        $note = Note::create([
            'user_id' => $request->user()->id,
            'content' => $request->validated('content'),
            'noteable_type' => $request->validated('noteable_type'),
            'noteable_id' => $request->validated('noteable_id'),
        ]);

        return response()->json([
            'message' => 'Note created successfully',
            'note' => $note->load('user'),
        ], 201);
    }

    public function update(UpdateNoteRequest $request, Note $note): JsonResponse
    {
        $this->authorize('update', $note);

        $note->update([
            'content' => $request->validated('content'),
        ]);

        return response()->json([
            'message' => 'Note updated successfully',
            'note' => $note->fresh()->load('user'),
        ]);
    }

    public function destroy(Request $request, Note $note): JsonResponse
    {
        $this->authorize('delete', $note);

        $note->delete();

        return response()->json([
            'message' => 'Note deleted successfully',
        ]);
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
}
