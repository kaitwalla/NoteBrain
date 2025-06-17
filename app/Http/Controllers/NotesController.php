<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotesController extends Controller
{
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
    }
    /**
     * Display a listing of the notes.
     */
    public function index(Request $request)
    {
        $perPage = $request->user()->preferences->article_preferences['articles_per_page'] ?? 20;
        $status = $request->get('status', 'inbox');

        $query = Note::query()->where('user_id', auth()->id());

        if ($status === 'inbox') {
            $query->where('status', 'inbox');
        } elseif ($status === 'archived') {
            $query->where('status', 'archived');
        }

        $query->orderBy('created_at', 'desc');

        $notes = $query->paginate($perPage);

        return view('notes.index', [
            'notes' => $notes,
            'currentStatus' => $status,
            'inboxCount' => Note::where('status', 'inbox')->where('user_id', auth()->id())->count(),
            'archivedCount' => Note::where('status', 'archived')->where('user_id', auth()->id())->count(),
        ]);
    }

    /**
     * Show the form for creating a new note.
     */
    public function create()
    {
        return view('notes.create');
    }

    /**
     * Store a newly created note in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userId = auth()->id();

        $note = new Note([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'status' => Note::STATUS_INBOX,
            'user_id' => $userId,
        ]);

        try {
            $note->save();
        } catch (\Exception $e) {
            \Log::error('Failed to save note: ' . $e->getMessage());
            \Log::error('User ID: ' . $userId);
            throw $e;
        }

        return redirect()->route('notes.show', $note)
            ->with('success', 'Note saved successfully.');
    }

    /**
     * Display the specified note.
     */
    public function show(Note $note)
    {
        $this->authorize('view', $note);

        return view('notes.show', compact('note'));
    }

    /**
     * Show the form for editing the specified note.
     */
    public function edit(Note $note)
    {
        $this->authorize('update', $note);

        return view('notes.edit', compact('note'));
    }

    /**
     * Update the specified note in storage.
     */
    public function update(Request $request, Note $note)
    {
        $this->authorize('update', $note);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $note->update($validated);

        return redirect()->route('notes.show', $note)
            ->with('success', 'Note updated successfully.');
    }

    /**
     * Move a note to inbox.
     */
    public function inbox(Note $note)
    {
        $this->authorize('update', $note);

        $note->moveToInbox();
        return redirect()->back()->with('success', 'Note moved to inbox.');
    }

    /**
     * Archive a note.
     */
    public function archive(Note $note)
    {
        $this->authorize('update', $note);

        $note->archive();
        return redirect()->back()->with('success', 'Note archived.');
    }

    /**
     * Toggle the star status of a note.
     */
    public function toggleStar(Note $note)
    {
        $this->authorize('update', $note);

        try {
            if ($note->starred) {
                // If note is starred, unstar it
                if ($note->google_drive_file_id) {
                    $this->googleDriveService->deleteFile($note->google_drive_file_id);
                    $note->update(['google_drive_file_id' => null]);
                }
                $note->unstar();
                $message = 'Note unstarred successfully';
            } else {
                // If note is not starred, star it
                $note->star();
                if (!$note->google_drive_file_id) {
                    $driveFileId = $this->googleDriveService->saveNoteText($note);
                    if ($driveFileId) {
                        $note->update(['google_drive_file_id' => $driveFileId]);
                    }
                }
                $message = 'Note starred successfully';
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Failed to toggle star status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to toggle star status');
        }
    }

    /**
     * Remove the specified note from storage.
     */
    public function destroy(Note $note)
    {
        $this->authorize('delete', $note);

        $note->delete();
        return redirect()->route('notes.index')->with('success', 'Note deleted successfully.');
    }

    /**
     * Handle bulk actions on multiple notes.
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|string|in:archive,inbox,star,unstar,delete',
            'note_ids' => 'required|array',
            'note_ids.*' => 'exists:notes,id',
        ]);

        $action = $validated['action'];
        $noteIds = $validated['note_ids'];
        $count = count($noteIds);
        $successMessage = '';

        try {
            // Get notes that belong to the current user
            $notes = Note::whereIn('id', $noteIds)
                ->where('user_id', auth()->id())
                ->get();

            if ($notes->isEmpty()) {
                return redirect()->back()->with('error', 'No valid notes selected');
            }

            switch ($action) {
                case 'archive':
                    foreach ($notes as $note) {
                        $note->archive();
                    }
                    $successMessage = $count . ' note(s) archived successfully';
                    break;

                case 'inbox':
                    foreach ($notes as $note) {
                        $note->moveToInbox();
                    }
                    $successMessage = $count . ' note(s) moved to inbox successfully';
                    break;

                case 'star':
                    foreach ($notes as $note) {
                        if (!$note->starred) {
                            $note->star();
                            if (!$note->google_drive_file_id) {
                                $driveFileId = $this->googleDriveService->saveNoteText($note);
                                if ($driveFileId) {
                                    $note->update(['google_drive_file_id' => $driveFileId]);
                                }
                            }
                        }
                    }
                    $successMessage = $count . ' note(s) starred successfully';
                    break;

                case 'unstar':
                    foreach ($notes as $note) {
                        if ($note->starred) {
                            if ($note->google_drive_file_id) {
                                $this->googleDriveService->deleteFile($note->google_drive_file_id);
                                $note->update(['google_drive_file_id' => null]);
                            }
                            $note->unstar();
                        }
                    }
                    $successMessage = $count . ' note(s) unstarred successfully';
                    break;

                case 'delete':
                    foreach ($notes as $note) {
                        $note->delete();
                    }
                    $successMessage = $count . ' note(s) deleted successfully';
                    break;
            }

            return redirect()->back()->with('success', $successMessage);
        } catch (\Exception $e) {
            \Log::error('Failed to perform bulk action: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to perform bulk action');
        }
    }
}
