<!-- MODAL TAMBAH ANGGOTA TIM -->
<div class="modal-backdrop fixed inset-0 bg-apple-textPrimary/25 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden" id="modalNewTeamMember" onclick="if(event.target===this) closeModal('modalNewTeamMember')">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-apple-modal border border-apple-border overflow-hidden">
        <div class="px-4 py-3.5 border-b border-apple-border flex items-center justify-between bg-apple-canvas/50">
            <h4 class="font-semibold text-[14px] text-apple-textPrimary">Invite Team Member</h4>
            <button type="button" class="w-6 h-6 rounded-full bg-black/5 hover:bg-black/10 flex items-center justify-center text-apple-textSecondary transition text-[12px]" onclick="closeModal('modalNewTeamMember')">✕</button>
        </div>
        <form action="{{ route('admin.team.store') }}" method="POST">
            @csrf
            <div class="p-4 flex flex-col gap-3.5 text-[12px]">
                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="staffName">Nama Lengkap</label>
                    <input type="text" id="staffName" name="name" class="w-full px-3 py-1.5 border border-apple-border rounded-lg focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="Contoh: Sarah CS" required>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="staffUsername">Username</label>
                    <input type="text" id="staffUsername" name="username" class="w-full px-3 py-1.5 border border-apple-border rounded-lg font-mono text-[11.5px] focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="sarah_cs (tanpa spasi)">
                    <span class="text-[10.5px] text-apple-textTertiary mt-0.5 block">Digunakan untuk login cepat ke dasbor.</span>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="staffEmail">Alamat Email</label>
                    <input type="email" id="staffEmail" name="email" class="w-full px-3 py-1.5 border border-apple-border rounded-lg focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="sarah@indraco.com" required>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="staffTelegram">Telegram Username <span class="text-[10.5px] font-normal text-sky-600">(Sinkronisasi CS via Telegram)</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1.5 text-apple-textTertiary font-mono text-[11.5px]">&#64;</span>
                        <input type="text" id="staffTelegram" name="telegram_username" class="w-full pl-7 pr-3 py-1.5 border border-apple-border rounded-lg font-mono text-[11.5px] focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="username_telegram">
                    </div>
                    <span class="text-[10.5px] text-apple-textTertiary mt-0.5 block">Saat staf membalas di Telegram, sistem akan otomatis mencocokkan identitas akun CS ini.</span>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="staffPassword">Password Awal</label>
                    <div class="relative">
                        <input type="password" id="staffPassword" name="password" class="w-full pl-3 pr-10 py-1.5 border border-apple-border rounded-lg focus:outline-none focus:ring-2 focus:ring-apple-blue/20" placeholder="Minimal 8 karakter" required minlength="8">
                        <button type="button" onclick="togglePasswordVisibility('staffPassword', this)" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-apple-textTertiary hover:text-apple-textPrimary p-1 cursor-pointer" title="Tampilkan / Sembunyikan">
                            <svg class="eye-icon w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <svg class="eye-off-icon w-4 h-4 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-apple-textPrimary mb-1" for="staffRole">Peran / Role</label>
                    <select id="staffRole" name="role" class="w-full px-3 py-1.5 border border-apple-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20 cursor-pointer" required>
                        <option value="agent" selected>Agent (Staf CS Operasional)</option>
                        <option value="superadmin">Superadmin (Akses Penuh Manajemen)</option>
                    </select>
                </div>
            </div>
            <div class="px-4 py-3 border-t border-apple-border flex justify-end gap-2 bg-apple-canvas/40">
                <button type="button" class="px-3 py-1.5 rounded-lg border border-apple-border text-apple-textSecondary hover:bg-white text-[11.5px] font-medium" onclick="closeModal('modalNewTeamMember')">Batal</button>
                <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-apple-blue text-white hover:bg-apple-blueHover text-[11.5px] font-medium shadow-apple-sm">Daftarkan Staf</button>
            </div>
        </form>
    </div>
</div>
