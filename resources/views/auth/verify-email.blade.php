<x-guest-layout>
    <div class="w-full sm:max-w-md py-6">
        <div class="w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl px-7 sm:px-8 py-9 shadow-xl relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-[#BAFF39] via-emerald-500 to-teal-500"></div>

            <div class="flex flex-col items-center justify-center mb-6 text-center">
                <div class="mb-4">
                    <x-vh-logo size="md" />
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#BAFF39]/25 dark:bg-[#BAFF39]/15 text-[#3e6106] dark:text-[#BAFF39] text-[10px] font-extrabold uppercase tracking-widest mb-2">
                    <span class="material-symbols-outlined text-[14px]">mark_email_unread</span>
                    Verifikasi Email
                </span>
                <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">Cek Kotak Masuk Anda</h2>
            </div>

            <div class="mb-6 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed text-center font-medium">
                Terima kasih telah bergabung! Silakan verifikasi alamat email Anda dengan mengklik tautan yang baru saja kami kirimkan. Belum menerima email? Kami siap mengirimkan ulang.
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-6 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 px-4 py-3 font-bold text-xs text-emerald-700 dark:text-emerald-400 text-center">
                    Tautan verifikasi baru telah dikirim ke alamat email yang terdaftar pada akun Anda.
                </div>
            @endif

            <div class="space-y-3">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="w-full flex justify-center items-center gap-2 py-3.5 px-4 rounded-xl shadow-md text-sm font-black text-zinc-950 bg-[#BAFF39] hover:bg-[#a8eb28] transition-all">
                        <span class="material-symbols-outlined text-[18px]">forward_to_inbox</span>
                        Kirim Ulang Email Verifikasi
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="text-center pt-2">
                    @csrf
                    <button type="submit" class="text-xs font-bold text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white underline underline-offset-4 transition-colors">
                        Keluar dari Akun
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>


