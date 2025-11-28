<?php

namespace App\Http\Controllers;

use App\Models\Reflection;
use Illuminate\Http\Request;

class ReflectionController extends Controller
{
    public function index()
    {
        // Returns today's reflection + next 6 days for caching (7 days total)
        $today = now()->toDateString();
        $nextWeek = now()->addDays(6)->toDateString();

        return Reflection::whereBetween('scheduled_date', [$today, $nextWeek])
            ->orderBy('scheduled_date')
            ->get();
    }
}
