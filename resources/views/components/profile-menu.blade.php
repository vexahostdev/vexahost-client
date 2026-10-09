{{--
    Menu akun di kanan atas dashboard (Admin & Client Panel), disamakan dengan project vexahost:
    di bilah atas hanya ikon profil (tanpa foto — foto tidak disimpan di database),
    nama, email, dan peran tampil di dalam dropdown.
--}}
@php
    $akun = auth()->user();
    $peran = $akun->getRoleNames()->map(fn ($r) => \Illuminate\Support\Str::headline($r))->join(', ');
@endphp

<div class="relative" x-data="{ userDropdown: false }" @click.outside="userDropdown = false" @keydown.escape.window="userDropdown = false">
    <button type="button" @click="userDropdown = ! userDropdown"
            class="flex items-center p-1 rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors focus:outline-none"
            :aria-expanded="userDropdown" aria-haspopup="true" aria-label="Menu akun" title="{{ $akun->name }}">
        <div class="rounded-full bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0"
             style="width: 32px; height: 32px; min-width: 32px; min-height: 32px; border-radius: 9999px; aspect-ratio: 1 / 1;">
            <svg class="shrink-0" style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </button>

    <div x-show="userDropdown" x-cloak x-transition style="display: none;"
         class="absolute right-0 mt-2 w-60 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-lg py-1.5 z-50 text-sm">
        <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-800 flex items-center gap-3">
            <div class="rounded-full bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0"
                 style="width: 40px; height: 40px; min-width: 40px; min-height: 40px; border-radius: 9999px; aspect-ratio: 1 / 1;">
                <svg class="shrink-0" style="width: 22px; height: 22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="font-bold text-zinc-900 dark:text-white truncate">{{ $akun->name }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 font-mono truncate">{{ $akun->email }}</p>
                @if ($peran)
                    <p class="mt-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 truncate">{{ $peran }}</p>
                @endif
            </div>
        </div>

        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800">Pengaturan Akun</a>

        @if ($akun->isClient())
            <a href="{{ route('invoices.index') }}" class="block px-4 py-2 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800">Tagihan Saya</a>
        @endif

        @can('users.manage')
            <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800">Kelola Pengguna</a>
        @endcan

        @can('settings.manage')
            <a href="{{ route('admin.settings.company.edit') }}" class="block px-4 py-2 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800">Pengaturan Perusahaan</a>
        @endcan

        <div class="border-t border-zinc-100 dark:border-zinc-800 my-1"></div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full text-left px-4 py-2 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 font-medium">Keluar</button>
        </form>
    </div>
</div>
