<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · CIMS</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
@php
    $role = auth()->user()->role->role_name ?? null;
    $navItem = function (string $routeName, string $label, string $icon) {
        $active = request()->routeIs($routeName.'*');
        return '<a href="'.route($routeName).'" class="'.($active ? 'active' : '').'"><span class="icon">'.$icon.'</span>'.$label.'</a>';
    };
@endphp
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">✚</div>
            <div><b>Clinic Inventory</b><span>Management System</span></div>
        </div>

        <div class="role-card">
            <label>Signed in as</label>
            <div class="name">{{ auth()->user()->fullname }}</div>
            <div class="muted small">{{ $role }}</div>
        </div>

        <nav class="nav">
            {!! $navItem('dashboard', 'Dashboard', '▦') !!}

            @if($role === 'Nurse')
                {!! $navItem('receiving.create', 'Receiving', '📥') !!}
                {!! $navItem('receiving.index', 'Receiving Records', '📄') !!}
                {!! $navItem('issuance.index', 'Issuance', '📤') !!}
                {!! $navItem('batches.index', 'Batches', '🧴') !!}
                {!! $navItem('equipment.index', 'Equipment', '🩺') !!}
                {!! $navItem('disposals.index', 'Disposals', '🗑') !!}
                {!! $navItem('suppliers.index', 'Suppliers', '🏭') !!}
            @endif

            @if($role === 'Supervisor')
                {!! $navItem('approvals.index', 'Approvals', '✅') !!}
                {!! $navItem('receiving.records', 'Receiving Records', '📄') !!}
                {!! $navItem('batches.index', 'Batches', '🧴') !!}
                {!! $navItem('equipment.index', 'Equipment', '🩺') !!}
                {!! $navItem('disposals.index', 'Disposals', '🗑') !!}
                {!! $navItem('reports.index', 'Reports', '📊') !!}
            @endif

            @if($role === 'Administrator')
                {!! $navItem('items.index', 'Item Master', '📚') !!}
                {!! $navItem('users.index', 'Users', '👤') !!}
                {!! $navItem('roles.index', 'Roles', '🔐') !!}
                {!! $navItem('settings.edit', 'System Settings', '⚙') !!}
                {!! $navItem('suppliers.index', 'Suppliers', '🏭') !!}
            @endif

            {!! $navItem('logs.index', 'Transaction Log', '🧾') !!}
        </nav>

        <div class="sidebar-foot">
            <span>CIMS · Laravel + MySQL</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">⎋ Log out</button>
            </form>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="page-heading">
                <h1>@yield('heading', 'Dashboard')</h1>
                <p>@yield('subheading', '')</p>
            </div>
            <div class="top-actions">
                @yield('top-actions')
            </div>
        </header>

        <div class="content">
            @if ($errors->any())
                <div class="notice danger" style="margin-bottom:16px">
                    <b>Please check the form below:</b>
                    <ul style="margin:6px 0 0;padding-left:18px">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

<div class="modal-back" id="modalBack" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal">
        <div class="modal-head">
            <h3 id="modalTitle"></h3>
            <button type="button" class="btn small" id="modalClose">Close</button>
        </div>
        <div class="modal-body" id="modalBody"></div>
    </div>
</div>
<div class="toast" id="toast"></div>

@if (session('status'))
    <script>window.CIMS_STATUS = @json(session('status'));</script>
@endif
<script src="{{ asset('js/app.js') }}"></script>
@yield('scripts')
</body>
</html>
