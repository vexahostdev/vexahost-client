@php
    $clientMenuTitle = match (true) {
        request()->routeIs('client.dashboard') => 'Dashboard',
        request()->routeIs('tickets.create') => 'Buat Tiket',
        request()->routeIs('tickets.*') => 'Tiket Saya',
        request()->routeIs('admin.tickets*', 'technician.tickets*') => 'Tiket Masuk',
        request()->routeIs('projects.*') => 'Proyek',
        request()->routeIs('invoices.*') => 'Tagihan & Pembayaran',
        request()->routeIs('admin.users.*') => 'Pengguna',
        request()->routeIs('admin.roles.*') => 'Role & Akses',
        request()->routeIs('profile.*') => 'Profil Saya',
        default => 'VexaHost Client',
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
       class="fixed top-0 left-0 z-50 h-screen w-full lg:w-64 bg-white dark:bg-zinc-950 border-r border-zinc-200/80 dark:border-zinc-800/80 flex flex-col shadow-xl lg:shadow-xs transition-transform duration-200 ease-out select-none">
    
    <!-- Branding Header -->
    <div class="flex h-20 shrink-0 items-center gap-3.5 px-5">
        <a href="{{ route('client.dashboard') }}" class="flex min-w-0 items-center gap-3.5 font-sans">
            <img src="{{ asset('images/logo.png') }}" alt="VexaHost Logo" class="h-11 w-auto shrink-0 object-contain">
            <span class="truncate text-lg font-extrabold tracking-tight text-zinc-900 dark:text-white leading-tight">VexaHost</span>
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
    <nav class="flex-1 overflow-y-auto custom-scrollbar py-6 space-y-1 transition-all duration-300 px-4">
        <!-- Dasbor Link -->
        @php $isDashboard = request()->routeIs('client.dashboard'); @endphp
        <a href="{{ route('client.dashboard') }}" 
           class="flex items-center px-3.5 py-2.5 justify-start gap-3 rounded-lg text-xs transition-colors duration-150 group {{ $isDashboard ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900/70 hover:text-zinc-900 dark:hover:text-zinc-100 font-medium' }}">
            <span class="material-symbols-outlined text-[20px] {{ $isDashboard ? 'text-white' : 'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300' }} shrink-0">dashboard</span>
            <span class="truncate">Dashboard</span>
        </a>

        <!-- Tiket Saya Link (Klien) -->
        @can('tickets.create')
            @php $isTickets = request()->routeIs('tickets.*'); @endphp
            <a href="{{ route('tickets.index') }}" 
               class="flex items-center px-3.5 py-2.5 justify-start gap-3 rounded-lg text-xs transition-colors duration-150 group {{ $isTickets ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900/70 hover:text-zinc-900 dark:hover:text-zinc-100 font-medium' }}">
                <span class="material-symbols-outlined text-[20px] {{ $isTickets ? 'text-white' : 'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300' }} shrink-0">confirmation_number</span>
                <span class="truncate">Tiket Saya</span>
            </a>
        @endcan

        <!-- Tiket Masuk Link (Admin / Teknisi) -->
        @can('tickets.manage')
            @php $isAdminTickets = request()->routeIs('admin.tickets*'); @endphp
            <a href="{{ route('admin.tickets') }}" 
               class="flex items-center px-3.5 py-2.5 justify-start gap-3 rounded-lg text-xs transition-colors duration-150 group {{ $isAdminTickets ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900/70 hover:text-zinc-900 dark:hover:text-zinc-100 font-medium' }}">
                <span class="material-symbols-outlined text-[20px] {{ $isAdminTickets ? 'text-white' : 'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300' }} shrink-0">support_agent</span>
                <span class="truncate">Tiket Masuk</span>
            </a>
        @elsecan('tickets.handle')
            @php $isTechTickets = request()->routeIs('technician.tickets*'); @endphp
            <a href="{{ route('technician.tickets') }}" 
               class="flex items-center px-3.5 py-2.5 justify-start gap-3 rounded-lg text-xs transition-colors duration-150 group {{ $isTechTickets ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900/70 hover:text-zinc-900 dark:hover:text-zinc-100 font-medium' }}">
                <span class="material-symbols-outlined text-[20px] {{ $isTechTickets ? 'text-white' : 'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300' }} shrink-0">support_agent</span>
                <span class="truncate">Tiket Masuk</span>
            </a>
        @endcan

        <!-- Proyek Link -->
        @php $isProjects = request()->routeIs('projects.*'); @endphp
        <a href="{{ route('projects.index') }}" 
           class="flex items-center px-3.5 py-2.5 justify-start gap-3 rounded-lg text-xs transition-colors duration-150 group {{ $isProjects ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900/70 hover:text-zinc-900 dark:hover:text-zinc-100 font-medium' }}">
            <span class="material-symbols-outlined text-[20px] {{ $isProjects ? 'text-white' : 'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300' }} shrink-0">view_kanban</span>
            <span class="truncate">Proyek</span>
        </a>

        <!-- Tagihan & Pembayaran Link -->
        @php $isInvoices = request()->routeIs('invoices.*'); @endphp
        <a href="{{ route('invoices.index') }}" 
           class="flex items-center px-3.5 py-2.5 justify-start gap-3 rounded-lg text-xs transition-colors duration-150 group {{ $isInvoices ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900/70 hover:text-zinc-900 dark:hover:text-zinc-100 font-medium' }}">
            <span class="material-symbols-outlined text-[20px] {{ $isInvoices ? 'text-white' : 'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300' }} shrink-0">payments</span>
            <span class="truncate">Tagihan &amp; Pembayaran</span>
        </a>

        <!-- Seksi Administrasi -->
        @canany(['users.manage', 'roles.manage'])
            <div class="flex items-center gap-2 px-3.5 py-2 mt-4 mb-2">
                <span class="text-[9px] font-bold font-mono text-zinc-400 dark:text-zinc-500 uppercase tracking-widest">Administrasi</span>
                <div class="h-px bg-zinc-200/60 dark:bg-zinc-800/60 flex-1"></div>
            </div>

            @can('users.manage')
                @php $isUsers = request()->routeIs('admin.users.*'); @endphp
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center px-3.5 py-2.5 justify-start gap-3 rounded-lg text-xs transition-colors duration-150 group {{ $isUsers ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900/70 hover:text-zinc-900 dark:hover:text-zinc-100 font-medium' }}">
                    <span class="material-symbols-outlined text-[20px] {{ $isUsers ? 'text-white' : 'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300' }} shrink-0">group</span>
                    <span class="truncate">Pengguna</span>
                </a>
            @endcan

            @can('roles.manage')
                @php $isRoles = request()->routeIs('admin.roles.*'); @endphp
                <a href="{{ route('admin.roles.index') }}"
                   class="flex items-center px-3.5 py-2.5 justify-start gap-3 rounded-lg text-xs transition-colors duration-150 group {{ $isRoles ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900/70 hover:text-zinc-900 dark:hover:text-zinc-100 font-medium' }}">
                    <span class="material-symbols-outlined text-[20px] {{ $isRoles ? 'text-white' : 'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300' }} shrink-0">admin_panel_settings</span>
                    <span class="truncate">Role &amp; Akses</span>
                </a>
            @endcan
        @endcanany

        <!-- Quick Action / Support in Sidebar -->
        <div class="pt-4 mt-4 border-t border-zinc-200/60 dark:border-zinc-800/60">
            @can('tickets.create')
                <a href="{{ route('tickets.create') }}" 
                   class="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-950/70 rounded-lg transition-colors border border-emerald-200/60 dark:border-emerald-800/40">
                    <span class="material-symbols-outlined text-[18px] text-emerald-600">add_circle</span>
                    <span>Buat Tiket Baru</span>
                </a>
            @else
                <a href="{{ route('projects.index') }}" 
                   class="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-950/70 rounded-lg transition-colors border border-emerald-200/60 dark:border-emerald-800/40">
                    <span class="material-symbols-outlined text-[18px] text-emerald-600">view_kanban</span>
                    <span>Lihat Semua Proyek</span>
                </a>
            @endcan
        </div>
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
<!-- 2. TOP HEADER (Desktop & Mobile) -->
<!-- ========================================================================= -->
<header class="fixed top-0 right-0 left-0 lg:left-64 h-16 bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800/80 flex items-center justify-between px-4 sm:px-8 z-30">
    
    <!-- Mobile Hamburger + Menu Name / Desktop Title -->
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
            {{ $clientMenuTitle }}
        </h1>

        <!-- Desktop Title & Status Badge -->
        <div class="hidden lg:flex items-center gap-3">
            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">
                VexaHost Client Panel
            </span>
            <span class="h-4 w-px bg-zinc-200 dark:bg-zinc-800"></span>
            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/40 text-[10px] font-mono font-semibold text-emerald-700 dark:text-emerald-400">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Client Support Active</span>
            </div>
        </div>
    </div>

    <!-- Right Action Items -->
    <div class="flex items-center gap-2 sm:gap-3">
        @can('tickets.create')
            <a href="{{ route('tickets.create') }}" 
               class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs active:scale-95">
                <span class="material-symbols-outlined text-[16px]">add_circle</span>
                <span>Buat Tiket</span>
            </a>
        @endcan
        
        <!-- Theme Toggle Button -->
        <button @click="toggleTheme()" 
                type="button"
                class="p-2 rounded-xl text-zinc-500 dark:text-zinc-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/50 transition-colors focus:outline-none cursor-pointer"
                title="Ganti Tema">
            <span class="material-symbols-outlined text-[22px] block text-amber-400" x-show="darkMode" x-cloak>light_mode</span>
            <span class="material-symbols-outlined text-[22px] block text-zinc-600 dark:text-zinc-400" x-show="!darkMode">dark_mode</span>
        </button>

        <!-- Notifications Dropdown -->
        <x-dropdown align="right" width="80" contentClasses="py-0 overflow-hidden bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xl">
            <x-slot name="trigger">
                <button type="button" class="relative p-2 rounded-xl text-zinc-500 dark:text-zinc-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/50 transition-colors focus:outline-none">
                    <span class="material-symbols-outlined text-[22px]">notifications</span>
                    @php
                        $unreadCount = method_exists(Auth::user(), 'unreadNotifications') ? Auth::user()->unreadNotifications->count() : 0;
                    @endphp
                    @if($unreadCount > 0)
                        <span id="notification-badge" class="absolute top-1.5 right-1.5 inline-flex items-center justify-center px-1.5 py-0.5 text-[9px] font-black leading-none text-white bg-rose-500 rounded-full border border-white dark:border-zinc-900 shadow-sm">
                            {{ $unreadCount }}
                        </span>
                    @else
                        <span id="notification-badge" class="hidden absolute top-1.5 right-1.5 inline-flex items-center justify-center px-1.5 py-0.5 text-[9px] font-black leading-none text-white bg-rose-500 rounded-full border border-white dark:border-zinc-900 shadow-sm">0</span>
                    @endif
                </button>
            </x-slot>

            <x-slot name="content">
                <div class="px-4 py-3 bg-zinc-50/50 dark:bg-zinc-950/50 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center">
                    <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 uppercase tracking-wider">Pusat Notifikasi</span>
                    <span id="notification-count-label" class="text-[10px] font-mono bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 rounded-md font-bold">{{ $unreadCount }} Baru</span>
                </div>

                <div id="notification-list" class="max-h-80 overflow-y-auto custom-scrollbar divide-y divide-zinc-100 dark:divide-zinc-800/40 bg-white dark:bg-zinc-900">
                    @if(method_exists(Auth::user(), 'unreadNotifications') && $unreadCount > 0)
                        @foreach(Auth::user()->unreadNotifications->take(5) as $notification)
                            <div data-notification-item class="px-4 py-3.5 hover:bg-zinc-50 dark:hover:bg-zinc-950/30 transition-colors duration-150 text-sm group">
                                @if(!empty($notification->data['url']))
                                    <a href="{{ $notification->data['url'] }}" class="block">
                                @endif
                                <div class="font-semibold text-zinc-800 dark:text-zinc-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                    {{ $notification->data['title'] ?? 'Pemberitahuan Sistem' }}
                                </div>
                                <div class="text-zinc-500 dark:text-zinc-400 text-xs mt-1 leading-relaxed">
                                    {{ $notification->data['message'] ?? '' }}
                                </div>
                                @if(!empty($notification->data['url']))
                                    </a>
                                @endif
                                <div class="text-[10px] font-mono text-zinc-400 dark:text-zinc-500 mt-2.5 flex justify-between items-center">
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[12px]">schedule</span>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                    <button type="button" @click.stop onclick="markAsRead('{{ $notification->id }}', this)" class="text-emerald-600 dark:text-emerald-400 hover:underline font-bold">Tandai Dibaca</button>
                                 </div>
                            </div>
                        @endforeach
                    @else
                        <div data-notification-empty class="px-4 py-8 text-center bg-white dark:bg-zinc-900">
                            <span class="material-symbols-outlined text-zinc-300 dark:text-zinc-600 text-[36px] block mb-2">notifications_off</span>
                            <p class="text-xs text-zinc-400 dark:text-zinc-500 italic">Kotak masuk bersih. Tidak ada notifikasi baru.</p>
                        </div>
                    @endif
                </div>

                @php
                    $totalNotifications = method_exists(Auth::user(), 'notifications') ? Auth::user()->notifications->count() : 0;
                    $readNotifications = $totalNotifications - $unreadCount;
                @endphp
                @if($totalNotifications > 0)
                    <div class="px-3 py-2.5 bg-zinc-50/50 dark:bg-zinc-950/50 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between gap-2" x-data>
                        @if($readNotifications > 0)
                            <form method="POST" action="{{ route('notifications.destroy-read') }}" x-ref="clearReadNotifs">
                                @csrf
                                @method('DELETE')
                                <button type="button" @click.stop="VhSwal.confirmDelete('Hapus {{ $readNotifications }} notifikasi yang sudah dibaca?', $refs.clearReadNotifs)" class="text-[10px] font-semibold text-zinc-500 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                                    Hapus Sudah Dibaca
                                </button>
                            </form>
                        @else
                            <span></span>
                        @endif
                        <form method="POST" action="{{ route('notifications.destroy-all') }}" x-ref="clearAllNotifs">
                            @csrf
                            @method('DELETE')
                            <button type="button" @click.stop="VhSwal.confirmDelete('Hapus seluruh {{ $totalNotifications }} notifikasi? Data tidak bisa dikembalikan.', $refs.clearAllNotifs)" class="text-[10px] font-semibold text-rose-500 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 transition-colors">
                                Hapus Semua
                            </button>
                        </form>
                    </div>
                @endif
            </x-slot>
        </x-dropdown>

        <!-- Menu akun (ikon profil saja; detail di dropdown) -->
        <x-profile-menu />
    </div>
</header>
