<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\TrainingAssignment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'trainingCount' => Training::count(),
            'assignmentCount' => TrainingAssignment::count(),
            'pendingCount' => TrainingAssignment::whereNull('completed_at')->count(),
            'completedCount' => TrainingAssignment::whereNotNull('completed_at')->count(),
            'recentAssignments' => TrainingAssignment::with('training')->latest('assigned_at')->limit(8)->get(),
        ]);
    }
}
