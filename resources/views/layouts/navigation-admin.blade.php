@php
    $authUser = Auth::user();

    $overdueCount = $authUser->can('leads.manage')
        ? \App\Models\Lead::whereNotNull('follow_up_date')->where('follow_up_date', '<', now()->toDateString())->whereNotIn('status', ['deal', 'tidak_lanjut'])->count()
        : 0;

    try {
        $expiringSoonCount = $authUser->can('maintenance.manage')
            ? \App\Models\ProjectSubscription::whereIn('status', ['akan_expired', 'expired'])->count()
            : 0;
    } catch (\Exception $e) {
        $expiringSoonCount = 0;
    }

    $verifyingInvoices = $authUser->can('invoices.manage')
        ? \App\Models\Invoice::where('status', 'verifying')->count()
        : 0;

    // Definisi menu Admin Panel: [label, route, pola aktif, ikon, izin, badge, warna badge]
    $menuSections = [
        'Ringkasan' => [
            ['Dashboard CRM', 'admin.dashboard', 'admin.dashboard', 'dashboard', 'crm.dashboard', 0, null],
            ['Dashboard Operasional', 'admin.operations', 'admin.operations', 'monitoring', 'dashboard.view', 0, null],
        ],
        'Penjualan & Keuangan' => [
            ['Leads & Pipeline', 'admin.leads.index', 'admin.leads.*', 'group', 'leads.manage', $overdueCount, 'bg-rose-500'],
            ['Proyek Website', 'admin.projects.index', 'admin.projects.*', 'web', 'crm.projects.manage', 0, null],
            ['Pembayaran & DP', 'admin.payments.index', 'admin.payments.*', 'payments', 'payments.manage', 0, null],
            ['Invoice Klien', 'invoices.index', 'invoices.*', 'receipt_long', 'invoices.manage', $verifyingInvoices, 'bg-amber-500'],
            ['Maintenance Bulanan', 'admin.maintenance.index', 'admin.maintenance.*', 'published_with_changes', 'maintenance.manage', 0, null],
            ['Masa Berlaku / Lisensi', 'admin.subscriptions.index', 'admin.subscriptions.*', 'license', 'maintenance.manage', $expiringSoonCount, 'bg-amber-500'],
            ['Riwayat Pesan WA', 'admin.messages.index', 'admin.messages.*', 'chat', 'messages.manage', 0, null],
        ],
        'Operasional Klien' => [
            ['Papan Kanban Proyek', 'projects.index', ['projects.*', 'tasks.*'], 'view_kanban', 'projects.view', 0, null],
            ['Kelola Tiket', 'admin.tickets', 'admin.tickets', 'inbox', 'tickets.manage', 0, null],
            ['Tiket Saya', 'technician.tickets', 'technician.tickets', 'support_agent', 'tickets.handle', 0, null],
        ],
        'Tata Kelola & Tim' => [
            ['Activity & Audit Log', 'admin.activity-logs.index', 'admin.activity-logs.*', 'history', 'activity.view', 0, null],
            ['Kelola Pengguna', 'admin.users.index', 'admin.users.*', 'manage_accounts', 'users.manage', 0, null],
            ['Role & Hak Akses', 'admin.roles.index', 'admin.roles.*', 'admin_panel_settings', 'roles.manage', 0, null],
            ['Pengaturan Perusahaan', 'admin.settings.company.edit', 'admin.settings.company.*', 'corporate_fare', 'settings.manage', 0, null],
        ],
    ];

    $visibleSections = [];
    foreach ($menuSections as $section => $items) {
        $items = array_values(array_filter($items, fn ($item) => $authUser->can($item[4])));
        if ($items) {
            $visibleSections[$section] = $items;
        }
    }

    $homeRoute = $authUser->can('crm.dashboard') ? 'admin.dashboard' : 'admin.operations';

    $adminMenuTitle = match (true) {
        request()->routeIs('admin.dashboard') => 'Dashboard CRM',
        request()->routeIs('admin.operations') => 'Dashboard Operasional',
        request()->routeIs('admin.leads.*') => 'Leads & Pipeline',
        request()->routeIs('admin.projects.*') => 'Proyek Website',
        request()->routeIs('admin.payments.*') => 'Pembayaran & DP',
        request()->routeIs('invoices.*') => 'Invoice Klien',
        request()->routeIs('admin.maintenance.*') => 'Maintenance Bulanan',
        request()->routeIs('admin.subscriptions.*') => 'Masa Berlaku / Lisensi',
        request()->routeIs('admin.messages.*') => 'Riwayat Pesan WA',
        request()->routeIs('projects.*', 'tasks.*') => 'Papan Kanban',
        request()->routeIs('admin.tickets') => 'Kelola Tiket',
        request()->routeIs('technician.tickets') => 'Tiket Saya',
        request()->routeIs('admin.activity-logs.*') => 'Activity & Audit Log',
        request()->routeIs('admin.users.*') => 'Kelola Pengguna',
        request()->routeIs('admin.roles.*') => 'Role & Hak Akses',
        request()->routeIs('admin.settings.company.*') => 'Pengaturan Perusahaan',
        request()->routeIs('profile.*') => 'Profil Saya',
        default => 'VexaHost Admin',
    };
