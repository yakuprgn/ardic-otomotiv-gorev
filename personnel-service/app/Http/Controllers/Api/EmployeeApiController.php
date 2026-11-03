<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeApiController extends Controller
{
    private const FIELDS = [
        'id',
        'first_name',
        'last_name',
        'email',
        'department',
        'updated_at',
    ];

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 50), 1), 100);

        $query = Employee::query()
            ->select(self::FIELDS)
            ->orderBy('id');

        if ($request->filled('search')) {
            $search = Employee::normalizeForSearch(
                (string) $request->query('search')
            );

            $query->where(function ($query) use ($search): void {
                foreach ([
                    'first_name',
                    'last_name',
                    'email',
                    'department',
                ] as $field) {
                    $query->orWhere(
                        $field.'_search',
                        'like',
                        '%'.$search.'%'
                    );
                }
            });
        }

        return response()->json(
            $query->paginate($perPage)
        );
    }

    public function show(Employee $employee): JsonResponse
    {
        return response()->json([
            'data' => $employee->only(self::FIELDS),
        ]);
    }
}
