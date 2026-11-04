
@extends('layouts.app')

@section('title', 'Atamalar')

@section('content')
<div class="d-flex flex-wrap justify-content-between gap-3 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Eğitim Atamaları</h1>
        <p class="text-secondary mb-0">
            Atamaları filtreleyin, tamamlanmayı izleyin ve dışa aktarın.
        </p>
    </div>

    <button
        type="button"
        class="btn btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#assignmentModal"
        @disabled($serviceError || $employees === [] || $trainings->isEmpty())
    >
        <i class="bi bi-person-plus me-1"></i>
        Yeni Atama
    </button>
</div>

@if($serviceError)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Personel servisi kullanılamıyor.</strong>
        {{ $serviceError }}
        Geçmiş kayıtlar korunuyor.
    </div>
@endif

<section class="panel overflow-hidden" data-dynamic-table>

    {{-- Filtre formu yalnızca araç çubuğunu kapsar. --}}
    <form
        id="assignmentFilters"
        action="{{ route('assignments.index') }}"
        method="GET"
        data-filter-form
    >
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="direction" value="{{ $direction }}">
        <input type="hidden" name="status" value="{{ $status }}">

        <div class="toolbar p-3 border-bottom d-flex flex-wrap gap-2 align-items-center">
            <div class="btn-group">
                <a
                    data-table-link
                    class="btn {{ $status === 'pending' ? 'btn-primary' : 'btn-light border' }}"
                    href="{{ route('assignments.index', ['status' => 'pending']) }}"
                >
                    <i class="bi bi-hourglass me-1"></i>
                    Bekleyen
                </a>

                <a
                    data-table-link
                    class="btn {{ $status === 'completed' ? 'btn-primary' : 'btn-light border' }}"
                    href="{{ route('assignments.index', ['status' => 'completed']) }}"
                >
                    <i class="bi bi-check2-circle me-1"></i>
                    Tamamlanan
                </a>
            </div>

            <div class="input-group" style="max-width:320px">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>

                <input
                    class="form-control"
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Personel veya eğitim ara…"
                    aria-label="Personel veya eğitim ara"
                    data-filter
                >
            </div>

            <select
                class="form-select"
                style="width:95px"
                name="per_page"
                aria-label="Sayfa başına kayıt sayısı"
                data-filter
            >
                @foreach([10, 25, 50, 100] as $n)
                    <option
                        value="{{ $n }}"
                        @selected((int) $perPage === $n)
                    >
                        {{ $n }}
                    </option>
                @endforeach
            </select>

            <a
                class="btn btn-light border"
                data-table-link
                href="{{ route('assignments.index', ['status' => $status]) }}"
            >
                Temizle
            </a>

            <div class="dropdown ms-lg-auto">
                <button
                    class="btn btn-success dropdown-toggle"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    <i class="bi bi-file-earmark-excel me-1"></i>
                    Excel
                </button>

                <ul class="dropdown-menu">
                    <li>
                        <button
                            class="dropdown-item"
                            type="button"
                            data-export="{{ route('assignments.export') }}"
                            data-scope="page"
                        >
                            Bu sayfa
                        </button>
                    </li>

                    <li>
                        <button
                            class="dropdown-item"
                            type="button"
                            data-export="{{ route('assignments.export') }}"
                            data-scope="filtered"
                        >
                            Tüm filtrelenenler
                        </button>
                    </li>

                    <li>
                        <button
                            class="dropdown-item"
                            type="button"
                            data-export="{{ route('assignments.export') }}"
                            data-scope="selected"
                        >
                            Seçilenler
                            (<span data-selected-count>0</span>)
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </form>

    <div data-scroll-pair>
        <div class="top-scroll">
            <div></div>
        </div>

        <div class="table-scroll">
            <table class="table mb-0" style="min-width:1200px">
                <thead>
                    <tr>
                        <th>
                            <input
                                class="form-check-input"
                                type="checkbox"
                                data-check-all
                                aria-label="Bu sayfadaki kayıtları seç"
                            >
                        </th>

                        <th>İşlem</th>

                        <th>
                            <a
                                class="sort-link"
                                data-table-link
                                href="{{ request()->fullUrlWithQuery([
                                    'sort' => 'employee_name',
                                    'direction' => $sort === 'employee_name' && $direction === 'asc' ? 'desc' : 'asc',
                                    'page' => 1
                                ]) }}"
                            >
                                Personel
                                <i class="bi bi-arrow-down-up"></i>
                            </a>
                        </th>

                        <th>Eğitim</th>

                        <th>
                            <a
                                class="sort-link"
                                data-table-link
                                href="{{ request()->fullUrlWithQuery([
                                    'sort' => 'assigned_at',
                                    'direction' => $sort === 'assigned_at' && $direction === 'asc' ? 'desc' : 'asc',
                                    'page' => 1
                                ]) }}"
                            >
                                Atanma
                                <i class="bi bi-arrow-down-up"></i>
                            </a>
                        </th>

                        <th>Tamamlanma</th>
                    </tr>

                    <tr>
                        <th></th>
                        <th class="small text-secondary">Filtreler</th>

                        <th>
                            <input
                                form="assignmentFilters"
                                class="form-control table-filter"
                                name="employee_name"
                                value="{{ request('employee_name') }}"
                                placeholder="Personel adı"
                                aria-label="Personel adına göre filtrele"
                                data-filter
                            >
                        </th>

                        <th>
                            <input
                                form="assignmentFilters"
                                class="form-control table-filter"
                                name="training_name"
                                value="{{ request('training_name') }}"
                                placeholder="Eğitim adı"
                                aria-label="Eğitim adına göre filtrele"
                                data-filter
                            >
                        </th>

                        <th>
                            <div class="d-flex gap-1">
                                <input
                                    form="assignmentFilters"
                                    class="form-control table-filter"
                                    type="date"
                                    name="assigned_from"
                                    value="{{ request('assigned_from') }}"
                                    aria-label="Atanma başlangıç tarihi"
                                    data-filter
                                >

                                <input
                                    form="assignmentFilters"
                                    class="form-control table-filter"
                                    type="date"
                                    name="assigned_to"
                                    value="{{ request('assigned_to') }}"
                                    aria-label="Atanma bitiş tarihi"
                                    data-filter
                                >
                            </div>
                        </th>

                        <th>
                            <div class="d-flex gap-1">
                                <input
                                    form="assignmentFilters"
                                    class="form-control table-filter"
                                    type="date"
                                    name="completed_from"
                                    value="{{ request('completed_from') }}"
                                    aria-label="Tamamlanma başlangıç tarihi"
                                    data-filter
                                >

                                <input
                                    form="assignmentFilters"
                                    class="form-control table-filter"
                                    type="date"
                                    name="completed_to"
                                    value="{{ request('completed_to') }}"
                                    aria-label="Tamamlanma bitiş tarihi"
                                    data-filter
                                >
                            </div>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($assignments as $a)
                        <tr>
                            <td>
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    value="{{ $a->id }}"
                                    aria-label="{{ $a->employee_name }} atamasını seç"
                                    data-row-check
                                >
                            </td>

                            <td>
                                @if(!$a->completed_at)
                                    <form
                                        method="POST"
                                        action="{{ route('assignments.complete', $a) }}"
                                        data-busy
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            class="btn btn-sm btn-success"
                                            type="submit"
                                        >
                                            <i class="bi bi-check-circle me-1"></i>
                                            Tamamla
                                        </button>
                                    </form>
                                @else
                                    <span class="badge text-bg-success">
                                        Tamamlandı
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ $a->employee_name }}
                                </div>

                                <small class="text-secondary">
                                    {{ $a->employee_email }}
                                    ·
                                    {{ $a->employee_department }}
                                </small>
                            </td>

                            <td>
                                {{ $a->training->name }}
                            </td>

                            <td>
                                {{ $a->assigned_at->format('d.m.Y H:i') }}
                            </td>

                            <td>
                                {{ $a->completed_at?->format('d.m.Y H:i') ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Filtrelerle eşleşen atama yok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="p-3 border-top d-flex flex-wrap justify-content-between gap-2">
        <small class="text-secondary">
            {{ $assignments->firstItem() ?? 0 }}
            –
            {{ $assignments->lastItem() ?? 0 }}
            /
            {{ $assignments->total() }}
            kayıt
        </small>

        {{ $assignments->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
</section>

{{-- Yeni atama formu tablo ve filtre formundan bağımsızdır. --}}
<div
    class="modal fade"
    id="assignmentModal"
    tabindex="-1"
    aria-labelledby="assignmentModalTitle"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form
                id="createAssignmentForm"
                method="POST"
                action="{{ route('assignments.store') }}"
                data-busy
            >
                @csrf

                <div class="modal-header">
                    <h2
                        class="modal-title fs-5"
                        id="assignmentModalTitle"
                    >
                        <i class="bi bi-person-plus me-2"></i>
                        Yeni Eğitim Ataması
                    </h2>

                    <button
                        class="btn-close btn-close-white"
                        type="button"
                        data-bs-dismiss="modal"
                        aria-label="Kapat"
                    ></button>
                </div>

                <div class="modal-body p-4">
                    <label
                        class="form-label"
                        for="assignmentEmployee"
                    >
                        Personel *
                    </label>

                    <select
                        class="form-select mb-3"
                        id="assignmentEmployee"
                        name="employee_id"
                        required
                    >
                        <option value="">Seçin</option>

                        @foreach($employees as $e)
                            <option
                                value="{{ $e['id'] }}"
                                @selected(old('employee_id') == $e['id'])
                            >
                                {{ $e['first_name'] }}
                                {{ $e['last_name'] }}
                                —
                                {{ $e['department'] }}
                            </option>
                        @endforeach
                    </select>

                    <label
                        class="form-label"
                        for="assignmentTraining"
                    >
                        Eğitim *
                    </label>

                    <select
                        class="form-select"
                        id="assignmentTraining"
                        name="training_id"
                        required
                    >
                        <option value="">Seçin</option>

                        @foreach($trainings as $t)
                            <option
                                value="{{ $t->id }}"
                                @selected(old('training_id') == $t->id)
                            >
                                {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-footer">
                    <button
                        class="btn btn-light"
                        type="button"
                        data-bs-dismiss="modal"
                    >
                        Vazgeç
                    </button>

                    <button
                        class="btn btn-primary"
                        type="submit"
                        form="createAssignmentForm"
                    >
                        <i class="bi bi-check2-circle me-1"></i>
                        Eğitimi Ata
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