@endphp

<!-- ========================================================================= -->
<!-- 1. SIDEBAR (Mobile Slide-Over & Desktop Fixed) -->
<!-- ========================================================================= -->
<!-- Backdrop Overlay for Mobile -->
<div x-show="sidebarOpen" 
     x-cloak 
     @click="sidebarOpen = false" 
     class="fixed inset-0 z-40 bg-zinc-950/60 backdrop-blur-xs lg:hidden"
     x-transition:enter="transition-opacity ease-linear duration-200"
     x-transition:enter-start="opacity-0" 
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-200"
     x-transition:leave-start="opacity-100" 
     x-transition:leave-end="opacity-0"></div>

<!-- Sidebar Drawer -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed top-0 left-0 z-50 h-screen w-full lg:w-64 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-xl border-r border-zinc-200/80 dark:border-zinc-900/80 flex flex-col shadow-xl lg:shadow-xs transition-transform duration-200 ease-out select-none">

    <!-- Branding Header -->
    <div class="flex h-20 shrink-0 items-center gap-3.5 px-5">
        <a href="{{ route($homeRoute) }}" class="flex min-w-0 items-center gap-3.5 font-sans">
            <img src="{{ asset('images/logo.png') }}" alt="VexaHost Logo" class="h-11 w-auto shrink-0 object-contain">
            <div class="flex min-w-0 flex-col">
                <span class="truncate text-lg font-extrabold tracking-tight text-zinc-900 dark:text-white leading-tight">VexaHost</span>
                <span class="truncate text-xs font-semibold tracking-wide text-zinc-500 dark:text-zinc-400 leading-tight">Admin Panel</span>
            </div>
        </a>
        <!-- Close Button (Mobile Only) -->
        <button @click="sidebarOpen = false" 
                type="button" 
                class="ml-auto lg:hidden p-1 rounded-md text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors"
                aria-label="Tutup Menu">
            <span class="material-symbols-outlined text-[20px] block">close</span>
        </button>
    </div>

    <!-- Navigation Menu items -->
    <nav class="flex-1 overflow-y-auto custom-scrollbar py-5 space-y-1 px-4">
        @foreach($visibleSections as $section => $items)
            <div class="flex items-center gap-2 px-3.5 py-2 {{ $loop->first ? '' : 'mt-3' }} mb-1">
                <span class="text-[9px] font-bold font-mono text-zinc-400 dark:text-zinc-500 uppercase tracking-widest">{{ $section }}</span>
                <div class="h-px bg-zinc-200/60 dark:bg-zinc-800/60 flex-1"></div>
            </div>

            @foreach($items as [$label, $routeName, $pattern, $icon, $permission, $badge, $badgeColor])
                @php $isActive = request()->routeIs(...(array) $pattern); @endphp
                <a href="{{ route($routeName) }}"
                   class="flex items-center px-3.5 py-2.5 justify-start gap-3 rounded-lg text-xs transition-colors duration-150 group {{ $isActive ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900/70 hover:text-zinc-900 dark:hover:text-zinc-100 font-medium' }}">
                    <span class="material-symbols-outlined text-[20px] {{ $isActive ? 'text-white' : 'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300' }} shrink-0">{{ $icon }}</span>
                    <span class="truncate flex-1">{{ $label }}</span>
                    @if($badge > 0)
                        <span class="px-1.5 py-0.5 text-[10px] font-mono font-bold rounded-md {{ $isActive ? 'bg-white/20 text-white' : $badgeColor . ' text-white' }} leading-none">{{ $badge }}</span>
                    @endif
                </a>
            @endforeach
        @endforeach

        <!-- Quick Snippets Shortcut in Sidebar -->
        @can('messages.manage')
            <div class="pt-4 px-2">
                <button @click="$dispatch('open-quick-snippets')"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg bg-zinc-100 dark:bg-zinc-900 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-zinc-700 dark:text-zinc-300 hover:text-emerald-700 dark:hover:text-emerald-400 border border-zinc-200 dark:border-zinc-800 hover:border-emerald-500/40 text-xs font-bold transition-all shadow-xs">
                    <span class="material-symbols-outlined text-[18px] text-emerald-600">content_paste</span>
                    <span>Template Chat WA</span>
                </button>
            </div>
        @endcan
    </nav>

    <!-- Quick Logout in Sidebar Footer -->
    <div class="shrink-0 border-t border-zinc-200/60 dark:border-zinc-800/60 p-3">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" 
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors">
                <span class="material-symbols-outlined text-[18px]">logout</span>
                <span>Keluar Akun</span>
            </button>
        </form>
    </div>
