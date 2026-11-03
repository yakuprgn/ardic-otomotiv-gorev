<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\TrainingAssignment;
use App\Services\PersonnelService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class AssignmentController extends Controller
{
    public function index(Request $request, PersonnelService $personnel): View
    {
        $status = in_array($request->query('status'), ['pending', 'completed'], true)
            ? $request->query('status') : 'pending';
        $assignments = TrainingAssignment::with('training')
            ->when($status === 'pending', fn ($query) => $query->whereNull('completed_at'))
            ->when($status === 'completed', fn ($query) => $query->whereNotNull('completed_at'))
            ->latest('assigned_at')->paginate(10)->withQueryString();

        $employees = [];
        $serviceError = null;
        try {
            $employees = $personnel->all();
        } catch (RuntimeException $exception) {
            $serviceError = $exception->getMessage();
        }

        return view('assignments.index', [
            'assignments' => $assignments,
            'trainings' => Training::orderBy('name')->get(),
            'employees' => $employees,
            'serviceError' => $serviceError,
            'status' => $status,
        ]);
    }

    public function store(Request $request, PersonnelService $personnel): RedirectResponse
    {
        $validated = $request->validate([
            'training_id' => ['required', 'integer', 'exists:trainings,id'],
            'employee_id' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $employee = $personnel->find((int) $validated['employee_id']);
            TrainingAssignment::create([
                ...$validated,
                'employee_name' => trim(($employee['first_name'] ?? '').' '.($employee['last_name'] ?? '')),
                'employee_email' => $employee['email'] ?? '',
                'employee_department' => $employee['department'] ?? '',
                'assigned_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return back()->withInput()->with('error', 'Bu eğitim personele daha önce atanmış.');
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Eğitim personele atandı.');
    }

    public function complete(TrainingAssignment $assignment): RedirectResponse
    {
        if ($assignment->completed_at === null) {
            $assignment->update(['completed_at' => now()]);
        }
        return back()->with('success', 'Eğitim tamamlandı olarak işaretlendi.');
    }
}
