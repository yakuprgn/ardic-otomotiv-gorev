
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Personel Yönetimi | Ardıç</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
:root{--ink:#15283e;--blue:#2367bd;--line:#dfe8f2;--muted:#64778d}
*{box-sizing:border-box}
body{margin:0;color:var(--ink);font-family:Inter,"Segoe UI",sans-serif;background:radial-gradient(circle at 95% 0,#cce9ff88,transparent 26%),#f2f6fa;min-height:100vh}
.topbar{position:sticky;top:0;z-index:1020;color:#fff;background:linear-gradient(110deg,#091b2e,#123a62 58%,#17679b);box-shadow:0 10px 35px #071a303d}
.brand{width:43px;height:43px;display:grid;place-items:center;border-radius:14px;background:linear-gradient(135deg,#2683dd,#55b8e8);box-shadow:0 8px 25px #2085d34c}
.panel{background:#fff;border:1px solid var(--line);border-radius:20px;box-shadow:0 12px 40px #0e355b0e}
.metric{transition:.2s;overflow:hidden;position:relative}
.metric:hover{transform:translateY(-3px);box-shadow:0 16px 45px #153b601b}
.small-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);font-weight:800}
.btn{border-radius:10px;transition:.2s}
.btn-primary{border:0;background:linear-gradient(120deg,#1d58a1,#2686d9);box-shadow:0 8px 20px #1d67ad37}
.btn-primary:hover{transform:translateY(-1px);filter:brightness(1.08)}
.btn-success{border:0;background:linear-gradient(120deg,#087c69,#13aa81)}
.btn-danger{border:0;background:linear-gradient(120deg,#b82e48,#e6525b)}
.form-control,.form-select{border-color:#d9e4ef;border-radius:10px}
.table{white-space:nowrap}
.table>:not(caption)>*>*{padding:.8rem .7rem;vertical-align:middle;border-bottom-color:#eaf0f6}
.table thead th{background:#f4f8fc;color:#435b74;font-size:.78rem}
.table-filter{min-width:125px;font-size:.78rem}
.table-scroll{overflow:auto}
.top-scroll{height:14px;overflow-x:auto;overflow-y:hidden}
.top-scroll>div{height:1px}
.sort{text-decoration:none;color:inherit}
.avatar{width:36px;height:36px;display:inline-grid;place-items:center;border-radius:11px;background:linear-gradient(135deg,#e2f1ff,#cce4fb);color:#195c9f;font-weight:800}
.dept{background:#e7f3ff;color:#195a94}
.table-refreshing{opacity:.55;transition:opacity .15s ease}
.modal-content{border:0;border-radius:22px;overflow:hidden}
.modal-header{color:#fff;background:linear-gradient(120deg,#123a62,#2785cf)}
.empty{text-align:center;padding:3.5rem!important;color:var(--muted)}
@media(max-width:768px){.toolbar>*{width:100%}}
@media(prefers-reduced-motion:reduce){*{transition:none!important}}
</style>
</head>
<body>
<header class="topbar py-3">
    <div class="container-fluid px-3 px-lg-4 d-flex align-items-center gap-3">
        <div class="brand">
            <i class="bi bi-people-fill fs-5"></i>
        </div>
        <div>
            <div class="fw-bold">ARDIÇ OTOMOTİV</div>
            <div class="small opacity-75">Personel Yönetim Sistemi</div>
        </div>
        <span class="badge rounded-pill bg-white text-dark ms-auto d-none d-sm-inline">
            Personnel Service
        </span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-sm text-white" style="background:#ffffff1c">
                <i class="bi bi-box-arrow-right me-1"></i>Çıkış
            </button>
        </form>
    </div>
</header>

<main class="container-fluid px-3 px-lg-4 py-4">
    <div class="d-flex flex-wrap justify-content-between gap-3 mb-3">
        <div>
            <h1 class="h3 fw-bold mb-1">Personel Listesi</h1>
            <p class="text-secondary mb-0">Kurum personelini güvenle yönetin ve filtreleyin.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#employeeModal" id="addEmployee">
            <i class="bi bi-person-plus me-1"></i>Yeni Personel
        </button>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="panel metric p-3">
                <div class="small-label">Toplam personel</div>
                <div class="fs-2 fw-bold">{{ number_format($totalCount,0,',','.') }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="panel metric p-3">
                <div class="small-label">Filtrelenen</div>
                <div class="fs-2 fw-bold" data-personnel-filtered-count>
                    {{ number_format($employees->total(),0,',','.') }}
                </div>
            </div>
        </div>
    </div>

    <section class="panel overflow-hidden" data-dynamic-table>
        <form method="GET" action="{{ route('employees.index') }}" data-filter-form>
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">

            <div class="toolbar p-3 border-bottom d-flex flex-wrap gap-2 align-items-center">
                <div class="input-group" style="max-width:350px">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input class="form-control" name="search" value="{{ request('search') }}"
                           placeholder="Tüm personelde ara…" data-filter>
                </div>

                <select class="form-select" style="width:100px" name="per_page" data-filter>
                    @foreach([10,25,50,100] as $n)
                        <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>
                    @endforeach
                </select>

                <a class="btn btn-light border" data-table-link href="{{ route('employees.index') }}">
                    <i class="bi bi-x-circle me-1"></i>Temizle
                </a>

                <div class="dropdown ms-lg-auto">
                    <button class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-file-earmark-excel me-1"></i>Excel Aktar
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <button class="dropdown-item" type="button"
                                    data-export="{{ route('employees.export') }}" data-scope="page">
                                Bu sayfa
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item" type="button"
                                    data-export="{{ route('employees.export') }}" data-scope="filtered">
                                Tüm filtrelenenler
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item" type="button"
                                    data-export="{{ route('employees.export') }}" data-scope="selected">
                                Seçilenler (<span data-selected-count>0</span>)
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <div data-scroll-pair>
                <div class="top-scroll"><div></div></div>
                <div class="table-scroll">
                    <table class="table mb-0" style="min-width:1200px">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" class="form-check-input" data-check-all>
                                </th>
                                <th>İşlemler</th>
                                @foreach([
                                    'first_name'=>'Ad',
                                    'last_name'=>'Soyad',
                                    'email'=>'E-posta',
                                    'department'=>'Departman',
                                    'created_at'=>'Oluşturulma'
                                ] as $field=>$label)
                                    <th>
                                        <a class="sort" data-table-link
                                           href="{{ request()->fullUrlWithQuery([
                                               'sort'=>$field,
                                               'direction'=>$sort===$field&&$direction==='asc'?'desc':'asc',
                                               'page'=>1
                                           ]) }}">
                                            {{ $label }}
                                            <i class="bi bi-arrow-down-up"></i>
                                        </a>
                                    </th>
                                @endforeach
                            </tr>
                            <tr>
                                <th></th>
                                <th class="small text-secondary">Filtreler</th>

                                @foreach([
                                    'first_name'=>'Ad ara',
                                    'last_name'=>'Soyad ara',
                                    'email'=>'E-posta ara'
                                ] as $field=>$ph)
                                    <th>
                                        <input class="form-control table-filter" name="{{ $field }}"
                                               value="{{ request($field) }}" placeholder="{{ $ph }}" data-filter>
                                    </th>
                                @endforeach

                                <th>
                                    <select class="form-select table-filter" name="department" data-filter>
                                        <option value="">Tümü</option>
                                        @foreach($departments as $d)
                                            <option value="{{ $d }}" @selected(request('department') === $d)>
                                                {{ $d }}
                                            </option>
                                        @endforeach
                                    </select>
                                </th>

                                <th>
                                    <div class="d-flex gap-1">
                                        <input class="form-control table-filter" type="date"
                                               name="created_from" value="{{ request('created_from') }}" data-filter>
                                        <input class="form-control table-filter" type="date"
                                               name="created_to" value="{{ request('created_to') }}" data-filter>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $e)
                                <tr>
                                    <td>
                                        <input class="form-check-input" type="checkbox"
                                               value="{{ $e->id }}" data-row-check>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-primary edit-employee"
                                                    type="button"
                                                    data-id="{{ $e->id }}"
                                                    data-first-name="{{ $e->first_name }}"
                                                    data-last-name="{{ $e->last_name }}"
                                                    data-email="{{ $e->email }}"
                                                    data-department="{{ $e->department }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <form method="POST" action="{{ route('employees.destroy',$e) }}"
                                                  data-confirm="Personel kaydı pasife alınsın mı?">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="avatar me-2">
                                            {{ mb_strtoupper(mb_substr($e->first_name,0,1).mb_substr($e->last_name,0,1)) }}
                                        </span>{{ $e->first_name }}
                                    </td>
                                    <td>{{ $e->last_name }}</td>
                                    <td>
                                        <a href="mailto:{{ $e->email }}">{{ $e->email }}</a>
                                    </td>
                                    <td>
                                        <span class="badge dept">{{ $e->department }}</span>
                                    </td>
                                    <td>{{ $e->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="empty">
                                        <i class="bi bi-person-x fs-2 d-block mb-2"></i>
                                        Filtrelerle eşleşen personel yok.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-3 border-top d-flex flex-wrap justify-content-between gap-2">
                <small class="text-secondary">
                    {{ $employees->firstItem()??0 }}–{{ $employees->lastItem()??0 }}
                    / {{ $employees->total() }} kayıt
                </small>
                {{ $employees->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </form>
    </section>

    <div class="modal fade" id="employeeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('employees.store') }}" id="employeeForm" data-busy>
                    @csrf
                    <input type="hidden" name="_method" id="employeeMethod" value="PUT" disabled>

                    <div class="modal-header">
                        <h2 class="modal-title fs-5">
                            <i class="bi bi-person-vcard me-2"></i>
                            <span id="employeeTitle">Yeni Personel</span>
                        </h2>
                        <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">Ad *</label>
                                <input class="form-control" name="first_name" id="firstName" required maxlength="100">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Soyad *</label>
                                <input class="form-control" name="last_name" id="lastName" required maxlength="100">
                            </div>
                            <div class="col-12">
                                <label class="form-label">E-posta *</label>
                                <input class="form-control" type="email" name="email" id="email" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Departman *</label>
                                <input class="form-control" name="department" id="department" required maxlength="120">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Vazgeç</button>
                        <button class="btn btn-primary" type="submit">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
@if(session('success'))
Swal.fire({
    icon:'success',
    title:'İşlem başarılı',
    text:@json(session('success')),
    timer:1900,
    showConfirmButton:false
});
@endif

@if($errors->any())
Swal.fire({
    icon:'error',
    title:'Doğrulama başarısız',
    text:@json(implode("\n",$errors->all()))
});
@endif

document.addEventListener('submit',async e=>{
    const f=e.target;

    if(f.dataset.confirm&&!f.dataset.ok){
        e.preventDefault();
        const r=await Swal.fire({
            icon:'warning',
            title:f.dataset.confirm,
            showCancelButton:true,
            confirmButtonText:'Onayla',
            cancelButtonText:'Vazgeç'
        });

        if(r.isConfirmed){
            f.dataset.ok=1;
            f.requestSubmit();
        }
        return;
    }

    if(f.dataset.busy!==undefined){
        const b=f.querySelector('[type=submit]');
        b.disabled=true;
        b.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>İşleniyor…';
    }
});

const modal=bootstrap.Modal.getOrCreateInstance('#employeeModal');
const ef=employeeForm;
const method=employeeMethod;

function fill(e=null){
    ef.reset();
    method.disabled=!e;
    ef.action=e
        ?@json(url('/employees'))+'/'+e.id
        :@json(route('employees.store'));

    employeeTitle.textContent=e?'Personeli Düzenle':'Yeni Personel';
    firstName.value=e?.first_name??'';
    lastName.value=e?.last_name??'';
    email.value=e?.email??'';
    department.value=e?.department??'';
}

addEmployee.onclick=()=>fill();

document.addEventListener('click',e=>{
    const b=e.target.closest('.edit-employee');
    if(b){
        fill({
            id:b.dataset.id,
            first_name:b.dataset.firstName,
            last_name:b.dataset.lastName,
            email:b.dataset.email,
            department:b.dataset.department
        });
        modal.show();
    }
});
</script>

<script>
(()=>{
    const allowed=new Set([
        'search','first_name','last_name','email','department',
        'created_from','created_to','sort','direction','per_page'
    ]);

    let pending=null;
    let debounceTimer=null;
    let sessionWarningShown=false;

    const region=()=>document.querySelector('[data-dynamic-table]');

    const controlsUrl=()=>{
        const area=region();
        const form=area?.querySelector('[data-filter-form]');
        const url=new URL(
            form?.getAttribute('action')||location.pathname,
            location.origin
        );

        area?.querySelectorAll('input[name],select[name]').forEach(input=>{
            if(!allowed.has(input.name)||input.disabled)return;

            const value=input.value.trim();
            if(value!=='')url.searchParams.set(input.name,value);
        });

        url.searchParams.delete('page');
        return url;
    };

    const sessionWarning=()=>{
        if(sessionWarningShown)return;
        sessionWarningShown=true;

        Swal.fire({
            icon:'warning',
            title:'Oturum doğrulanamadı',
            text:'Oturumunuz sona ermiş olabilir. Güvenlik nedeniyle yeniden giriş yapılması gerekiyor.',
            confirmButtonText:'Giriş sayfasına git',
            showCancelButton:true,
            cancelButtonText:'Bu sayfada kal'
        }).then(result=>{
            sessionWarningShown=false;
            if(result.isConfirmed){
                location.assign(@json(route('login')));
            }
        });
    };

    async function load(url,mode='navigate',historyMode='push'){
        const area=region();
        if(!area)return;

        pending?.abort();

        const request=new AbortController();
        pending=request;
        const requestUrl=new URL(url,location.origin);

        area.querySelector('tbody')?.classList.add('table-refreshing');

        try{
            const response=await fetch(requestUrl.href,{
                method:'GET',
                credentials:'same-origin',
                signal:request.signal,
                headers:{
                    'X-Requested-With':'XMLHttpRequest',
                    'Accept':'application/json'
                }
            });

            const redirectedToLogin=
                response.redirected &&
                new URL(response.url).pathname.endsWith('/login');

            if(
                response.status===401||
                response.status===419||
                redirectedToLogin
            ){
                sessionWarning();
                return;
            }

            if(response.status===429){
                throw new Error('Çok sayıda istek gönderildi. Kısa süre sonra tekrar deneyin.');
            }

            if(!response.ok){
                throw new Error('HTTP '+response.status);
            }

            const html=await response.text();
            const freshDocument=new DOMParser().parseFromString(html,'text/html');
            const fresh=freshDocument.querySelector('[data-dynamic-table]');

            if(!fresh){
                throw new Error('Beklenmeyen sunucu yanıtı.');
            }

            if(mode==='filter'){
                // Yeni yazılan metin varsa eski isteğin sonucunu kullanma.
                if(controlsUrl().href!==requestUrl.href)return;

                const oldBody=area.querySelector('.table-scroll tbody');
                const newBody=fresh.querySelector('.table-scroll tbody');
                const oldFooter=area.querySelector('.p-3.border-top');
                const newFooter=fresh.querySelector('.p-3.border-top');

                if(!oldBody||!newBody||!oldFooter||!newFooter){
                    throw new Error('Tablo yapısı değişmiş.');
                }

                // Arama kutuları aynı DOM elemanları olarak kalır.
                oldBody.replaceWith(newBody);
                oldFooter.replaceWith(newFooter);

                const sortLinks=area.querySelectorAll('a.sort,a.sort-link');
                const freshSortLinks=fresh.querySelectorAll('a.sort,a.sort-link');

                sortLinks.forEach((link,i)=>{
                    if(freshSortLinks[i])link.href=freshSortLinks[i].href;
                });

                const checkAll=area.querySelector('[data-check-all]');
                if(checkAll)checkAll.checked=false;

                area.querySelectorAll('[data-selected-count]').forEach(
                    node=>node.textContent='0'
                );

                const count=document.querySelector('[data-personnel-filtered-count]');
                const freshCount=freshDocument.querySelector('[data-personnel-filtered-count]');
                if(count&&freshCount){
                    count.textContent=freshCount.textContent;
                }
            }else{
                area.replaceWith(fresh);
            }

            if(historyMode==='push'){
                history.pushState({},'',requestUrl.href);
            }

            if(historyMode==='replace'){
                history.replaceState({},'',requestUrl.href);
            }
        }catch(error){
            if(error.name!=='AbortError'){
                Swal.fire({
                    icon:'error',
                    title:'Veriler yüklenemedi',
                    text:error.message||'Bağlantıyı kontrol edin.'
                });
            }
        }finally{
            if(pending===request){
                region()?.querySelector('tbody')?.classList.remove('table-refreshing');
                pending=null;
            }
        }
    }

    const filterNow=()=>load(
        controlsUrl().href,
        'filter',
        'replace'
    );

    document.addEventListener('submit',event=>{
        if(!event.target.matches('[data-filter-form]'))return;
        event.preventDefault();
        clearTimeout(debounceTimer);
        filterNow();
    });

    document.addEventListener('input',event=>{
        if(!event.target.matches(
            '[data-dynamic-table] input[data-filter]:not([type="date"])'
        ))return;

        clearTimeout(debounceTimer);
        debounceTimer=setTimeout(filterNow,350);
    });

    document.addEventListener('change',event=>{
        if(!event.target.matches(
            '[data-dynamic-table] select[data-filter], [data-dynamic-table] input[type="date"][data-filter]'
        ))return;

        clearTimeout(debounceTimer);
        filterNow();
    });

    document.addEventListener('click',event=>{
        const link=event.target.closest(
            '[data-dynamic-table] a[data-table-link], [data-dynamic-table] .pagination a'
        );

        if(!link)return;

        event.preventDefault();
        clearTimeout(debounceTimer);
        load(link.href,'navigate','push');
    });

    addEventListener('popstate',()=>{
        clearTimeout(debounceTimer);
        load(location.href,'navigate','none');
    });

    document.addEventListener('change',event=>{
        if(event.target.matches('[data-check-all]')){
            region()?.querySelectorAll('[data-row-check]').forEach(
                box=>box.checked=event.target.checked
            );
        }

        if(event.target.matches('[data-check-all],[data-row-check]')){
            const selected=
                region()?.querySelectorAll('[data-row-check]:checked').length||0;

            region()?.querySelectorAll('[data-selected-count]').forEach(
                node=>node.textContent=String(selected)
            );
        }
    });
})();
</script>

<script>
(()=>{
    const allowed=new Set([
        'search','first_name','last_name','email','department',
        'created_from','created_to','sort','direction','per_page','page'
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
            Swal.fire({
                icon:'info',
                text:'Önce en az bir kayıt seçin.'
            });
            return;
        }

        if(ids.length){
            url.searchParams.set('ids',ids.join(','));
        }

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

            const measure=()=>{
                top.style.width=bottom.clientWidth+'px';
                spacer.style.width=bottom.scrollWidth+'px';
                sync(bottom,top);
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
</body>
</html>
