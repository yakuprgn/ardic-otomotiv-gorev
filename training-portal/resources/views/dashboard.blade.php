@extends('layouts.app')
@section('title','Dashboard')
@section('content')
<div class="mb-3"><h1 class="h4 fw-bold mb-1">Eğitim Özeti</h1><p class="small text-secondary mb-0">Atama ve tamamlanma durumlarının güncel görünümü.</p></div>
<div class="row g-3 mb-4">
@foreach([['Toplam eğitim',$trainingCount,'journal-text'],['Toplam atama',$assignmentCount,'people'],['Bekleyen',$pendingCount,'hourglass-split'],['Tamamlanan',$completedCount,'check-circle']] as [$label,$value,$icon])
<div class="col-6 col-xl-3"><div class="panel metric p-3 h-100"><div class="d-flex justify-content-between"><div><div class="small-label">{{ $label }}</div><div class="fs-3 fw-bold">{{ $value }}</div></div><i class="bi bi-{{ $icon }} fs-4 text-primary"></i></div></div></div>
@endforeach
</div>
<section class="panel overflow-hidden"><div class="p-3 border-bottom fw-semibold">Son Atamalar</div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Personel</th><th>Eğitim</th><th>Atanma</th><th>Durum</th></tr></thead><tbody>
@forelse($recentAssignments as $assignment)<tr><td><div class="fw-medium">{{ $assignment->employee_name }}</div><small class="text-secondary">{{ $assignment->employee_department }}</small></td><td>{{ $assignment->training->name }}</td><td>{{ $assignment->assigned_at->format('d.m.Y H:i') }}</td><td><span class="badge {{ $assignment->completed_at?'text-bg-success':'text-bg-warning' }}">{{ $assignment->completed_at?'Tamamlandı':'Bekliyor' }}</span></td></tr>@empty<tr><td colspan="4" class="text-center text-secondary py-5">Henüz eğitim ataması yok.</td></tr>@endforelse
</tbody></table></div></section>
@endsection
