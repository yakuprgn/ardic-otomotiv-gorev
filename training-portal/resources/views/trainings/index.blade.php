
@extends('layouts.app')

@section('title', 'Eğitimler')

@section('content')

<div class="d-flex flex-wrap justify-content-between gap-3 mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Eğitim Yönetimi</h1>
        <p class="text-secondary mb-0">
            Eğitim kataloğunu arayın, filtreleyin ve yönetin.
        </p>
    </div>

    <button
        class="btn btn-primary px-3"
        type="button"
        data-bs-toggle="modal"
        data-bs-target="#trainingModal"
        id="addTraining"
    >
        <i class="bi bi-plus-circle me-1"></i>
        Yeni Eğitim
    </button>
</div>

<section class="panel overflow-hidden" data-dynamic-table>
    <form
        action="{{ route('trainings.index') }}"
        method="GET"
        data-filter-form
    >
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="direction" value="{{ $direction }}">

        <div class="toolbar p-3 border-bottom d-flex flex-wrap gap-2 align-items-center">
            <div class="input-group" style="max-width:360px">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>

                <input
                    class="form-control"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Tüm alanlarda ara…"
                    data-filter
                >
            </div>

            <select
                class="form-select"
                style="width:100px"
                name="per_page"
                data-filter
            >
                @foreach([10,25,50,100] as $n)
                    <option value="{{ $n }}" @selected($perPage === $n)>
                        {{ $n }}
                    </option>
                @endforeach
            </select>

            <a
                class="btn btn-light border"
                href="{{ route('trainings.index') }}"
                data-table-link
            >
                <i class="bi bi-x-circle me-1"></i>
                Temizle
            </a>

            <div class="dropdown ms-lg-auto">
                <button
                    class="btn btn-success dropdown-toggle"
                    type="button"
                    data-bs-toggle="dropdown"
                >
                    <i class="bi bi-file-earmark-excel me-1"></i>
                    Excel Aktar
                </button>

                <ul class="dropdown-menu">
                    <li>
                        <button
                            class="dropdown-item"
                            type="button"
                            data-export="{{ route('trainings.export') }}"
                            data-scope="page"
                        >
                            Bu sayfa
                        </button>
                    </li>

                    <li>
                        <button
                            class="dropdown-item"
                            type="button"
                            data-export="{{ route('trainings.export') }}"
                            data-scope="filtered"
                        >
                            Tüm filtrelenenler
                        </button>
                    </li>

                    <li>
                        <button
                            class="dropdown-item"
                            type="button"
                            data-export="{{ route('trainings.export') }}"
                            data-scope="selected"
                        >
                            Seçili kayıtlar
                            (<span data-selected-count>0</span>)
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <div data-scroll-pair>
            <div class="top-scroll">
                <div></div>
            </div>

            <div class="table-scroll">
                <table class="table mb-0" style="min-width:1050px">
                    <thead>
                        <tr>
                            <th>
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    data-check-all
                                >
                            </th>

                            <th>İşlemler</th>

                            @foreach([
                                'name' => 'Eğitim',
                                'duration_minutes' => 'Süre',
                                'created_at' => 'Oluşturulma'
                            ] as $field => $label)
                                <th>
                                    <a
                                        class="sort-link"
                                        data-table-link
                                        href="{{ request()->fullUrlWithQuery([
                                            'sort' => $field,
                                            'direction' => $sort === $field && $direction === 'asc' ? 'desc' : 'asc',
                                            'page' => 1
                                        ]) }}"
                                    >
                                        {{ $label }}
                                        <i class="bi bi-arrow-down-up"></i>
                                    </a>
                                </th>
                            @endforeach

                            <th>Atama</th>
                        </tr>

                        <tr>
                            <th></th>

                            <th class="small text-secondary">
                                Filtreler
                            </th>

                            <th>
                                <input
                                    class="form-control table-filter"
                                    name="name"
                                    value="{{ request('name') }}"
                                    placeholder="Eğitim adı"
                                    data-filter
                                >
                            </th>

                            <th>
                                <div class="d-flex gap-1">
                                    <input
                                        class="form-control table-filter"
                                        type="number"
                                        name="duration_min"
                                        value="{{ request('duration_min') }}"
                                        placeholder="Min"
                                        data-filter
                                    >

                                    <input
                                        class="form-control table-filter"
                                        type="number"
                                        name="duration_max"
                                        value="{{ request('duration_max') }}"
                                        placeholder="Max"
                                        data-filter
                                    >
                                </div>
                            </th>

                            <th>
                                <div class="d-flex gap-1">
                                    <input
                                        class="form-control table-filter"
                                        type="date"
                                        name="created_from"
                                        value="{{ request('created_from') }}"
                                        data-filter
                                    >

                                    <input
                                        class="form-control table-filter"
                                        type="date"
                                        name="created_to"
                                        value="{{ request('created_to') }}"
                                        data-filter
                                    >
                                </div>
                            </th>

                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($trainings as $t)
                            <tr>
                                <td>
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        value="{{ $t->id }}"
                                        data-row-check
                                    >
                                </td>

                                <td>
                                    <div class="d-flex gap-1">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary edit-training"
                                            data-id="{{ $t->id }}"
                                            data-name="{{ $t->name }}"
                                            data-description="{{ $t->description }}"
                                            data-duration-minutes="{{ $t->duration_minutes }}"
                                            title="Eğitimi düzenle"
                                            aria-label="Eğitimi düzenle"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-danger delete-training"
                                            data-delete-url="{{ route('trainings.destroy', $t) }}"
                                            title="Eğitimi sil"
                                            aria-label="Eğitimi sil"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $t->name }}
                                    </div>

                                    <small class="text-secondary">
                                        {{ Str::limit($t->description,85) }}
                                    </small>
                                </td>

                                <td>
                                    {{ $t->duration_minutes }} dk
                                </td>

                                <td>
                                    {{ $t->created_at->format('d.m.Y') }}
                                </td>

                                <td>
                                    <span class="badge badge-soft">
                                        {{ $t->assignments_count }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty">
                                    <i class="bi bi-search fs-2 d-block mb-2"></i>
                                    Filtrelerle eşleşen eğitim yok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="p-3 border-top d-flex flex-wrap justify-content-between gap-2">
            <small class="text-secondary">
                {{ $trainings->firstItem() ?? 0 }}
                –
                {{ $trainings->lastItem() ?? 0 }}
                /
                {{ $trainings->total() }}
                kayıt
            </small>

            {{ $trainings->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    </form>
</section>

{{-- Filtre formundan tamamen bağımsız DELETE formu --}}
<form
    id="deleteTrainingForm"
    method="POST"
    action="{{ route('trainings.index') }}"
    data-confirm="Eğitim silinsin mi?"
    hidden
>
    @csrf
    @method('DELETE')
</form>

<div
    class="modal fade"
    id="trainingModal"
    tabindex="-1"
    aria-labelledby="trainingTitle"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form
                method="POST"
                action="{{ route('trainings.store') }}"
                id="trainingForm"
                data-busy
            >
                @csrf

                <input
                    type="hidden"
                    name="_method"
                    id="trainingMethod"
                    value="PUT"
                    disabled
                >

                <div class="modal-header">
                    <h2 class="modal-title fs-5">
                        <i class="bi bi-journal-plus me-2"></i>
                        <span id="trainingTitle">Yeni Eğitim</span>
                    </h2>

                    <button
                        class="btn-close btn-close-white"
                        type="button"
                        data-bs-dismiss="modal"
                        aria-label="Kapat"
                    ></button>
                </div>

                <div class="modal-body p-4">
                    <label class="form-label" for="trainingName">
                        Eğitim adı *
                    </label>

                    <input
                        class="form-control mb-3"
                        name="name"
                        id="trainingName"
                        maxlength="160"
                        required
                    >

                    <label class="form-label" for="trainingDescription">
                        Açıklama
                    </label>

                    <textarea
                        class="form-control mb-3"
                        name="description"
                        id="trainingDescription"
                        rows="4"
                        maxlength="2000"
                    ></textarea>

                    <label class="form-label" for="trainingDuration">
                        Süre (dakika) *
                    </label>

                    <input
                        class="form-control"
                        type="number"
                        min="1"
                        max="10080"
                        name="duration_minutes"
                        id="trainingDuration"
                        required
                    >
                </div>

                <div class="modal-footer">
                    <button
                        class="btn btn-light"
                        type="button"
                        data-bs-dismiss="modal"
                    >
                        Vazgeç
                    </button>

                    <button class="btn btn-primary" type="submit">
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const tm = bootstrap.Modal.getOrCreateInstance('#trainingModal');
const tf = document.getElementById('trainingForm');
const method = document.getElementById('trainingMethod');
const deleteTrainingForm = document.getElementById('deleteTrainingForm');

function fill(t = null) {
    tf.reset();

    method.disabled = !t;

    tf.action = t
        ? @json(url('/trainings')) + '/' + encodeURIComponent(t.id)
        : @json(route('trainings.store'));

    document.getElementById('trainingTitle').textContent =
        t ? 'Eğitimi Düzenle' : 'Yeni Eğitim';

    document.getElementById('trainingName').value = t?.name ?? '';
    document.getElementById('trainingDescription').value = t?.description ?? '';
    document.getElementById('trainingDuration').value = t?.duration_minutes ?? '';
}

document.getElementById('addTraining').addEventListener('click', () => {
    fill();
});

document.addEventListener('click', event => {
    const editButton = event.target.closest('.edit-training');

    if (editButton) {
        fill({
            id: editButton.dataset.id,
            name: editButton.dataset.name,
            description: editButton.dataset.description,
            duration_minutes: editButton.dataset.durationMinutes
        });

        tm.show();
        return;
    }

    const deleteButton = event.target.closest('.delete-training');

    if (!deleteButton) return;

    event.preventDefault();

    const targetUrl = new URL(
        deleteButton.dataset.deleteUrl,
        window.location.origin
    );

    if (targetUrl.origin !== window.location.origin) {
        Swal.fire({
            icon: 'error',
            title: 'Geçersiz işlem adresi',
            text: 'Silme işlemi güvenlik nedeniyle başlatılmadı.'
        });
        return;
    }

    deleteTrainingForm.action = targetUrl.href;
    deleteTrainingForm.requestSubmit();
});
</script>
@endpush
