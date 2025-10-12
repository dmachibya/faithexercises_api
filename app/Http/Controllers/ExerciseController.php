<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function index()
    {
        return Exercise::orderBy('sort_order')->orderBy('id')->get();
    }

    public function show(Exercise $exercise)
    {
        return $exercise;
    }

    public function tasks(Exercise $exercise)
    {
        return $exercise->tasks()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();
    }
}
