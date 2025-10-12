<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'task_id' => 'nullable|integer',
        ]);
        $q = JournalEntry::where('user_id', $request->user()->id)->orderBy('entry_date', 'desc');
        if (!empty($data['from'])) {
            $q->whereDate('entry_date', '>=', $data['from']);
        }
        if (!empty($data['to'])) {
            $q->whereDate('entry_date', '<=', $data['to']);
        }
        if (!empty($data['task_id'])) {
            $q->where('task_id', $data['task_id']);
        }
        return $q->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'entry_date' => 'required|date',
            'content' => 'required|string',
            'task_id' => 'nullable|integer',
        ]);
        $data['user_id'] = $request->user()->id;
        $entry = JournalEntry::create($data);
        return $entry;
    }

    public function update(Request $request, JournalEntry $journal)
    {
        if ($journal->user_id !== $request->user()->id) {
            abort(403);
        }
        $data = $request->validate([
            'entry_date' => 'sometimes|date',
            'content' => 'sometimes|string',
            'task_id' => 'nullable|integer',
        ]);
        $journal->update($data);
        return $journal;
    }

    public function destroy(Request $request, JournalEntry $journal)
    {
        if ($journal->user_id !== $request->user()->id) {
            abort(403);
        }
        $journal->delete();
        return response()->noContent();
    }
}
