
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Personel Yönetimi | Ardıç Otomotiv</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --ink: #192c43;
            --blue: #2859a5;
            --line: #e2eaf4;
            --light: #f4f7fc;
        }
        body {
            color: var(--ink);
            background: var(--light);
            font-family: Inter, "Segoe UI", system-ui, sans-serif;
        }
        .topbar {
            background: linear-gradient(115deg, #14283e, #274c83);
            color: white;
        }
        .brand-badge {
            width: 39px;
            height: 39px;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,.14);
            border-radius: 12px;
        }
        .panel {
            background: white;
            border: 1px solid var(--line);
            border-radius: 16px;
            box-shadow: 0 7px 30px rgba(26,53,89,.055);
        }
        .small-label {
            font-size: .75rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #667b94;
        }
        .btn-primary {
            background: linear-gradient(110deg, #245baf, #2a7bc6);
            border: 0;
            box-shadow: 0 5px 14px rgba(37,91,175,.14);
        }
        .btn-primary:hover {
            filter: brightness(1.07);
            transform: translateY(-1px);
        }
        .btn {
            transition: transform .15s ease, filter .15s ease;
        }
        .form-control, .form-select {
            border-color: #d8e2ee;
        }
        .form-control:focus, .form-select:focus {
            border-color: #729bdf;
            box-shadow: 0 0 0 .2rem rgba(43,110,201,.12);
        }
        .table {
            margin: 0;
            white-space: nowrap;
        }
        .table>:not(caption)>*>* {
            padding: .85rem .75rem;
            border-bottom-color: #e8eef6;
            vertical-align: middle;
        }
        .table thead th {
            background: #f5f8fd;
            color: #405776;
            font-size: .8rem;
            font-weight: 700;
        }
        .table tbody tr:hover {
            background: #f9fbff;
        }
        .table .filter-input {
            min-width: 135px;
            font-size: .8rem;
        }
        .avatar {
            display: inline-grid;
            place-items: center;
            width: 35px;
            height: 35px;
            border-radius: 10px;
            background: #e7f0ff;
            color: #295ba6;
            font-weight: 700;
        }
        .sort-link {
            color: inherit;
            text-decoration: none;
        }
        .sort-link:hover {
            color: #245baf;
        }
        .dept {
            background: #eaf3ff;
            color: #285a9b;
            border: 1px solid #d8e7fd;
        }
        .top-scroll {
            overflow-x: auto;
            overflow-y: hidden;
            height: 17px;
        }
        .top-scroll-content {
            height: 1px;
        }
        .table-scroll {
            overflow: auto;
        }
        .table-scroll table {
            min-width: 1050px;
        }
        .pagination {
            margin: 0;
        }
        .page-link {
            color: #2a5c9f;
        }
        .card-metric {
            border-left: 3px solid #477bc1;
        }
        .modal-content {
            border: 0;
            border-radius: 18px;
            overflow: hidden;
        }
        .modal-header {
            background: #f5f8fd;
        }
        @media(max-width:768px) {
            .toolbar {
                align-items: stretch!important;
            }
            .toolbar form {
                width: 100%;
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <header class="topbar py-3">
        <div class="container-fluid px-3 px-lg-4 d-flex align-items-center gap-3">
            <div class="brand-badge">
                <i class="bi bi-people-fill fs-5"></i>
            </div>
            <div>
                <div class="fw-bold lh-sm">ARDIÇ OTOMOTİV</div>
                <div class="small opacity-75">Personel Yönetim Sistemi</div>
            </div>
            <span class="ms-auto badge rounded-pill bg-light text-dark px-3 py-2">
                Personnel Service
            </span>
        </div>
    </header>

    <main class="container-fluid px-3 px-lg-4 py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h1 class="h4 fw-bold mb-1">Personel Listesi</h1>
                <p class="text-secondary small mb-0">
                    Personel kayıtlarını yönetin ve eğitim portalıyla güvenli şekilde paylaşın.
                </p>
            </div>
            <button type="button" class="btn btn-primary px-3" id="add-employee">
                <i class="bi bi-person-plus me-1"></i> Yeni Personel
            </button>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="panel card-metric px-3 py-2">
                    <div class="small-label">Toplam personel</div>
                    <div class="fs-4 fw-bold">
                        {{ number_format($totalCount, 0, ',', '.') }}
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="panel card-metric px-3 py-2">
                    <div class="small-label">Filtrelenen kayıt</div>
                    <div class="fs-4 fw-bold">
                        {{ number_format($employees->total(), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <section class="panel overflow-hidden">
            <div class="toolbar px-3 py-3 d-flex align-items-center justify-content-between flex-wrap gap-2 border-bottom">
                <div class="fw-semibold">
                    <i class="bi bi-table me-2 text-primary"></i>Personel Kayıtları
                </div>

                <form id="filter-form"
                      class="d-flex align-items-center gap-2"
                      action="{{ route('employees.index') }}"
                      method="GET">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">

                    <label for="per-page" class="small text-secondary mb-0">
                        Sayfa boyutu
                    </label>

                    <select id="per-page"
                            name="per_page"
                            class="form-select form-select-sm"
                            style="width:80px"
                            aria-label="Sayfa boyutu">
                        @foreach([10,25,50] as $size)
                            <option value="{{ $size }}" @selected($perPage === $size)>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-funnel me-1"></i>Uygula
                    </button>

                    <a class="btn btn-sm btn-outline-secondary"
                       href="{{ route('employees.index') }}">
                        Temizle
                    </a>
                </form>
            </div>

            <div class="px-3 pt-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="small text-secondary">
                    {{ $employees->firstItem() ?? 0 }}–{{ $employees->lastItem() ?? 0 }}
                    / {{ $employees->total() }} kayıt
                </span>
                <div class="small">
                    {{ $employees->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>

            <div class="top-scroll" id="top-scroll" aria-label="Üst yatay kaydırma">
                <div class="top-scroll-content" id="top-scroll-content"></div>
            </div>

            <div class="table-scroll" id="table-scroll">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">İşlemler</th>

                            @foreach(['first_name' => 'Ad', 'last_name' => 'Soyad', 'email' => 'E-posta', 'department' => 'Departman'] as $field => $label)
                                <th scope="col">
                                    <a class="sort-link"
                                       href="{{ request()->fullUrlWithQuery(['sort' => $field, 'direction' => $sort === $field && $direction === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">
                                        {{ $label }}
                                        <i class="bi bi-arrow-down-up ms-1"></i>
                                    </a>
                                </th>
                            @endforeach

                            <th scope="col">
                                <a class="sort-link"
                                   href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => $sort === 'created_at' && $direction === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">
                                    Oluşturulma
                                    <i class="bi bi-arrow-down-up"></i>
                                </a>
                            </th>
                        </tr>
                        <tr>
                            <th class="text-secondary small">Filtreler</th>

                            @foreach(['first_name' => 'Ad ara', 'last_name' => 'Soyad ara', 'email' => 'E-posta ara', 'department' => 'Departman ara'] as $field => $placeholder)
                                <th>
                                    <input form="filter-form"
                                           type="search"
                                           class="form-control form-control-sm filter-input"
                                           name="{{ $field }}"
                                           value="{{ request()->query($field, '') }}"
                                           placeholder="{{ $placeholder }}"
                                           aria-label="{{ $placeholder }}">
                                </th>
                            @endforeach

                            <th class="text-secondary small">—</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary edit-employee"
                                                title="Düzenle"
                                                aria-label="{{ $employee->first_name }} personelini düzenle"
                                                data-id="{{ $employee->id }}"
                                                data-first-name="{{ $employee->first_name }}"
                                                data-last-name="{{ $employee->last_name }}"
                                                data-email="{{ $employee->email }}"
                                                data-department="{{ $employee->department }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <form class="delete-employee"
                                              method="POST"
                                              action="{{ route('employees.destroy', $employee) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Sil"
                                                    aria-label="{{ $employee->first_name }} personelini sil">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <td>
                                    <span class="avatar me-2">
                                        {{ mb_strtoupper(mb_substr($employee->first_name, 0, 1)) }}
                                    </span>
                                    <span class="fw-medium">{{ $employee->first_name }}</span>
                                </td>
                                <td>{{ $employee->last_name }}</td>
                                <td>
                                    <a class="text-decoration-none"
                                       href="mailto:{{ $employee->email }}">
                                        {{ $employee->email }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge dept fw-medium">
                                        {{ $employee->department }}
                                    </span>
                                </td>
                                <td class="text-secondary small">
                                    {{ $employee->created_at?->format('d.m.Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-secondary">
                                    <i class="bi bi-people fs-3 d-block mb-2"></i>
                                    Henüz eşleşen personel kaydı yok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-3 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="small text-secondary">
                    Veriler sunucu tarafında filtrelenir ve sayfalanır.
                </span>
                <div>
                    {{ $employees->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </section>
    </main>

    <div class="modal fade" id="employee-modal" tabindex="-1"
         aria-labelledby="employee-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5 fw-bold" id="employee-modal-title">
                        Yeni Personel
                    </h2>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>

                <form id="employee-form"
                      method="POST"
                      action="{{ route('employees.store') }}">
                    @csrf

                    <input type="hidden" name="_method" id="employee-method"
                           value="PUT" disabled>
                    <input type="hidden" name="employee_id" id="employee-id">

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="first-name">Ad *</label>
                                <input id="first-name" name="first_name"
                                       class="form-control" maxlength="100"
                                       required autocomplete="given-name">
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label" for="last-name">Soyad *</label>
                                <input id="last-name" name="last_name"
                                       class="form-control" maxlength="100"
                                       required autocomplete="family-name">
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="email">E-posta *</label>
                                <input id="email" name="email" type="email"
                                       class="form-control" maxlength="255"
                                       required autocomplete="email">
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="department">Departman *</label>
                                <input id="department" name="department"
                                       class="form-control" maxlength="120" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border"
                                data-bs-dismiss="modal">
                            Vazgeç
                        </button>
                        <button type="submit" class="btn btn-primary" id="employee-submit">
                            <i class="bi bi-check-circle me-1"></i> Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const modalElement = document.getElementById('employee-modal');
        const employeeModal = bootstrap.Modal.getOrCreateInstance(modalElement);
        const form = document.getElementById('employee-form');
        const methodInput = document.getElementById('employee-method');
        const submitButton = document.getElementById('employee-submit');
        const baseUrl = @json(url('/employees'));

        function openEmployeeForm(data = null) {
            form.reset();

            const editing = Boolean(data && data.id);
            methodInput.disabled = !editing;

            document.getElementById('employee-id').value = editing ? data.id : '';

            form.action = editing
                ? baseUrl + '/' + encodeURIComponent(data.id)
                : @json(route('employees.store'));

            document.getElementById('employee-modal-title').textContent =
                editing ? 'Personel Düzenle' : 'Yeni Personel';

            document.getElementById('first-name').value = data?.firstName ?? '';
            document.getElementById('last-name').value = data?.lastName ?? '';
            document.getElementById('email').value = data?.email ?? '';
            document.getElementById('department').value = data?.department ?? '';

            submitButton.disabled = false;
            employeeModal.show();
        }

        document.getElementById('add-employee').addEventListener('click', () => {
            openEmployeeForm();
        });

        document.querySelectorAll('.edit-employee').forEach(button => {
            button.addEventListener('click', () => {
                openEmployeeForm(button.dataset);
            });
        });

        form.addEventListener('submit', () => {
            submitButton.disabled = true;
            submitButton.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Kaydediliyor...';
        });

        document.querySelectorAll('.delete-employee').forEach(deleteForm => {
            deleteForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Personel silinsin mi?',
                    text: 'Kayıt pasife alınacak ve aktif personel API listesinde görünmeyecek.',
                    showCancelButton: true,
                    confirmButtonText: 'Evet, sil',
                    cancelButtonText: 'Vazgeç',
                    confirmButtonColor: '#c43e4b'
                });

                if (result.isConfirmed) {
                    deleteForm.submit();
                }
            });
        });

        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'İşlem başarılı',
            text: @json(session('success')),
            timer: 2300,
            showConfirmButton: false
        });
        @endif

        @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Doğrulama başarısız',
            text: @json(implode("\n", $errors->all()))
        });

        openEmployeeForm({
            id: @json(old('employee_id')),
            firstName: @json(old('first_name')),
            lastName: @json(old('last_name')),
            email: @json(old('email')),
            department: @json(old('department'))
        });
        @endif

        const upper = document.getElementById('top-scroll');
        const lower = document.getElementById('table-scroll');
        const spacer = document.getElementById('top-scroll-content');

        function syncWidth() {
            spacer.style.width = lower.scrollWidth + 'px';
        }

        upper.addEventListener('scroll', () => {
            if (lower.scrollLeft !== upper.scrollLeft) {
                lower.scrollLeft = upper.scrollLeft;
            }
        });

        lower.addEventListener('scroll', () => {
            if (upper.scrollLeft !== lower.scrollLeft) {
                upper.scrollLeft = lower.scrollLeft;
            }
        });

        if ('ResizeObserver' in window) {
            new ResizeObserver(syncWidth).observe(lower.querySelector('table'));
        }

        window.addEventListener('resize', syncWidth);
        syncWidth();
    </script>
</body>
</html>
