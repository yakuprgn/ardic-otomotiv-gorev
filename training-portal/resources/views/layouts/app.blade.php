
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title') | Ardıç Eğitim</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
:root{--ink:#17203b;--muted:#66718a;--line:#e5e8f2;--violet:#5b42dd;--blue:#2879ed}
*{box-sizing:border-box}
body{color:var(--ink);background:radial-gradient(circle at 95% 0,#ddd8ff66,transparent 25%),#f4f6fb;font-family:Inter,"Segoe UI",system-ui,sans-serif;min-height:100vh}
.topbar{position:sticky;top:0;z-index:1020;color:#fff;background:linear-gradient(105deg,#24205f,#4830b6 55%,#167aa1);box-shadow:0 10px 30px #241a7040}
.brand-badge{width:42px;height:42px;display:grid;place-items:center;background:#ffffff24;border:1px solid #ffffff2c;border-radius:14px}
.nav-pills .nav-link{color:#e9e7ff;padding:.65rem 1rem;border-radius:12px;transition:.2s}
.nav-pills .nav-link.active,.nav-pills .nav-link:hover{color:#fff;background:#ffffff25;transform:translateY(-1px)}
.panel{background:#fff;border:1px solid var(--line);border-radius:20px;box-shadow:0 12px 40px #2920640d}
.metric{position:relative;overflow:hidden;transition:.2s}
.metric:hover{transform:translateY(-3px);box-shadow:0 18px 45px #29206418}
.small-label{font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);font-weight:700}
.btn{border-radius:10px;transition:.2s}
.btn-primary{background:linear-gradient(120deg,var(--violet),var(--blue));border:0;box-shadow:0 8px 20px #493ddc35}
.btn-primary:hover{transform:translateY(-1px);filter:brightness(1.08)}
.btn-success{background:linear-gradient(120deg,#058b73,#18b27d);border:0}
.btn-danger{background:linear-gradient(120deg,#c93350,#ec5960);border:0}
.form-control,.form-select{border-color:#dfe3ef;border-radius:10px}
.form-control:focus,.form-select:focus{border-color:#8174ed;box-shadow:0 0 0 .2rem #6554e31c}
.table{white-space:nowrap}
.table>:not(caption)>*>*{padding:.82rem .72rem;vertical-align:middle;border-bottom-color:#edf0f7}
.table thead th{background:#f7f7fd;color:#4d5870;font-size:.78rem}
.badge-soft{background:#ebe9ff;color:#4e3ac4;border:1px solid #dcd7ff}
.modal-content{border:0;border-radius:22px;overflow:hidden;box-shadow:0 30px 90px #1f174c4d}
.modal-header{color:#fff;background:linear-gradient(120deg,#4936c3,#277fea)}
.table-scroll{overflow:auto}
.top-scroll{height:14px;overflow-x:auto;overflow-y:hidden}
.top-scroll>div{height:1px}
.table-refreshing{opacity:.55;transition:opacity .15s ease}
.sort-link{text-decoration:none;color:inherit}
.empty{padding:3.5rem!important;text-align:center;color:var(--muted)}
.donut{--value:0;width:76px;height:76px;border-radius:50%;display:grid;place-items:center;background:conic-gradient(var(--accent,#604ce1) calc(var(--value)*1%),#e9eaf3 0);position:relative}
.donut:after{content:"";position:absolute;inset:9px;border-radius:50%;background:#fff}
.donut>*{z-index:1}
.pagination{margin:0}
.page-link{color:#503bc4}
.table-filter{min-width:125px;font-size:.78rem}
@media(max-width:768px){
    .nav-pills{order:3;width:100%;overflow:auto;flex-wrap:nowrap}
    .nav-pills .nav-link{white-space:nowrap}
    .toolbar>*{width:100%}
    .modal-dialog{margin:.75rem}
}
@media(prefers-reduced-motion:reduce){
    *,*:before,*:after{transition:none!important}
}
</style>
</head>
<body>
<header class="topbar py-3">
    <div class="container-fluid px-3 px-lg-4 d-flex flex-wrap align-items-center gap-3">
        <div class="brand-badge">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div>
            <div class="fw-bold">ARDIÇ OTOMOTİV</div>
            <div class="small opacity-75">Eğitim & Gelişim Portalı</div>
        </div>
        <nav class="nav nav-pills ms-lg-auto gap-1">
            <a class="nav-link {{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-grid me-1"></i>Dashboard
            </a>
            <a class="nav-link {{ request()->routeIs('trainings.*')?'active':'' }}" href="{{ route('trainings.index') }}">
                <i class="bi bi-journal-text me-1"></i>Eğitimler
            </a>
            <a class="nav-link {{ request()->routeIs('assignments.*')?'active':'' }}" href="{{ route('assignments.index') }}">
                <i class="bi bi-people me-1"></i>Atamalar
            </a>
        </nav>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-sm text-white" style="background:#ffffff20">
                <i class="bi bi-box-arrow-right me-1"></i>Çıkış
            </button>
        </form>
    </div>
</header>

<main class="container-fluid px-3 px-lg-4 py-4">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('submit',e=>{
    const f=e.target;
    if(f.matches('form[data-busy]')){
        const b=f.querySelector('[type=submit]');
        if(b){
            b.disabled=true;
            b.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>İşleniyor…';
        }
    }
});

document.addEventListener('submit',async e=>{
    const f=e.target;
    if(!f.matches('form[data-confirm]')||f.dataset.confirmed)return;
    e.preventDefault();
    const r=await Swal.fire({
        icon:'warning',
        title:f.dataset.confirm,
        showCancelButton:true,
        confirmButtonText:'Onayla',
        cancelButtonText:'Vazgeç'
    });
    if(r.isConfirmed){
        f.dataset.confirmed='1';
        f.requestSubmit();
    }
});

@if(session('success'))
Swal.fire({icon:'success',title:'İşlem başarılı',text:@json(session('success')),timer:1900,showConfirmButton:false});
@endif
@if(session('error'))
Swal.fire({icon:'error',title:'İşlem tamamlanamadı',text:@json(session('error'))});
@endif
@if($errors->any())
Swal.fire({icon:'error',title:'Doğrulama başarısız',text:@json(implode("\n",$errors->all()))});
@endif

(()=>{
    const allowed = new Set([
        'search','name','description','duration_min','duration_max',
        'created_from','created_to','status','employee_name',
        'training_name','assigned_from','assigned_to',
        'completed_from','completed_to','sort','direction','per_page'
    ]);

    let pending = null;
    let debounceTimer = null;
    let sessionWarningShown = false;

    const region = () => document.querySelector('[data-dynamic-table]');

    const controlsUrl = () => {
        const area = region();
        const form = area?.querySelector('[data-filter-form]');
        const url = new URL(
            form?.getAttribute('action') || location.pathname,
            location.origin
        );

        area?.querySelectorAll('input[name], select[name]').forEach(input => {
            if (!allowed.has(input.name) || input.disabled) return;
            const value = input.value.trim();
            if (value !== '') url.searchParams.set(input.name, value);
        });

        url.searchParams.delete('page');
        return url;
    };

    const sessionWarning = () => {
        if (sessionWarningShown) return;
        sessionWarningShown = true;

        Swal.fire({
            icon: 'warning',
            title: 'Oturum doğrulanamadı',
            text: 'Oturumunuz sona ermiş olabilir. Güvenlik nedeniyle yeniden giriş yapılması gerekiyor.',
            confirmButtonText: 'Giriş sayfasına git',
            showCancelButton: true,
            cancelButtonText: 'Bu sayfada kal'
        }).then(result => {
            sessionWarningShown = false;
            if (result.isConfirmed) {
                location.assign(@json(route('login')));
            }
        });
    };

    async function load(url, mode = 'navigate', historyMode = 'push') {
        const area = region();
        if (!area) return;

        pending?.abort();
        const request = new AbortController();
        pending = request;
        const requestUrl = new URL(url, location.origin);

        area.querySelector('tbody')?.classList.add('table-refreshing');

        try {
            const response = await fetch(requestUrl.href, {
                method: 'GET',
                credentials: 'same-origin',
                signal: request.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const redirectedToLogin =
                response.redirected &&
                new URL(response.url).pathname.endsWith('/login');

            if (
                response.status === 401 ||
                response.status === 419 ||
                redirectedToLogin
            ) {
                sessionWarning();
                return;
            }

            if (response.status === 429) {
                throw new Error('Çok sayıda istek gönderildi. Kısa süre sonra tekrar deneyin.');
            }

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const html = await response.text();
            const freshDocument = new DOMParser().parseFromString(
                html,
                'text/html'
            );
            const fresh = freshDocument.querySelector('[data-dynamic-table]');

            if (!fresh) {
                throw new Error('Beklenmeyen sunucu yanıtı.');
            }

            if (mode === 'filter') {
                // Kullanıcı yeni karakter yazdıysa eski yanıtı kullanma.
                if (controlsUrl().href !== requestUrl.href) return;

                const oldBody = area.querySelector('.table-scroll tbody');
                const newBody = fresh.querySelector('.table-scroll tbody');
                const oldFooter = area.querySelector('.p-3.border-top');
                const newFooter = fresh.querySelector('.p-3.border-top');

                if (!oldBody || !newBody || !oldFooter || !newFooter) {
                    throw new Error('Tablo yapısı değişmiş.');
                }

                // Filtre input'ları ve focus korunur.
                oldBody.replaceWith(newBody);
                oldFooter.replaceWith(newFooter);

                // Filtre değiştikten sonra sütun sıralama linkleri güncel kalır.
                const sortLinks = area.querySelectorAll('a.sort, a.sort-link');
                const freshSortLinks = fresh.querySelectorAll('a.sort, a.sort-link');

                sortLinks.forEach((link, i) => {
                    if (freshSortLinks[i]) link.href = freshSortLinks[i].href;
                });

                const checkAll = area.querySelector('[data-check-all]');
                if (checkAll) checkAll.checked = false;

                area.querySelectorAll('[data-selected-count]').forEach(
                    node => node.textContent = '0'
                );
            } else {
                // Sayfa/tab/sıralama geçişlerinde tam tablo bölümü yenilenebilir.
                area.replaceWith(fresh);
            }

            if (historyMode === 'push') {
                history.pushState({}, '', requestUrl.href);
            }
            if (historyMode === 'replace') {
                history.replaceState({}, '', requestUrl.href);
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                Swal.fire({
                    icon: 'error',
                    title: 'Veriler yüklenemedi',
                    text: error.message || 'Bağlantıyı kontrol edin.'
                });
            }
        } finally {
            if (pending === request) {
                region()?.querySelector('tbody')?.classList.remove('table-refreshing');
                pending = null;
            }
        }
    }

    const filterNow = () => load(
        controlsUrl().href,
        'filter',
        'replace'
    );

    document.addEventListener('submit', event => {
        if (!event.target.matches('[data-filter-form]')) return;
        event.preventDefault();
        clearTimeout(debounceTimer);
        filterNow();
    });

    document.addEventListener('input', event => {
        if (!event.target.matches(
            '[data-dynamic-table] input[data-filter]:not([type="date"])'
        )) return;

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(filterNow, 350);
    });

    document.addEventListener('change', event => {
        if (!event.target.matches(
            '[data-dynamic-table] select[data-filter], [data-dynamic-table] input[type="date"][data-filter]'
        )) return;

        clearTimeout(debounceTimer);
        filterNow();
    });

    document.addEventListener('click', event => {
        const link = event.target.closest(
            '[data-dynamic-table] a[data-table-link], [data-dynamic-table] .pagination a'
        );
        if (!link) return;

        event.preventDefault();
        clearTimeout(debounceTimer);
        load(link.href, 'navigate', 'push');
    });

    addEventListener('popstate', () => {
        clearTimeout(debounceTimer);
        load(location.href, 'navigate', 'none');
    });

    document.addEventListener('change', event => {
        if (event.target.matches('[data-check-all]')) {
            region()?.querySelectorAll('[data-row-check]').forEach(
                box => box.checked = event.target.checked
            );
        }

        if (event.target.matches('[data-check-all], [data-row-check]')) {
            const selected =
                region()?.querySelectorAll('[data-row-check]:checked').length || 0;

            region()?.querySelectorAll('[data-selected-count]').forEach(
                node => node.textContent = String(selected)
            );
        }
    });
})();
</script>

<script>
(()=>{
    const allowed = new Set([
        'search','name','description','duration_min','duration_max',
        'created_from','created_to','status','employee_name',
        'training_name','assigned_from','assigned_to',
        'completed_from','completed_to','sort','direction','per_page','page'
    ]);

    document.addEventListener('click',e=>{
        const button=e.target.closest('[data-export]');
        if(!button)return;

        e.preventDefault();
        e.stopImmediatePropagation();

        const url=new URL(button.dataset.export);
        const table=document.querySelector('[data-dynamic-table]');
        const page=new URL(location.href).searchParams.get('page');

        if(page)url.searchParams.set('page',page);

        table?.querySelectorAll('input[name],select[name]').forEach(input=>{
            if(allowed.has(input.name)&&!input.disabled&&input.value!==''){
                url.searchParams.set(input.name,input.value);
            }
        });

        url.searchParams.set('scope',button.dataset.scope);

        const ids=[...document.querySelectorAll('[data-row-check]:checked')]
            .map(input=>input.value);

        if(button.dataset.scope==='selected'&&!ids.length){
            Swal.fire({icon:'info',text:'Önce en az bir kayıt seçin.'});
            return;
        }

        if(ids.length)url.searchParams.set('ids',ids.join(','));

        location.href=url;
    },true);

    const observers=new WeakMap();

    function setup(root=document){
        root.querySelectorAll('[data-scroll-pair]').forEach(pair=>{
            const top=pair.querySelector('.top-scroll');
            const bottom=pair.querySelector('.table-scroll');
            const spacer=top?.firstElementChild;

            if(!top||!bottom||!spacer)return;

            observers.get(pair)?.disconnect();
            let syncing=false;

            const measure=()=>{
                top.style.width=bottom.clientWidth+'px';
                spacer.style.width=bottom.scrollWidth+'px';
                sync(bottom,top);
            };

            const sync=(source,target)=>{
                if(syncing)return;

                const sourceMax=source.scrollWidth-source.clientWidth;
                const targetMax=target.scrollWidth-target.clientWidth;

                syncing=true;
                target.scrollLeft=sourceMax>0
                    ?(source.scrollLeft/sourceMax)*Math.max(0,targetMax)
                    :0;

                requestAnimationFrame(()=>syncing=false);
            };

            top.onscroll=()=>sync(top,bottom);
            bottom.onscroll=()=>sync(bottom,top);

            const ro=new ResizeObserver(measure);
            ro.observe(bottom);

            const table=bottom.querySelector('table');
            if(table)ro.observe(table);

            observers.set(pair,ro);
            measure();
        });
    }

    const mo=new MutationObserver(()=>setup());
    mo.observe(document.body,{childList:true,subtree:true});

    addEventListener('resize',()=>setup());
    setup();
})();
</script>

@stack('scripts')
</body>
</html>
