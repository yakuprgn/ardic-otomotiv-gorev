<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $trainings = Training::query()
            ->withCount('assignments')
            ->when($search !== '', fn ($query) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')))
            ->latest()->paginate(10)->withQueryString();

        return view('trainings.index', compact('trainings', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        Training::create($this->validated($request));
        return back()->with('success', 'Eğitim oluşturuldu.');
    }

    public function update(Request $request, Training $training): RedirectResponse
    {
        $training->update($this->validated($request));
        return back()->with('success', 'Eğitim güncellendi.');
    }

    public function destroy(Training $training): RedirectResponse
    {
        if ($training->assignments()->exists()) {
            return back()->with('error', 'Ataması bulunan eğitim silinemez.');
        }
        $training->delete();
        return back()->with('success', 'Eğitim silindi.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:10080'],
        ]);
    }
}
