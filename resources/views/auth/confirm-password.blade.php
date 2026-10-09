<x-guest-layout>
    <div class="w-full sm:max-w-md py-6">
        <div class="w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl px-7 sm:px-8 py-9 shadow-xl relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-[#BAFF39] via-emerald-500 to-teal-500"></div>

            <div class="flex flex-col items-center justify-center mb-6 text-center">
                <div class="mb-4">
                    <x-vh-logo size="md" />
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#BAFF39]/25 dark:bg-[#BAFF39]/15 text-[#3e6106] dark:text-[#BAFF39] text-[10px] font-extrabold uppercase tracking-widest mb-2">
                    <span class="material-symbols-outlined text-[14px]">shield_lock</span>
                    Area Terlindungi
                </span>
                <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">Konfirmasi Kata Sandi</h2>
            </div>

            <div class="mb-6 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed text-center font-medium">
                Ini adalah area aman pada portal VexaHost. Harap konfirmasi kata sandi Anda sebelum melanjutkan.
            </div>

            <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">Kata Sandi</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan kata sandi Anda"
                           class="block w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-zinc-900 dark:text-zinc-50 placeholder-zinc-400 dark:placeholder-zinc-500 focus:border-emerald-600 dark:focus:border-[#BAFF39] focus:ring focus:ring-emerald-500/20 dark:focus:ring-[#BAFF39]/20 transition-all px-4 py-3 text-sm font-medium">
                    @error('password')
                        <p class="text-rose-600 dark:text-rose-400 text-xs mt-2 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full flex justify-center items-center gap-2 py-3.5 px-4 rounded-xl shadow-md text-sm font-black text-zinc-950 bg-[#BAFF39] hover:bg-[#a8eb28] transition-all">
                    <span class="material-symbols-outlined text-[18px]">verified_user</span>
                    Konfirmasi Akses
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>

