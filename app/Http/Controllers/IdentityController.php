<?php

namespace App\Http\Controllers;

use App\Models\Identity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdentityController extends Controller
{
    public function index()
    {
        $identities = Auth::user()->identities;
        return response()->json($identities);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'statement' => 'required|string|max:255',
            'category' => 'required|string|max:50',
        ]);

        $identity = Auth::user()->identities()->create($validated);

        return response()->json($identity, 201);
    }

    public function update(Request $request, Identity $identity)
    {
        if ($identity->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'statement' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:50',
        ]);

        $identity->update($validated);

        return response()->json($identity);
    }

    public function destroy(Identity $identity)
    {
        if ($identity->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $identity->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
