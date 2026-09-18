        <!-- ============================================================ -->
        <!-- TAB 2.5: MASKOT INTERAKTIF (PAGE MASCOT)                     -->
        <!-- ============================================================ -->
        <div id="tab-pane-mascot" class="tab-pane flex flex-col gap-4" style="display: none;">
            <!-- Gaya Tombol Peluncur Chat (Chat Launcher Style) -->
            <div class="bg-white border border-apple-border rounded-xl p-4 sm:p-5 shadow-apple-sm flex flex-col gap-4">
                <div class="pb-3 border-b border-apple-border flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-[14px] font-bold text-apple-textPrimary flex items-center gap-2">
                            <span>Gaya Tombol Peluncur Chat (Chat Launcher Style)</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Koboyo Mascot</span>
                        </h3>
                        <p class="text-[11.5px] text-apple-textSecondary mt-0.5">Pilih gaya tombol terapung di sudut website Anda: balon pesan klasik atau maskot interaktif yang matanya mengikuti kursor pengunjung.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-apple-border bg-apple-canvas/30 hover:bg-white cursor-pointer transition-all has-[:checked]:border-apple-blue has-[:checked]:bg-blue-50/40 has-[:checked]:ring-1 has-[:checked]:ring-apple-blue" onclick="toggleLauncherType('default')">
                        <input type="radio" name="launcher_type" value="default" {{ ($widgetSetting->launcher_type ?? 'default') === 'default' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-apple-blue focus:ring-apple-blue">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-blue-100 text-apple-blue flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                </span>
                                <span class="text-[13px] font-bold text-apple-textPrimary">Balon Chat Standar (Default)</span>
                            </div>
                            <p class="text-[11px] text-apple-textSecondary mt-1.5 leading-relaxed">Tombol melayang bundar dengan ikon balon pesan klasik. Menggunakan warna aksen brand yang Anda pilih.</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-apple-border bg-apple-canvas/30 hover:bg-white cursor-pointer transition-all has-[:checked]:border-apple-blue has-[:checked]:bg-blue-50/40 has-[:checked]:ring-1 has-[:checked]:ring-apple-blue" onclick="toggleLauncherType('mascot')">
                        <input type="radio" name="launcher_type" value="mascot" {{ ($widgetSetting->launcher_type ?? 'default') === 'mascot' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-apple-blue focus:ring-apple-blue">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-[15px] shrink-0">🦊</span>
                                <span class="text-[13px] font-bold text-apple-textPrimary">Maskot Interaktif (Page Mascot)</span>
                                <span class="text-[10px] px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 font-semibold">Interactive</span>
                            </div>
                            <p class="text-[11px] text-apple-textSecondary mt-1.5 leading-relaxed">Karakter maskot animasi lucu yang menoleh mengikuti kursor mouse pengunjung web, berkedip, dan berekspresi riang saat diklik.</p>
                        </div>
                    </label>
                </div>

                <!-- Sub Panel Mascot Settings -->
                <div id="sectionMascotOptions" class="{{ ($widgetSetting->launcher_type ?? 'default') === 'mascot' ? '' : 'hidden' }} flex flex-col gap-4 pt-4 border-t border-apple-border">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                        <!-- Left: Character Selection & Controls (7 Cols) -->
                        <div class="lg:col-span-7 flex flex-col gap-4">
                            <div>
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                                    <div>
                                        <label class="block text-[12px] font-semibold text-apple-textPrimary">Pilih Karakter Maskot</label>
                                        <span class="text-[10.5px] text-apple-textSecondary">Tersedia 58 karakter lengkap: Orang &amp; Profesi, Mesin &amp; Robot, Hewan, dan Art Style.</span>
                                    </div>
                                    <div class="relative w-full sm:w-48">
                                        <input type="text" id="inputSearchMascot" placeholder="Cari maskot..." oninput="searchMascots(this.value)" class="w-full text-[11px] px-2.5 py-1.5 pl-7 bg-apple-canvas/50 border border-apple-border rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-apple-blue">
                                        <svg class="w-3.5 h-3.5 text-neutral-400 absolute left-2 top-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                    </div>
                                </div>

                                <!-- Category Filter Tabs -->
                                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1.5 mb-2.5">
                                    <button type="button" onclick="filterMascotsCategory('all', this)" class="mascot-cat-btn px-2.5 py-1 rounded-lg text-[11px] font-bold bg-apple-blue text-white shadow-2xs transition shrink-0">
                                        ✨ Semua (58)
                                    </button>
                                    <button type="button" onclick="filterMascotsCategory('people', this)" class="mascot-cat-btn px-2.5 py-1 rounded-lg text-[11px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas border border-apple-border transition shrink-0">
                                        🧑 Orang &amp; Profesi (19)
                                    </button>
                                    <button type="button" onclick="filterMascotsCategory('machines', this)" class="mascot-cat-btn px-2.5 py-1 rounded-lg text-[11px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas border border-apple-border transition shrink-0">
                                        🤖 Mesin &amp; Robot (13)
                                    </button>
                                    <button type="button" onclick="filterMascotsCategory('animals', this)" class="mascot-cat-btn px-2.5 py-1 rounded-lg text-[11px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas border border-apple-border transition shrink-0">
                                        🐾 Hewan (21)
                                    </button>
                                    <button type="button" onclick="filterMascotsCategory('art', this)" class="mascot-cat-btn px-2.5 py-1 rounded-lg text-[11px] font-medium text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas border border-apple-border transition shrink-0">
                                        🎨 Art Style (5)
                                    </button>
                                </div>

                                @php
                                    $allMascotsList = [
                                        // 🧑 Orang & Profesi
                                        ['id' => 'astronaut', 'name' => 'Astronaut', 'desc' => 'Penjelajah Antariksa', 'cat' => 'people'],
                                        ['id' => 'nurse', 'name' => 'Nurse', 'desc' => 'Tenaga Medis', 'cat' => 'people'],
                                        ['id' => 'chef', 'name' => 'Chef', 'desc' => 'Koki & Kuliner', 'cat' => 'people'],
                                        ['id' => 'scientist', 'name' => 'Scientist', 'desc' => 'Ilmuwan & Sains', 'cat' => 'people'],
                                        ['id' => 'hijabi', 'name' => 'Hijabi', 'desc' => 'Muslimah Ramah', 'cat' => 'people'],
                                        ['id' => 'builder', 'name' => 'Builder', 'desc' => 'Teknisi & Konstruksi', 'cat' => 'people'],
                                        ['id' => 'glasses', 'name' => 'Glasses', 'desc' => 'Kacamata Cerdas', 'cat' => 'people'],
                                        ['id' => 'beard', 'name' => 'Beard', 'desc' => 'Pria Brewok', 'cat' => 'people'],
                                        ['id' => 'cap', 'name' => 'Cap Boy', 'desc' => 'Kasual Topi', 'cat' => 'people'],
                                        ['id' => 'afro', 'name' => 'Afro', 'desc' => 'Gaya Afro', 'cat' => 'people'],
                                        ['id' => 'bald', 'name' => 'Bald', 'desc' => 'Plontos Ramah', 'cat' => 'people'],
                                        ['id' => 'ballerina', 'name' => 'Ballerina', 'desc' => 'Penari Balet', 'cat' => 'people'],
                                        ['id' => 'grandpa', 'name' => 'Grandpa', 'desc' => 'Kakek Bijak', 'cat' => 'people'],
                                        ['id' => 'granny', 'name' => 'Granny', 'desc' => 'Nenek Penyayang', 'cat' => 'people'],
                                        ['id' => 'kamran', 'name' => 'Kamran', 'desc' => 'Karakter Ceria', 'cat' => 'people'],
                                        ['id' => 'pirate', 'name' => 'Pirate', 'desc' => 'Bajak Laut Petualang', 'cat' => 'people'],
                                        ['id' => 'sikh', 'name' => 'Sikh', 'desc' => 'Bersorban Khas', 'cat' => 'people'],
                                        ['id' => 'skater', 'name' => 'Skater', 'desc' => 'Pemain Skateboard', 'cat' => 'people'],
                                        ['id' => 'wizard', 'name' => 'Wizard', 'desc' => 'Penyihir Ajaib', 'cat' => 'people'],

                                        // 🤖 Mesin & Robot
                                        ['id' => 'gearbot', 'name' => 'Gearbot', 'desc' => 'Robot Roda Gigi', 'cat' => 'machines'],
                                        ['id' => 'drone', 'name' => 'Drone', 'desc' => 'Drone Terbang Mini', 'cat' => 'machines'],
                                        ['id' => 'postbot', 'name' => 'Postbot', 'desc' => 'Robot Kurir Pesan', 'cat' => 'machines'],
                                        ['id' => 'scout', 'name' => 'Scout Bot', 'desc' => 'Robot Penjelajah', 'cat' => 'machines'],
                                        ['id' => 'clockwork', 'name' => 'Clockwork', 'desc' => 'Mesin Jam Antik', 'cat' => 'machines'],
                                        ['id' => 'crt', 'name' => 'CRT Monitor', 'desc' => 'Layar Komputer Retro', 'cat' => 'machines'],
                                        ['id' => 'tv', 'name' => 'Retro TV', 'desc' => 'Televisi Antena Klasik', 'cat' => 'machines'],
                                        ['id' => 'radio', 'name' => 'Retro Radio', 'desc' => 'Radio Gelombang Suara', 'cat' => 'machines'],
                                        ['id' => 'rocket', 'name' => 'Rocket', 'desc' => 'Roket Antariksa', 'cat' => 'machines'],
                                        ['id' => 'toaster', 'name' => 'Toaster', 'desc' => 'Pemanggang Roti Ceria', 'cat' => 'machines'],
                                        ['id' => 'cube', 'name' => 'Cyber Cube', 'desc' => 'Kubus Digital', 'cat' => 'machines'],
                                        ['id' => 'knight', 'name' => 'Knight Bot', 'desc' => 'Robot Ksatria Zirah', 'cat' => 'machines'],
                                        ['id' => 'lantern', 'name' => 'Lantern', 'desc' => 'Lentera Cahaya', 'cat' => 'machines'],

                                        // 🐾 Hewan
                                        ['id' => 'fox', 'name' => 'Fox (Rubah)', 'desc' => 'Lincah & Ramah', 'cat' => 'animals'],
                                        ['id' => 'cat', 'name' => 'Cat (Kucing)', 'desc' => 'Lucu & Santai', 'cat' => 'animals'],
                                        ['id' => 'panda', 'name' => 'Panda', 'desc' => 'Menggemaskan', 'cat' => 'animals'],
                                        ['id' => 'bunny', 'name' => 'Bunny (Kelinci)', 'desc' => 'Ceria & Aktif', 'cat' => 'animals'],
                                        ['id' => 'bear', 'name' => 'Bear (Beruang)', 'desc' => 'Hangat & Ramah', 'cat' => 'animals'],
                                        ['id' => 'otter', 'name' => 'Otter (Berang)', 'desc' => 'Playful & Cerdas', 'cat' => 'animals'],
                                        ['id' => 'owl', 'name' => 'Owl (Hantu)', 'desc' => 'Bijak & Waspada', 'cat' => 'animals'],
                                        ['id' => 'dino', 'name' => 'Dino', 'desc' => 'Dinosaurus Lucu', 'cat' => 'animals'],
                                        ['id' => 'frog', 'name' => 'Frog (Katak)', 'desc' => 'Katak Riang', 'cat' => 'animals'],
                                        ['id' => 'hamster', 'name' => 'Hamster', 'desc' => 'Pipi Tembem', 'cat' => 'animals'],
                                        ['id' => 'hedgehog', 'name' => 'Hedgehog', 'desc' => 'Landak Mini', 'cat' => 'animals'],
                                        ['id' => 'koala', 'name' => 'Koala', 'desc' => 'Tenang & Manis', 'cat' => 'animals'],
                                        ['id' => 'mouse', 'name' => 'Mouse (Tikus)', 'desc' => 'Mungil & Gesit', 'cat' => 'animals'],
                                        ['id' => 'penguin', 'name' => 'Penguin', 'desc' => 'Pinguin Kutub', 'cat' => 'animals'],
                                        ['id' => 'pug', 'name' => 'Pug Dog', 'desc' => 'Anjing Pug Setia', 'cat' => 'animals'],
                                        ['id' => 'raccoon', 'name' => 'Raccoon', 'desc' => 'Cerdik Bertopeng', 'cat' => 'animals'],
                                        ['id' => 'redpanda', 'name' => 'Red Panda', 'desc' => 'Panda Merah', 'cat' => 'animals'],
                                        ['id' => 'sheep', 'name' => 'Sheep (Domba)', 'desc' => 'Bulu Lembut', 'cat' => 'animals'],
                                        ['id' => 'sloth', 'name' => 'Sloth (Kukang)', 'desc' => 'Santai & Rileks', 'cat' => 'animals'],
                                        ['id' => 'tiger', 'name' => 'Tiger (Harimau)', 'desc' => 'Gagah & Berani', 'cat' => 'animals'],
                                        ['id' => 'deer', 'name' => 'Deer (Rusa)', 'desc' => 'Tanduk Anggun', 'cat' => 'animals'],

                                        // 🎨 Art Styles
                                        ['id' => 'fox-pixel', 'name' => 'Fox Pixel', 'desc' => 'Game 8-Bit Retro', 'cat' => 'art'],
                                        ['id' => 'fox-ink', 'name' => 'Fox Ink', 'desc' => 'Tinta Hitam Putih', 'cat' => 'art'],
                                        ['id' => 'fox-sketch', 'name' => 'Fox Sketch', 'desc' => 'Sketsa Pensil', 'cat' => 'art'],
                                        ['id' => 'fox-riso', 'name' => 'Fox Riso', 'desc' => 'Cetak Risograf 2-Warna', 'cat' => 'art'],
                                        ['id' => 'fox-paper', 'name' => 'Fox Paper', 'desc' => 'Guntingan Kertas', 'cat' => 'art'],
                                    ];
                                    $currentMascot = $widgetSetting->mascot_id ?? 'fox';
                                @endphp

                                <input type="hidden" name="mascot_id" id="inputSelectedMascotId" value="{{ $currentMascot }}">

                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 max-h-72 overflow-y-auto pr-1 border border-apple-border/50 rounded-xl p-2 bg-apple-canvas/20" id="mascotCardsContainer">
                                    @foreach($allMascotsList as $m)
                                        <div class="mascot-card flex flex-col items-center justify-center p-2.5 rounded-xl border {{ $currentMascot === $m['id'] ? 'border-apple-blue bg-blue-50/50 ring-1 ring-apple-blue' : 'border-apple-border bg-white hover:bg-apple-canvas/40' }} cursor-pointer transition text-center group select-none" data-mascot="{{ $m['id'] }}" data-cat="{{ $m['cat'] }}" data-name="{{ strtolower($m['name']) }} {{ strtolower($m['desc']) }}" onclick="selectMascot('{{ $m['id'] }}')">
                                            <div class="w-12 h-12 rounded-full overflow-hidden bg-slate-50 shadow-2xs border border-apple-border/50 flex items-center justify-center mb-1 group-hover:scale-110 transition-transform">
                                                <div class="w-12 h-12" style="background-image: url('/mascots/{{ $m['id'] }}-directions.webp'); background-size: 300% 300%; background-position: 50% 50%;"></div>
                                            </div>
                                            <span class="text-[11.5px] font-bold text-apple-textPrimary leading-tight">{{ $m['name'] }}</span>
                                            <span class="text-[9.5px] text-apple-textTertiary mt-0.5 line-clamp-1">{{ $m['desc'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Controls: Size & Cursor Tracking -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-apple-border/60">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="text-[11.5px] font-semibold text-apple-textPrimary">Ukuran Maskot</label>
                                        <span id="labelMascotSize" class="text-[11px] font-mono font-bold text-apple-blue">{{ $widgetSetting->mascot_size ?? 72 }}px</span>
                                    </div>
                                    <input type="range" name="mascot_size" id="inputMascotSize" min="54" max="96" step="2" value="{{ $widgetSetting->mascot_size ?? 72 }}" oninput="updateMascotSize(this.value)" class="w-full accent-apple-blue cursor-pointer">
                                    <div class="flex justify-between text-[10px] text-apple-textTertiary mt-0.5">
                                        <span>Kecil (54px)</span>
                                        <span>Normal (72px)</span>
                                        <span>Besar (96px)</span>
                                    </div>
                                </div>

                                <div class="flex flex-col justify-center">
                                    <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-apple-border bg-apple-canvas/30 hover:bg-white cursor-pointer transition">
                                        <input type="checkbox" name="mascot_tracking" value="1" id="inputMascotTracking" {{ ($widgetSetting->mascot_tracking ?? true) ? 'checked' : '' }} onchange="toggleMascotTracking(this.checked)" class="w-4 h-4 text-apple-blue rounded focus:ring-apple-blue">
                                        <div>
                                            <span class="text-[11.5px] font-bold text-apple-textPrimary block">Gerakan Ikuti Kursor</span>
                                            <span class="text-[10px] text-apple-textSecondary">Mata &amp; kepala menoleh mengikuti kursor mouse</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Live Interactive Sandbox Preview (5 Cols) -->
                        <div class="lg:col-span-5 flex flex-col">
                            <div class="bg-gradient-to-b from-slate-50 to-slate-100/70 border border-slate-200 rounded-xl p-4 flex flex-col items-center justify-center relative overflow-hidden h-full min-h-[220px]" id="adminMascotSandbox">
                                <div class="absolute top-2.5 left-3 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="text-[10.5px] font-semibold text-slate-600 uppercase tracking-wider">Live Preview</span>
                                </div>
                                <span class="absolute top-2.5 right-3 text-[10px] text-slate-400 font-medium">Arahkan kursor &amp; klik</span>

                                <!-- Mascot Character in Sandbox -->
                                <div class="flex flex-col items-center justify-center my-auto cursor-pointer select-none group" id="adminMascotPreviewWrapper" onclick="pokeAdminMascot()" title="Klik untuk menggelitik maskot!">
                                    <div id="adminMascotPreviewSprite" class="transition-transform group-hover:scale-105" style="width: {{ $widgetSetting->mascot_size ?? 72 }}px; height: {{ $widgetSetting->mascot_size ?? 72 }}px; background-image: url('/mascots/{{ $currentMascot }}-directions.webp'); background-size: 300% 300%; background-position: 50% 50%;"></div>
                                    <div class="w-10 h-2 rounded-full bg-black/10 blur-[2px] mt-1"></div>
                                </div>

                                <div class="mt-auto pt-2 text-center">
                                    <p class="text-[11px] font-medium text-slate-600" id="adminMascotStatusText">Arahkan mouse di sekitar kotak ini</p>
                                    <p class="text-[10px] text-slate-400">Maskot menoleh 9 arah sesuai posisi kursor</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
