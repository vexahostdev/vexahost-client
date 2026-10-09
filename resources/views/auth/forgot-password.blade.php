<x-guest-layout>
    <div class="w-full sm:max-w-md py-6">
        <div class="w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl px-7 sm:px-8 py-9 shadow-xl relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-[#BAFF39] via-emerald-500 to-teal-500"></div>

            <div class="flex flex-col items-center justify-center mb-6 text-center">
                <div class="mb-4">
                    <x-vh-logo size="md" />
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#BAFF39]/25 dark:bg-[#BAFF39]/15 text-[#3e6106] dark:text-[#BAFF39] text-[10px] font-extrabold uppercase tracking-widest mb-2">
                    <span class="material-symbols-outlined text-[14px]">mail_lock</span>
                    Pemulihan Akses
                </span>
                <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">Lupa Kata Sandi?</h2>
            </div>

            <div class="mb-6 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed text-center font-medium">
                Tidak masalah. Masukkan alamat email akun Anda, dan kami akan mengirimkan tautan resmi untuk mengatur ulang kata sandi Anda.
            </div>

            <x-auth-session-status class="mb-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 px-4 py-3 text-emerald-700 dark:text-emerald-400 font-bold text-xs" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">Alamat Email</label>
                    <div class="relative">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@perusahaan.com"
                               class="block w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-zinc-900 dark:text-zinc-50 placeholder-zinc-400 dark:placeholder-zinc-500 focus:border-emerald-600 dark:focus:border-[#BAFF39] focus:ring focus:ring-emerald-500/20 dark:focus:ring-[#BAFF39]/20 transition-all pl-4 pr-10 py-3 text-sm font-medium">
                        <span class="material-symbols-outlined text-[18px] text-zinc-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none">mail</span>
                    </div>
                    @error('email')
                        <p class="text-rose-600 dark:text-rose-400 text-xs mt-2 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full flex justify-center items-center gap-2 py-3.5 px-4 rounded-xl shadow-md text-sm font-black text-zinc-950 bg-[#BAFF39] hover:bg-[#a8eb28] transition-all">
                    <span class="material-symbols-outlined text-[18px]">send</span>
                    Kirim Tautan Reset Sandi
                </button>

                <div class="pt-2 text-center text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                    Sudah ingat kata sandi Anda?
                    <a href="{{ route('login') }}" class="font-bold text-emerald-600 dark:text-[#BAFF39] hover:underline ml-1">Kembali ke halaman Masuk</a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>

