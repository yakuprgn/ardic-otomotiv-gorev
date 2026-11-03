<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title') | Ardıç Eğitim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root{--ink:#192c43;--blue:#2859a5;--line:#e2eaf4;--light:#f4f7fc} body{color:var(--ink);background:var(--light);font-family:Inter,"Segoe UI",system-ui,sans-serif}.topbar{background:linear-gradient(115deg,#14283e,#274c83);color:#fff}.brand-badge{width:39px;height:39px;display:grid;place-items:center;background:#ffffff24;border-radius:12px}.nav-pills .nav-link{color:#dbe8fa}.nav-pills .nav-link.active,.nav-pills .nav-link:hover{color:#fff;background:#ffffff1f}.panel{background:#fff;border:1px solid var(--line);border-radius:16px;box-shadow:0 7px 30px #1a35590e}.metric{border-left:3px solid #477bc1}.small-label{font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;color:#667b94}.btn-primary{background:linear-gradient(110deg,#245baf,#2a7bc6);border:0}.form-control:focus,.form-select:focus{border-color:#729bdf;box-shadow:0 0 0 .2rem #2b6ec91f}.table>:not(caption)>*>*{padding:.8rem .75rem;vertical-align:middle;border-bottom-color:#e8eef6}.table thead th{background:#f5f8fd;color:#405776;font-size:.8rem}.badge-soft{background:#eaf3ff;color:#285a9b;border:1px solid #d8e7fd}.modal-content{border:0;border-radius:18px}.pagination{margin:0}@media(max-width:768px){.nav-pills{width:100%;overflow:auto;flex-wrap:nowrap}.nav-pills .nav-link{white-space:nowrap}}
    </style>
</head>
<body>
<header class="topbar py-3"><div class="container-fluid px-3 px-lg-4 d-flex flex-wrap align-items-center gap-3">
    <div class="brand-badge"><i class="bi bi-mortarboard-fill"></i></div><div><div class="fw-bold">ARDIÇ OTOMOTİV</div><div class="small opacity-75">Eğitim Portalı</div></div>
    <nav class="nav nav-pills ms-lg-auto gap-1"><a class="nav-link {{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}">Dashboard</a><a class="nav-link {{ request()->routeIs('trainings.*')?'active':'' }}" href="{{ route('trainings.index') }}">Eğitimler</a><a class="nav-link {{ request()->routeIs('assignments.*')?'active':'' }}" href="{{ route('assignments.index') }}">Atamalar</a></nav>
</div></header>
<main class="container-fluid px-3 px-lg-4 py-4">@yield('content')</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('form[data-busy]').forEach(f=>f.addEventListener('submit',()=>{const b=f.querySelector('[type=submit]');if(b){b.disabled=true;b.dataset.original=b.innerHTML;b.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>İşleniyor...'}}));
document.querySelectorAll('form[data-confirm]').forEach(f=>f.addEventListener('submit',async e=>{if(f.dataset.confirmed)return;e.preventDefault();const r=await Swal.fire({icon:'warning',title:f.dataset.confirm,showCancelButton:true,confirmButtonText:'Onayla',cancelButtonText:'Vazgeç',confirmButtonColor:'#c43e4b'});if(r.isConfirmed){f.dataset.confirmed='1';f.requestSubmit()}}));
@if(session('success')) Swal.fire({icon:'success',title:'İşlem başarılı',text:@json(session('success')),timer:2200,showConfirmButton:false}); @endif
@if(session('error')) Swal.fire({icon:'error',title:'İşlem tamamlanamadı',text:@json(session('error'))}); @endif
@if($errors->any()) Swal.fire({icon:'error',title:'Doğrulama başarısız',text:@json(implode("\n",$errors->all()))}); @endif
</script>@stack('scripts')
</body></html>
