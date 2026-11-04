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
use App\Services\XlsxExporter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AssignmentController extends Controller
{
    public function index(Request $request, PersonnelService $personnel): View
    {
        $status = in_array($request->query('status'), ['pending', 'completed'], true)
            ? $request->query('status') : 'pending';
        $sort=in_array($request->query('sort'),['employee_name','assigned_at','completed_at'],true)?$request->query('sort'):'assigned_at';
        $direction=$request->query('direction')==='asc'?'asc':'desc'; $perPage=in_array((int)$request->query('per_page'),[10,25,50,100],true)?(int)$request->query('per_page'):10;
        $assignments = $this->filtered($request)->with('training')->orderBy($sort,$direction)->orderBy('id')->paginate($perPage)->withQueryString();

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
            'sort'=>$sort,'direction'=>$direction,'perPage'=>$perPage,
        ]);
    }

    public function export(Request $request, XlsxExporter $xlsx): Response
    {
        $q=$this->filtered($request)->with('training');$sort=in_array($request->query('sort'),['employee_name','assigned_at','completed_at'],true)?$request->query('sort'):'assigned_at';$q->orderBy($sort,$request->query('direction')==='asc'?'asc':'desc')->orderBy('id');
        $ids=array_values(array_filter(array_map('intval',explode(',',(string)$request->query('ids')))));if($request->query('scope')==='selected'&&$ids!==[])$q->whereIn('id',$ids);if($request->query('scope')==='page')$q->forPage(max(1,(int)$request->query('page',1)),min(100,max(1,(int)$request->query('per_page',10))));
        try {
            return $xlsx->download('atamalar-'.now()->format('Ymd-His').'.xlsx',['Personel','E-posta','Eğitim','Atanma tarihi','Durum','Tamamlanma tarihi'],$q->cursor()->map(fn($a)=>[$a->employee_name,$a->employee_email,$a->training->name,$a->assigned_at,$a->completed_at?'Tamamlandı':'Bekliyor',$a->completed_at]));
        } catch (Throwable $exception) {
            report($exception);
            return redirect()->route('assignments.index', $request->except(['scope','ids']))->with('error', $exception->getMessage());
        }
    }

    private function filtered(Request $request)
    {
        $q=TrainingAssignment::query();$status=in_array($request->query('status'),['pending','completed'],true)?$request->query('status'):'pending';$q->when($status==='pending',fn($x)=>$x->whereNull('completed_at'))->when($status==='completed',fn($x)=>$x->whereNotNull('completed_at'));
        $search=trim((string)$request->query('search'));if($search!=='')$q->where(fn($x)=>$x->where('employee_name','like','%'.$search.'%')->orWhere('employee_email','like','%'.$search.'%')->orWhereHas('training',fn($t)=>$t->where('name','like','%'.$search.'%')));
        if($request->filled('employee_name'))$q->where('employee_name','like','%'.$request->query('employee_name').'%');if($request->filled('training_name'))$q->whereHas('training',fn($t)=>$t->where('name','like','%'.$request->query('training_name').'%'));
        if($request->filled('assigned_from'))$q->whereDate('assigned_at','>=',$request->query('assigned_from'));if($request->filled('assigned_to'))$q->whereDate('assigned_at','<=',$request->query('assigned_to'));if($request->filled('completed_from'))$q->whereDate('completed_at','>=',$request->query('completed_from'));if($request->filled('completed_to'))$q->whereDate('completed_at','<=',$request->query('completed_to'));return $q;
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
