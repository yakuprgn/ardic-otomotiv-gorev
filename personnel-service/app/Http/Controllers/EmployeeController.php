<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Services\XlsxExporter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EmployeeController extends Controller
{
    private const FILTER_FIELDS = ['first_name', 'last_name', 'email', 'department'];

    public function index(Request $request): View
    {
        $sort = (string) $request->query('sort', 'first_name');
        $sort = in_array($sort, [...self::FILTER_FIELDS, 'created_at'], true) ? $sort : 'first_name';

        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $perPage = (int) $request->query('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $query = $this->filtered($request);

        $column = $sort === 'created_at' ? $sort : $sort.'_search';

        $employees = $query
            ->orderBy($column, $direction)
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('employees.index', [
            'employees' => $employees,
            'totalCount' => Employee::count(),
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $perPage,
            'departments' => Employee::query()->distinct()->orderBy('department')->pluck('department'),
        ]);
    }

    public function export(Request $request, XlsxExporter $xlsx): Response
    {
        $query = $this->filtered($request);
        $sort = in_array($request->query('sort'), [...self::FILTER_FIELDS, 'created_at'], true) ? $request->query('sort') : 'first_name';
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sort === 'created_at' ? $sort : $sort.'_search', $direction)->orderBy('id');
        $ids = array_values(array_filter(array_map('intval', explode(',', (string) $request->query('ids')))));
        if ($request->query('scope') === 'selected' && $ids !== []) $query->whereIn('id', $ids);
        if ($request->query('scope') === 'page') $query->forPage(max(1,(int)$request->query('page',1)), min(100,max(1,(int)$request->query('per_page',10))));
        $rows = $query->cursor()->map(fn(Employee $e) => [$e->id,$e->first_name,$e->last_name,$e->email,$e->department,$e->created_at]);
        try {
            return $xlsx->download('personeller-'.now()->format('Ymd-His').'.xlsx', ['ID','Ad','Soyad','E-posta','Departman','Oluşturulma tarihi'], $rows);
        } catch (Throwable $exception) {
            report($exception);
            return redirect()->route('employees.index', $request->except(['scope','ids']))->with('error', $exception->getMessage());
        }
    }

    private function filtered(Request $request)
    {
        $query = Employee::query();
        $global = Employee::normalizeForSearch(trim((string)$request->query('search')));
        if ($global !== '') $query->where(function($q) use($global){ foreach(self::FILTER_FIELDS as $field) $q->orWhere($field.'_search','like','%'.$global.'%'); });
        foreach (self::FILTER_FIELDS as $field) {
            $filter = trim((string) $request->query($field, ''));
            if ($filter !== '') $query->where($field.'_search', 'like', '%'.Employee::normalizeForSearch($filter).'%');
        }
        if ($request->filled('created_from')) $query->whereDate('created_at','>=',$request->query('created_from'));
        if ($request->filled('created_to')) $query->whereDate('created_at','<=',$request->query('created_to'));
        return $query;
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);

        $validated = $this->validateEmployee($request);

        Employee::create($validated);

        return back()->with('success', 'Personel başarıyla eklendi.');
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $this->normalizeEmail($request);

        $validated = $this->validateEmployee($request, $employee);

        $employee->update($validated);

        return back()->with('success', 'Personel başarıyla güncellendi.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return back()->with('success', 'Personel kaydı pasife alındı.');
    }

    private function normalizeEmail(Request $request): void
    {
        $email = $request->input('email');

        if (is_string($email)) {
            $request->merge(['email' => mb_strtolower(trim($email))]);
        }
    }

    private function validateEmployee(Request $request, ?Employee $employee = null): array
    {
        return Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($employee?->id),
            ],
            'department' => ['required', 'string', 'max:120'],
        ], [
            'required' => ':attribute zorunludur.',
            'email' => 'Geçerli bir e-posta adresi girin.',
            'unique' => 'Bu e-posta adresi zaten kayıtlı.',
            'max' => ':attribute izin verilen uzunluğu aşıyor.',
        ], [
            'first_name' => 'Ad',
            'last_name' => 'Soyad',
            'email' => 'E-posta',
            'department' => 'Departman',
        ])->validate();
    }
}
