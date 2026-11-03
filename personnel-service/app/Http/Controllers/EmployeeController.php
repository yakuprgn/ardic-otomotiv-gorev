<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    private const FILTER_FIELDS = ['first_name', 'last_name', 'email', 'department'];

    public function index(Request $request): View
    {
        $sort = (string) $request->query('sort', 'first_name');
        $sort = in_array($sort, [...self::FILTER_FIELDS, 'created_at'], true) ? $sort : 'first_name';

        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $perPage = (int) $request->query('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;

        $query = Employee::query();

        foreach (self::FILTER_FIELDS as $field) {
            $filter = trim((string) $request->query($field, ''));

            if ($filter !== '') {
                $query->where($field.'_search', 'like', '%'.Employee::normalizeForSearch($filter).'%');
            }
        }

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
        ]);
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
