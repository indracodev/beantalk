        <!-- ============================================================ -->
        <!-- TAB 2: APPEARANCE & BRANDING                                 -->
        <!-- ============================================================ -->
        <div id="tab-pane-appearance" class="tab-pane flex flex-col gap-4" style="display: none;">
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                <div class="pb-3 border-b border-apple-border">
                    <h3 class="text-[14px] font-bold text-apple-textPrimary">Warna &amp; Teks Header Widget</h3>
                    <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Kustomisasi identitas visual chat widget agar selaras dengan brand website Anda.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Widget Language -->
                    <div class="md:col-span-2 pb-3 border-b border-apple-border/60">
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1.5">Bahasa Tampilan Widget (Widget UI Language)</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-apple-border bg-apple-canvas/30 hover:bg-white cursor-pointer transition-all has-[:checked]:border-apple-blue has-[:checked]:bg-blue-50/40 has-[:checked]:ring-1 has-[:checked]:ring-apple-blue">
                                <input type="radio" name="language" value="id" {{ ($widgetSetting->language ?? 'id') === 'id' ? 'checked' : '' }} class="w-4 h-4 text-apple-blue focus:ring-apple-blue">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[15px]">🇮🇩</span>
                                        <span class="text-[12.5px] font-bold text-apple-textPrimary">Bahasa Indonesia</span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-neutral-100 text-neutral-600 font-medium">Default</span>
                                    </div>
                                    <p class="text-[10.5px] text-apple-textSecondary mt-0.5">Teks balon chat, salam sapa, formulir nama, aksi selesaikan tiket, dan riwayat obrolan dalam Bahasa Indonesia.</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-apple-border bg-apple-canvas/30 hover:bg-white cursor-pointer transition-all has-[:checked]:border-apple-blue has-[:checked]:bg-blue-50/40 has-[:checked]:ring-1 has-[:checked]:ring-apple-blue">
                                <input type="radio" name="language" value="en" {{ ($widgetSetting->language ?? 'id') === 'en' ? 'checked' : '' }} class="w-4 h-4 text-apple-blue focus:ring-apple-blue">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[15px]">🇬🇧</span>
                                        <span class="text-[12.5px] font-bold text-apple-textPrimary">English</span>
                                    </div>
                                    <p class="text-[10.5px] text-apple-textSecondary mt-0.5">Chat bubble, greeting prompts, visitor name intro, resolve ticket button, and tickets history in English.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Brand Color -->
                    <div>
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Warna Aksen Utama (Primary Brand Color)</label>
                        <div class="flex items-center gap-2.5">
                            <input type="color" id="inputColorPicker" name="primary_color" value="{{ $widgetSetting->primary_color }}" onchange="updateBrandColor(this.value)" class="w-9 h-9 rounded-lg border border-apple-border cursor-pointer p-0.5 bg-white shrink-0">
                            <input type="text" id="inputColorHex" value="{{ $widgetSetting->primary_color }}" oninput="updateBrandColor(this.value)" class="w-28 text-[12px] font-mono uppercase px-3 py-1.5 bg-apple-canvas/40 border border-apple-border rounded-lg">
                            
                            <!-- Presets -->
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <button type="button" onclick="updateBrandColor('#0071E3')" class="w-5 h-5 rounded-full bg-[#0071E3] border border-black/10 shadow-2xs cursor-pointer" title="Apple Blue"></button>
                                <button type="button" onclick="updateBrandColor('#C59B27')" class="w-5 h-5 rounded-full bg-[#C59B27] border border-black/10 shadow-2xs cursor-pointer" title="Gold Supresso"></button>
                                <button type="button" onclick="updateBrandColor('#10B981')" class="w-5 h-5 rounded-full bg-[#10B981] border border-black/10 shadow-2xs cursor-pointer" title="Emerald"></button>
                                <button type="button" onclick="updateBrandColor('#6366F1')" class="w-5 h-5 rounded-full bg-[#6366F1] border border-black/10 shadow-2xs cursor-pointer" title="Indigo"></button>
                                <button type="button" onclick="updateBrandColor('#1C1C1E')" class="w-5 h-5 rounded-full bg-[#1C1C1E] border border-black/10 shadow-2xs cursor-pointer" title="Dark Slate"></button>
                            </div>
                        </div>
                        <p class="text-[10.5px] text-apple-textTertiary mt-1">Warna tombol widget terapung, header popup obrolan, dan bubble pesan.</p>
                    </div>

                    <!-- Custom Support Button Title -->
                    <div>
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Teks Tombol Layanan (Support Button Title)</label>
                        <input type="text" name="support_title" value="{{ old('support_title', $widgetSetting->support_title ?: 'Customer Support') }}" placeholder="Contoh: Customer Support, Live Help" class="w-full text-[12px] px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20 focus:border-apple-blue">
                        <p class="text-[10.5px] text-apple-textTertiary mt-1">Muncul pada tombol mulai chat di Welcome Hub widget.</p>
                    </div>

                    <!-- Greeting Title -->
                    <div>
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Judul Sapaan Welcome Hub (Greeting Title)</label>
                        <input type="text" name="greeting_title" value="{{ old('greeting_title', $widgetSetting->greeting_title ?: 'Hallo!') }}" placeholder="Contoh: Hallo!, Selamat Datang!" class="w-full text-[12px] px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20 focus:border-apple-blue">
                    </div>

                    <!-- Greeting Subtitle -->
                    <div>
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Sub-judul Sapaan (Greeting Subtitle)</label>
                        <input type="text" name="greeting_subtitle" value="{{ old('greeting_subtitle', $widgetSetting->greeting_subtitle ?: 'Ada yang bisa kami bantu? Tanyakan informasi apapun di sini!') }}" placeholder="Contoh: Ada yang bisa kami bantu?" class="w-full text-[12px] px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20 focus:border-apple-blue">
                    </div>

                    <!-- Find Us Title -->
                    <div class="md:col-span-2">
                        <label class="block text-[11.5px] font-semibold text-apple-textPrimary mb-1">Judul Saluran Eksternal (Find Us Section Title)</label>
                        <input type="text" name="find_us_title" value="{{ old('find_us_title', $widgetSetting->find_us_title ?: 'Find Us Somewhere Else') }}" placeholder="Contoh: Reach Us Anywhere Else, Saluran Resmi Lainnya" class="w-full text-[12px] px-3 py-2 bg-apple-canvas/40 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-apple-blue/20 focus:border-apple-blue">
                    </div>
                </div>
            </div>
        </div>
