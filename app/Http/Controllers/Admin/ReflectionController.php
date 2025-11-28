<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reflection;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReflectionController extends Controller
{
    public function index()
    {
        $reflections = Reflection::orderBy('scheduled_date', 'desc')->paginate(10);
        return Inertia::render('admin/reflections/index', [
            'reflections' => $reflections
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/reflections/create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:text,audio,quote,verse',
            'scheduled_date' => 'required|date|unique:reflections,scheduled_date',
            'content' => 'nullable|string',
            'author' => 'nullable|string',
            'reference' => 'nullable|string',
            'media_url' => 'nullable|string',
        ]);

        Reflection::create($validated);

        return redirect()->route('admin.reflections.index')
            ->with('success', 'Reflection created successfully.');
    }

    public function edit(Reflection $reflection)
    {
        return Inertia::render('admin/reflections/edit', [
            'reflection' => $reflection
        ]);
    }

    public function update(Request $request, Reflection $reflection)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:text,audio,quote,verse',
            'scheduled_date' => 'required|date|unique:reflections,scheduled_date,' . $reflection->id,
            'content' => 'nullable|string',
            'author' => 'nullable|string',
            'reference' => 'nullable|string',
            'media_url' => 'nullable|string',
        ]);

        $reflection->update($validated);

        return redirect()->route('admin.reflections.index')
            ->with('success', 'Reflection updated successfully.');
    }

    public function destroy(Reflection $reflection)
    {
        $reflection->delete();
        return redirect()->route('admin.reflections.index')
            ->with('success', 'Reflection deleted successfully.');
    }
}
