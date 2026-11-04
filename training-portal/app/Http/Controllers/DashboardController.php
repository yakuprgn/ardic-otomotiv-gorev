<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\TrainingAssignment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $assignmentCount = TrainingAssignment::count();
        $completedCount = TrainingAssignment::whereNotNull('completed_at')->count();
        return view('dashboard', [
            'trainingCount' => Training::count(),
            'assignmentCount' => $assignmentCount,
            'pendingCount' => TrainingAssignment::whereNull('completed_at')->count(),
            'completedCount' => $completedCount,
            'completionRate' => $assignmentCount > 0 ? round($completedCount / $assignmentCount * 100) : 0,
            'recentAssignments' => TrainingAssignment::with('training')->latest('assigned_at')->limit(8)->get(),
        ]);
    }
}
