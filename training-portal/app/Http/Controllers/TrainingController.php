<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\XlsxExporter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TrainingController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $sort = in_array($request->query('sort'), ['name','duration_minutes','created_at'], true) ? $request->query('sort') : 'created_at';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int)$request->query('per_page'),[10,25,50,100],true)?(int)$request->query('per_page'):10;
        $trainings = $this->filtered($request)->withCount('assignments')->orderBy($sort,$direction)->orderBy('id')->paginate($perPage)->withQueryString();
        return view('trainings.index', compact('trainings', 'search','sort','direction','perPage'));
    }

    public function export(Request $request, XlsxExporter $xlsx): Response
    {
        $query=$this->filtered($request); $sort=in_array($request->query('sort'),['name','duration_minutes','created_at'],true)?$request->query('sort'):'created_at';
        $query->orderBy($sort,$request->query('direction')==='asc'?'asc':'desc')->orderBy('id');
        $ids=array_values(array_filter(array_map('intval',explode(',',(string)$request->query('ids'))))); if($request->query('scope')==='selected'&&$ids!==[])$query->whereIn('id',$ids);
        if($request->query('scope')==='page')$query->forPage(max(1,(int)$request->query('page',1)),min(100,max(1,(int)$request->query('per_page',10))));
        try {
            return $xlsx->download('egitimler-'.now()->format('Ymd-His').'.xlsx',['ID','Eğitim adı','Açıklama','Süre (dk)','Oluşturulma tarihi'],$query->cursor()->map(fn($t)=>[$t->id,$t->name,$t->description,$t->duration_minutes,$t->created_at]));
        } catch (Throwable $exception) {
            report($exception);
            return redirect()->route('trainings.index', $request->except(['scope','ids']))->with('error', $exception->getMessage());
        }
    }

    private function filtered(Request $request)
    {
        $q=Training::query(); $search=trim((string)$request->query('search'));
        if($search!=='')$q->where(fn($i)=>$i->where('name','like','%'.$search.'%')->orWhere('description','like','%'.$search.'%'));
        if($request->filled('name'))$q->where('name','like','%'.$request->query('name').'%');
        if($request->filled('description'))$q->where('description','like','%'.$request->query('description').'%');
        if($request->filled('duration_min'))$q->where('duration_minutes','>=',(int)$request->query('duration_min'));
        if($request->filled('duration_max'))$q->where('duration_minutes','<=',(int)$request->query('duration_max'));
        if($request->filled('created_from'))$q->whereDate('created_at','>=',$request->query('created_from'));
        if($request->filled('created_to'))$q->whereDate('created_at','<=',$request->query('created_to'));
        return $q;
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