</aside>

<!-- ========================================================================= -->
<!-- 2. TOP HEADER BAR -->
<!-- ========================================================================= -->
<header class="fixed top-0 right-0 left-0 lg:left-64 h-16 bg-white/80 dark:bg-zinc-950/80 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800/80 flex items-center justify-between px-4 sm:px-8 z-30">

    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
        <!-- Hamburger Button (Mobile Only) -->
        <button @click="sidebarOpen = true" 
                type="button"
                class="lg:hidden p-2 -ml-1 rounded-lg text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors focus:outline-none shrink-0"
                aria-label="Buka Menu">
            <span class="material-symbols-outlined text-[24px] block">menu</span>
        </button>

        <!-- Active Menu Name (Mobile Only - No logo) -->
        <h1 class="lg:hidden text-sm sm:text-base font-bold text-zinc-900 dark:text-white truncate">
            {{ $adminMenuTitle }}
        </h1>

        <!-- Desktop Title -->
        <span class="hidden lg:inline text-xs font-semibold text-zinc-500 dark:text-zinc-400">
            VexaHost &middot; Admin Panel
        </span>
    </div>

    <div class="flex items-center gap-2 sm:gap-4">

        <!-- Theme Toggle Button -->
        <button @click="toggleTheme()"
                class="p-2 rounded-lg text-zinc-500 dark:text-zinc-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/50 transition-colors focus:outline-none cursor-pointer"
                title="Ganti Tema">
            <span class="material-symbols-outlined text-[22px] block text-amber-400" x-show="darkMode" x-cloak>light_mode</span>
            <span class="material-symbols-outlined text-[22px] block text-zinc-600 dark:text-zinc-400" x-show="!darkMode">dark_mode</span>
        </button>

        <!-- Menu akun (ikon profil saja; detail di dropdown) -->
        <x-profile-menu />
    </div>
</header>
