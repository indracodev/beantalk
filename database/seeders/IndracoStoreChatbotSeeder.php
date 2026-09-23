<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\Project;
use App\Models\ProjectDomain;
use App\Models\Tenant;
use App\Models\WidgetSetting;
use Illuminate\Database\Seeder;

class IndracoStoreChatbotSeeder extends Seeder
{
    /**
     * Seed INDRACO Store project, widget settings, and structured chatbot knowledge tree with interactive options.
     */
    public function run()
    {
        // 1. Ensure Tenant exists
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'indraco'],
            [
                'name'      => 'PT Indraco Jaya Perkasa',
                'plan'      => 'enterprise',
                'is_active' => true,
            ]
        );

        app()->instance('current_tenant_id', $tenant->id);

        // 2. Ensure Project exists
        $project = Project::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'indracostore'],
            [
                'name'      => 'INDRACO Store',
                'is_active' => true,
            ]
        );

        // 3. Whitelist Domains for INDRACO Store
        $domains = [
            'indracostore.com',
            'www.indracostore.com',
            'beantalk.indracoglobal.com',
            'localhost',
            '127.0.0.1',
        ];

        foreach ($domains as $domain) {
            ProjectDomain::firstOrCreate(
                ['project_id' => $project->id, 'domain' => $domain],
                ['is_verified' => true]
            );
        }

        // 4. API Key for Live Widget Integration
        $publicKey = 'pk_live_indracostore_prod';
        $apiKey = ApiKey::firstOrCreate(
            ['public_key' => $publicKey],
            [
                'tenant_id'  => $tenant->id,
                'project_id' => $project->id,
                'name'       => 'INDRACO Store Web Widget Key',
                'is_active'  => true,
            ]
        );

        // 5. Bot Welcome Message & Rules with Interactive Options
        $welcomeMessage = "Halo! Terima kasih telah menghubungi *INDRACO Store*! 👋\nSilakan pilih menu bantuan di bawah ini:\n\n" .
            "• *Pembelian Produk* (Kopi, Non Kopi, Bumbu Dapur)\n" .
            "• *Informasi & Kerjasama* (Distributor, Reseller, Hadiah, Karir, Sponsor)\n" .
            "• *Kendala Pembelian di Toko Online* (Checkout, Voucher, Akun, Komplain)\n\n" .
            "💡 *Tips*: Anda dapat langsung mengklik tombol pilihan di bawah, mengetik kata kunci, atau klik *Bicara dengan CS* untuk terhubung langsung dengan tim kami. 😊🙏";

        $rules = array (
  0 => 
  array (
    'name' => 'Menu Utama',
    'keywords' => 
    array (
      0 => 'menu',
      1 => 'menu utama',
      2 => 'bantuan',
      3 => 'help',
      4 => 'mulai',
      5 => 'start',
      6 => 'halo',
      7 => 'hai',
      8 => 'pilihan',
    ),
    'response' => 'Halo! Terima kasih telah menghubungi *INDRACO Store*! 👋
Silakan pilih menu bantuan di bawah ini:

• *Pembelian Produk* (Kopi, Non Kopi, Bumbu Dapur)
• *Informasi & Kerjasama* (Distributor, Reseller, Hadiah, Karir, Sponsor)
• *Kendala Pembelian di Toko Online* (Checkout, Voucher, Akun, Komplain)

💡 *Tips*: Anda dapat langsung mengklik tombol pilihan di bawah, mengetik kata kunci, atau klik *Bicara dengan CS* untuk terhubung langsung dengan tim kami. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '📦 Pembelian Produk',
        'value' => '1',
      ),
      1 => 
      array (
        'label' => '🤝 Informasi & Kerjasama',
        'value' => '2',
      ),
      2 => 
      array (
        'label' => '🛠️ Kendala Belanja Online',
        'value' => '3',
      ),
      3 => 
      array (
        'label' => '💬 Bicara dengan CS',
        'value' => 'YA',
      ),
    ),
  ),
  1 => 
  array (
    'name' => 'Menu 1 - Pembelian Produk',
    'keywords' => 
    array (
      0 => '1',
      1 => 'menu 1',
      2 => 'pembelian produk',
      3 => 'kategori produk',
      4 => 'produk',
      5 => 'beli produk',
      6 => 'katalog',
    ),
    'response' => '📦 *Kategori Produk INDRACO Store*
Silakan pilih kategori produk yang ingin Anda ketahui:

• *Kopi* (Supresso, Tugu Buaya, Uang Emas, Rasa Sayang, CERIA, UCAFE)
• *Produk Non Kopi* (Jaheku, BROCHOCO)
• *Bumbu Dapur* (Intirasa)

Pilih salah satu kategori di bawah atau klik *Menu Utama* untuk kembali.',
    'options' => 
    array (
      0 => 
      array (
        'label' => '☕ Kopi',
        'value' => '1.1',
      ),
      1 => 
      array (
        'label' => '🍵 Produk Non Kopi',
        'value' => '1.2',
      ),
      2 => 
      array (
        'label' => '🍳 Bumbu Dapur (Intirasa)',
        'value' => '1.3',
      ),
      3 => 
      array (
        'label' => '🔙 Menu Utama',
        'value' => 'MENU',
      ),
      4 => 
      array (
        'label' => '💬 Bicara dengan CS',
        'value' => 'YA',
      ),
    ),
  ),
  2 => 
  array (
    'name' => 'Menu 1.1 - Kategori Kopi',
    'keywords' => 
    array (
      0 => '1.1',
      1 => 'kopi',
      2 => 'coffee',
      3 => 'kategori kopi',
      4 => 'brand kopi',
    ),
    'response' => '☕ *Koleksi Brand Kopi INDRACO*
Pilih brand kopi di bawah ini untuk melihat series & variannya:

• *Supresso Coffee* (Single Origin, Gourmet, The Collections, BaliCafe, World Blend)
• *Tugu Buaya* (Kopi Special, Coffee Mix)
• *Uang Emas* (Kopi Special)
• *Rasa Sayang* (Gold Special, Super Quality, SP Bintang, Kopi Bali, Coffee Mix)
• *CERIA* (Kopi Bubuk Halus)
• *UCAFE* (Pure Coffee, Instant Coffee)

Klik brand yang ingin Anda lihat atau kunjungi *www.indracostore.com*.',
    'options' => 
    array (
      0 => 
      array (
        'label' => '☕ Supresso Coffee',
        'value' => 'Supresso',
      ),
      1 => 
      array (
        'label' => '☕ Kopi Tugu Buaya',
        'value' => 'Tugu Buaya',
      ),
      2 => 
      array (
        'label' => '☕ Kopi Uang Emas',
        'value' => 'Uang Emas',
      ),
      3 => 
      array (
        'label' => '☕ Kopi Rasa Sayang',
        'value' => 'Rasa Sayang',
      ),
      4 => 
      array (
        'label' => '☕ Kopi CERIA',
        'value' => 'CERIA',
      ),
      5 => 
      array (
        'label' => '☕ UCAFE Coffee',
        'value' => 'UCAFE',
      ),
      6 => 
      array (
        'label' => '🔙 Kategori Produk',
        'value' => '1',
      ),
      7 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  3 => 
  array (
    'name' => 'Menu 1.2 - Produk Non Kopi',
    'keywords' => 
    array (
      0 => '1.2',
      1 => 'non kopi',
      2 => 'produk non kopi',
      3 => 'minuman non kopi',
    ),
    'response' => '🍵 *Koleksi Produk Non Kopi INDRACO*
Pilih brand minuman di bawah ini:

• *Jaheku Premium Ginger* (Minuman jahe hangat dengan bahan alami pilihan)
• *BROCHOCO Chocolate Drink* (Minuman cokelat premium Original & Mix)

Pilih brand di bawah untuk melihat varian lengkapnya:',
    'options' => 
    array (
      0 => 
      array (
        'label' => '🍵 Jaheku Premium Ginger',
        'value' => 'Jaheku',
      ),
      1 => 
      array (
        'label' => '🍫 BROCHOCO Chocolate Drink',
        'value' => 'Brochoco',
      ),
      2 => 
      array (
        'label' => '🔙 Kategori Produk',
        'value' => '1',
      ),
      3 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  4 => 
  array (
    'name' => 'Menu 1.3 - Bumbu Dapur',
    'keywords' => 
    array (
      0 => '1.3',
      1 => 'bumbu dapur',
      2 => 'bumbu',
      3 => 'intirasa',
    ),
    'response' => '🍳 *Koleksi Bumbu Dapur Intirasa*
Pilihan varian bumbu berkualitas untuk kebutuhan dapur dan usaha Anda:

• Bumbu Tabur
• Bumbu Tepung
• Bumbu Pelengkap & Dasar (Santan Instan, dll)
• Krimer Minuman & Masakan
• Bumbu Instan, Kaldu, & Rendam

Kunjungi *www.intirasa.com* untuk katalog lengkap:',
    'options' => 
    array (
      0 => 
      array (
        'label' => 'Bumbu Tabur',
        'value' => 'Bumbu Tabur',
      ),
      1 => 
      array (
        'label' => 'Bumbu Tepung',
        'value' => 'Bumbu Tepung',
      ),
      2 => 
      array (
        'label' => 'Bumbu Dasar & Pelengkap',
        'value' => 'Bumbu Dasar',
      ),
      3 => 
      array (
        'label' => 'Krimer',
        'value' => 'Krimer',
      ),
      4 => 
      array (
        'label' => 'Bumbu Instan & Kaldu',
        'value' => 'Bumbu Instan',
      ),
      5 => 
      array (
        'label' => '🔙 Kategori Produk',
        'value' => '1',
      ),
      6 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  5 => 
  array (
    'name' => 'Menu 2 - Informasi dan Kerjasama',
    'keywords' => 
    array (
      0 => '2',
      1 => 'menu 2',
      2 => 'informasi dan kerjasama',
      3 => 'kerjasama',
      4 => 'info kerjasama',
    ),
    'response' => '🤝 *Informasi & Kerjasama INDRACO Group*
Silakan pilih informasi yang Anda butuhkan:

• *Informasi Distributor Terdekat* (Tersedia di 38 Provinsi)
• *Cara Menjadi Reseller / Distributor*
• *Informasi Penukaran Hadiah* (Karton Tugu Buaya & Voucher Toko Online)
• *Lowongan Pekerjaan* (Karir di INDRACO Group)
• *Penawaran Kerjasama* (Event, Manufaktur, Supplier, Creative/Endorsement)

Pilih salah satu opsi di bawah ini:',
    'options' => 
    array (
      0 => 
      array (
        'label' => '📍 Info Distributor Terdekat',
        'value' => '2.1',
      ),
      1 => 
      array (
        'label' => '💼 Syarat Reseller / Distributor',
        'value' => '2.2',
      ),
      2 => 
      array (
        'label' => '🎁 Penukaran Hadiah Karton',
        'value' => '2.3',
      ),
      3 => 
      array (
        'label' => '💼 Lowongan Pekerjaan (Karir)',
        'value' => '2.4',
      ),
      4 => 
      array (
        'label' => '🤝 Sponsorship & Supplier',
        'value' => '2.5',
      ),
      5 => 
      array (
        'label' => '🔙 Menu Utama',
        'value' => 'MENU',
      ),
      6 => 
      array (
        'label' => '💬 Bicara dengan CS',
        'value' => 'YA',
      ),
    ),
  ),
  6 => 
  array (
    'name' => 'Menu 2.1 - Info Distributor Terdekat',
    'keywords' => 
    array (
      0 => '2.1',
      1 => 'distributor',
      2 => 'distributor terdekat',
      3 => 'agen terdekat',
      4 => 'lokasi distributor',
      5 => 'cari distributor',
      6 => 'aceh',
      7 => 'sumatra utara',
      8 => 'sumatera utara',
      9 => 'sumatra selatan',
      10 => 'sumatera selatan',
      11 => 'sumatra barat',
      12 => 'sumatera barat',
      13 => 'bengkulu',
      14 => 'riau',
      15 => 'kepulauan riau',
      16 => 'kepri',
      17 => 'jambi',
      18 => 'lampung',
      19 => 'bangka belitung',
      20 => 'babel',
      21 => 'kalimantan barat',
      22 => 'kalbar',
      23 => 'kalimantan timur',
      24 => 'kaltim',
      25 => 'kalimantan selatan',
      26 => 'kalsel',
      27 => 'kalimantan tengah',
      28 => 'kalteng',
      29 => 'kalimantan utara',
      30 => 'kaltara',
      31 => 'banten',
      32 => 'dki jakarta',
      33 => 'jakarta',
      34 => 'jawa barat',
      35 => 'jabar',
      36 => 'jawa tengah',
      37 => 'jateng',
      38 => 'daerah istimewa yogyakarta',
      39 => 'jogja',
      40 => 'yogyakarta',
      41 => 'jawa timur',
      42 => 'jatim',
      43 => 'surabaya',
      44 => 'bali',
      45 => 'nusa tenggara timur',
      46 => 'ntt',
      47 => 'nusa tenggara barat',
      48 => 'ntb',
      49 => 'gorontalo',
      50 => 'sulawesi barat',
      51 => 'sulbar',
      52 => 'sulawesi tengah',
      53 => 'sulteng',
      54 => 'sulawesi utara',
      55 => 'sulut',
      56 => 'sulawesi tenggara',
      57 => 'sultra',
      58 => 'sulawesi selatan',
      59 => 'sulsel',
      60 => 'makassar',
      61 => 'maluku utara',
      62 => 'maluku',
      63 => 'papua barat',
      64 => 'papua',
      65 => 'papua tengah',
      66 => 'papua pegunungan',
      67 => 'papua selatan',
      68 => 'papua barat daya',
    ),
    'response' => '📍 *Informasi Distributor Terdekat INDRACO*

Distributor tersebar hampir di seluruh provinsi di Indonesia. Untuk memudahkan Anda menemukan distributor terdekat, mohon lengkapi data berikut:

• *Nama*:
• *Nomor WhatsApp Aktif*:
• *Domisili (Kota/Provinsi)*:

Tim INDRACO akan segera menghubungi dan mengarahkan ke distributor terdekat.',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Hubungkan ke Tim CS Sekarang',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '💼 Syarat Menjadi Distributor/Reseller',
        'value' => '2.2',
      ),
      2 => 
      array (
        'label' => '🏠 Kembali ke Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  7 => 
  array (
    'name' => 'Menu 2.2 - Syarat Reseller & Distributor',
    'keywords' => 
    array (
      0 => '2.2',
      1 => 'reseller',
      2 => 'syarat reseller',
      3 => 'cara jadi reseller',
      4 => 'keagenan',
      5 => 'grosir',
      6 => 'partai besar',
      7 => 'beli banyak',
      8 => 'syarat distributor',
    ),
    'response' => '💼 *Syarat Menjadi Reseller / Pembelian Jumlah Banyak*

Terima kasih atas minat Anda bermitra dengan INDRACO Group. Untuk menjadi reseller atau pembelian grosir, siapkan brand/produk yang diinginkan, lalu informasikan:

• *Nama*:
• *Nomor WhatsApp Aktif*:
• *Domisili / Lokasi Usaha*:

Tim penjualan kami akan segera mengontak Anda untuk penawaran harga terbaik.',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Hubungkan ke Sales / CS',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '📍 Cek Distributor Terdekat',
        'value' => '2.1',
      ),
      2 => 
      array (
        'label' => '🏠 Kembali ke Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  8 => 
  array (
    'name' => 'Menu 2.3 - Penukaran Hadiah & Voucher',
    'keywords' => 
    array (
      0 => '2.3',
      1 => 'hadiah',
      2 => 'tukar hadiah',
      3 => 'karton',
      4 => 'penukaran hadiah',
      5 => 'tukar karton',
      6 => 'tugu buaya 12g',
    ),
    'response' => '🎁 *Program Penukaran Hadiah Karton Tugu Buaya 12g*

Kumpulkan kemasan karton Kopi Tugu Buaya 12g dan tukarkan dengan hadiah menarik:
• 10 Lembar: 1 Sachet Kopi Tugu Buaya 12g
• 50 Lembar: Piring Keramik
• 100 Lembar: Mangkok Keramik
• 250 Lembar: Kaos Eksklusif
• 500 Lembar: Jam Dinding Cantik
• 1.000 Lembar: Dispenser
• 2.500 Lembar: Magic Com
• 5.000 Lembar: Kulkas 1 Pintu
• 10.000 Lembar: Mesin Cuci 2 Tabung
• 17.000 Lembar: Smart TV 32 Inch!',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Tanya Lokasi Penukaran ke CS',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '☕ Info Varian Kopi Tugu Buaya',
        'value' => 'Tugu Buaya',
      ),
      2 => 
      array (
        'label' => '🏠 Kembali ke Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  9 => 
  array (
    'name' => 'Menu 2.4 - Lowongan Pekerjaan',
    'keywords' => 
    array (
      0 => '2.4',
      1 => 'karir',
      2 => 'career',
      3 => 'lowongan',
      4 => 'loker',
      5 => 'recruitment',
      6 => 'rekrutmen',
      7 => 'kerja',
      8 => 'lamar kerja',
    ),
    'response' => '💼 *Karir & Lowongan Pekerjaan INDRACO Group*

Informasi posisi terbuka (Manufaktur, Marketing, IT, Finance, HRD, dll) dapat diakses melalui:
🌐 *www.indraco.com/career*
Atau kirimkan CV & Portfolio Anda ke email:
📧 *recruitment@indraco.com*',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Tanya Info Karir ke Tim HR',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🏠 Kembali ke Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  10 => 
  array (
    'name' => 'Menu 2.5 - Kerjasama Sponsorship & Supplier',
    'keywords' => 
    array (
      0 => '2.5',
      1 => 'sponsor',
      2 => 'proposal',
      3 => 'supplier',
      4 => 'creative',
      5 => 'endorsement',
      6 => 'partnership',
      7 => 'kerjasama event',
      8 => 'mesin',
      9 => 'bahan baku',
    ),
    'response' => '🤝 *Penawaran Kerjasama & Sponsorship*

• *Event / Acara Kampus / Musik*: Kirim proposal ke *info@indraco.com* (Subjek: Event_Nama Acara_Kategori)
• *Supplier Kopi / Jahe / Bahan Baku*: Kirim penawaran ke *info@indraco.com* (Subjek: Supplier_Nama Perusahaan_Kategori Item)
• *Creative / Endorsement / Agency*: Kirim ke *dm@indraco.com* & *info@indraco.com* (Subjek: Creative_Nama Perusahaan_Kategori Item)',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Hubungkan ke Tim Kerjasama',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🏠 Kembali ke Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  11 => 
  array (
    'name' => 'Menu 3 - Kendala Pembelian di Toko Online',
    'keywords' => 
    array (
      0 => '3',
      1 => 'menu 3',
      2 => 'kendala',
      3 => 'masalah',
      4 => 'bantuan web',
      5 => 'komplain',
      6 => 'kendala belanja',
    ),
    'response' => '🛠️ *Pusat Bantuan & Kendala Belanja Online*
Silakan pilih kendala yang sedang Anda alami:',
    'options' => 
    array (
      0 => 
      array (
        'label' => '🛒 Kendala Checkout & Keranjang',
        'value' => '3.1',
      ),
      1 => 
      array (
        'label' => '🚚 Kendala Pengiriman & Alamat',
        'value' => '3.2',
      ),
      2 => 
      array (
        'label' => '🏷️ Kendala Voucher Promo',
        'value' => '3.3',
      ),
      3 => 
      array (
        'label' => '💳 Kendala Pembayaran',
        'value' => '3.4',
      ),
      4 => 
      array (
        'label' => '🔐 Kendala Akun & Password',
        'value' => '3.5',
      ),
      5 => 
      array (
        'label' => '📦 Komplain Produk Rusak',
        'value' => '3.6',
      ),
      6 => 
      array (
        'label' => '💬 Bicara dengan CS',
        'value' => 'YA',
      ),
      7 => 
      array (
        'label' => '🔙 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  12 => 
  array (
    'name' => 'Menu 3.1 - Kendala Checkout & Keranjang',
    'keywords' => 
    array (
      0 => '3.1',
      1 => 'checkout',
      2 => 'keranjang',
      3 => 'tidak bisa checkout',
      4 => 'gagal checkout',
      5 => 'tambah keranjang',
      6 => 'troli',
    ),
    'response' => '🛒 *Panduan Kendala Checkout & Keranjang*

1. Pastikan produk masih memiliki kuota stok (tidak Out of Stock).
2. Pilih opsi varian produk (jika ada) sebelum menekan \'Beli Sekarang\'.
3. Buka Keranjang, pastikan data penerima & alamat tujuan sudah benar.
4. Pilih opsi pengiriman dan metode pembayaran yang diinginkan.
5. Tekan tombol \'Proses Untuk Pembayaran\'.',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Tombol Masih Tidak Merespon (Bantuan CS)',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '💳 Kendala Pembayaran',
        'value' => '3.4',
      ),
      2 => 
      array (
        'label' => '🏠 Kembali ke Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  13 => 
  array (
    'name' => 'Menu 3.2 - Kendala Pengiriman',
    'keywords' => 
    array (
      0 => '3.2',
      1 => 'pengiriman',
      2 => 'kurir',
      3 => 'ekspedisi',
      4 => 'ongkir',
      5 => 'alamat',
      6 => 'tidak bisa pilih ekspedisi',
    ),
    'response' => '🚚 *Kendala Pilihan Pengiriman & Alamat*

1. Pastikan kolom provinsi, kota/kabupaten, dan kode pos terisi sesuai.
2. Pastikan alamat lengkap dan nomor WhatsApp aktif sudah benar.
3. Jika pilihan ekspedisi tidak muncul, coba refresh browser Anda.',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Hubungkan ke Tim CS',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🛒 Kendala Checkout',
        'value' => '3.1',
      ),
      2 => 
      array (
        'label' => '🏠 Kembali ke Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  14 => 
  array (
    'name' => 'Menu 3.3 - Kendala Voucher Promo',
    'keywords' => 
    array (
      0 => '3.3',
      1 => 'voucher',
      2 => 'promo',
      3 => 'diskon',
      4 => 'kode voucher',
      5 => 'kupon',
      6 => 'voucher salah',
      7 => 'voucher tidak berlaku',
    ),
    'response' => '🏷️ *Kendala Penggunaan Voucher Promo*

1. Pastikan kode voucher yang dimasukkan sesuai dengan huruf besar/kecilnya.
2. Pastikan voucher Anda masih dalam periode penggunaan dan belum melewati batas penukaran.
3. Jika voucher reguler toko, klik \'Makin Hemat Pakai Promo\' atau \'Ambil Voucher\' pada halaman keranjang.',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Butuh Bantuan Verifikasi Voucher (CS)',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🛒 Kembali ke Info Checkout',
        'value' => '3.1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  15 => 
  array (
    'name' => 'Menu 3.4 - Kendala Pembayaran',
    'keywords' => 
    array (
      0 => '3.4',
      1 => 'pembayaran',
      2 => 'bayar',
      3 => 'transfer',
      4 => 'virtual account',
      5 => 'va',
      6 => 'qris',
      7 => 'kode bayar',
      8 => 'tidak bisa bayar',
    ),
    'response' => '💳 *Kendala Pembayaran*

1. Pastikan koneksi internet stabil saat proses pembayaran.
2. Pastikan Anda telah memilih metode pembayaran yang tersedia (Manual Transfer, VA, Kartu Kredit, atau E-Wallet).
3. Pastikan memeriksa kembali produk yang diorder, alamat, dan ekspedisi.',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Konfirmasi Bukti Bayar ke CS',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🛒 Kendala Checkout',
        'value' => '3.1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  16 => 
  array (
    'name' => 'Menu 3.5 - Kendala Akun & Password',
    'keywords' => 
    array (
      0 => '3.5',
      1 => 'akun',
      2 => 'password',
      3 => 'lupa password',
      4 => 'tidak bisa login',
      5 => 'login',
      6 => 'ganti password',
    ),
    'response' => '🔐 *Kendala Login & Akun*

1. Pastikan Anda telah melakukan verifikasi email pada inbox email pendaftaran.
2. Jika lupa kata sandi, klik \'Lupa Password\' di pojok kanan atas halaman untuk reset kata sandi.
3. Pastikan email dan password yang dimasukkan sudah sesuai.',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bantuan Verifikasi Akun (CS)',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  17 => 
  array (
    'name' => 'Menu 3.6 - Komplain Produk & Kritik Saran',
    'keywords' => 
    array (
      0 => '3.6',
      1 => 'komplain',
      2 => 'rusak',
      3 => 'barang rusak',
      4 => 'kritik',
      5 => 'saran',
      6 => 'cacat',
      7 => 'retur',
      8 => 'kemasan rusak',
      9 => 'benda asing',
      10 => 'produk rusak',
    ),
    'response' => '📦 *Layanan Komplain & Kepuasan Pelanggan INDRACO*

Kepuasan Anda adalah prioritas kami. Jika terjadi kerusakan kemasan, produk tidak sesuai, atau terdapat saran layanan, mohon siapkan video unboxing dan kirimkan data:

• *Nama Anda*:
• *Produk yang dibeli*:
• *Tanggal transaksi & nomor order*:

Tim kami akan segera memproses penggantian atau tindak lanjut keluhan Anda.',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Laporkan ke Tim CS Sekarang',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  18 => 
  array (
    'name' => 'Brand Supresso',
    'keywords' => 
    array (
      0 => 'supresso',
      1 => 'supresso coffee',
    ),
    'response' => '☕ *Supresso Coffee*
Koleksi kopi premium Nusantara dan mancanegara dengan citarasa anggun dan kompleks. Pilih varian series Supresso di bawah ini:',
    'options' => 
    array (
      0 => 
      array (
        'label' => 'Single Origin',
        'value' => 'Single Origin',
      ),
      1 => 
      array (
        'label' => 'Gourmet Collections',
        'value' => 'Gourmet Collections',
      ),
      2 => 
      array (
        'label' => 'The Collections',
        'value' => 'The Collections',
      ),
      3 => 
      array (
        'label' => 'BaliCafe',
        'value' => 'BaliCafe',
      ),
      4 => 
      array (
        'label' => 'World Blend',
        'value' => 'World Blend',
      ),
      5 => 
      array (
        'label' => '🔙 Kategori Kopi',
        'value' => '1.1',
      ),
      6 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  19 => 
  array (
    'name' => 'Brand Tugu Buaya',
    'keywords' => 
    array (
      0 => 'tugu buaya',
      1 => 'kopi tugu buaya',
    ),
    'response' => '☕ *Kopi Tugu Buaya (Sejak 1977)*
Kopi hitam bubuk legendaris dengan rasa pahit seimbang dan aroma khas, Nikmat Setiap Saat. Tersedia berbagai ukuran gramasi serta program penukaran hadiah karton.',
    'options' => 
    array (
      0 => 
      array (
        'label' => 'Kopi Special',
        'value' => 'Tugu Buaya Kopi Special',
      ),
      1 => 
      array (
        'label' => 'Coffee Mix',
        'value' => 'Tugu Buaya Coffee Mix',
      ),
      2 => 
      array (
        'label' => '🎁 Info Tukar Hadiah Karton',
        'value' => '2.3',
      ),
      3 => 
      array (
        'label' => '🔙 Kategori Kopi',
        'value' => '1.1',
      ),
      4 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  20 => 
  array (
    'name' => 'Brand Uang Emas',
    'keywords' => 
    array (
      0 => 'uang emas',
      1 => 'kopi uang emas',
    ),
    'response' => '☕ *Kopi Uang Emas*
Kopi hitam bubuk dengan rasa mantap dan body tebal. Ampas kopi cepat turun saat diseduh, sangat cocok untuk stok di rumah maupun usaha warung kopi Anda.',
    'options' => 
    array (
      0 => 
      array (
        'label' => 'Kopi Special',
        'value' => 'Uang Emas Kopi Special',
      ),
      1 => 
      array (
        'label' => '🔙 Kategori Kopi',
        'value' => '1.1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  21 => 
  array (
    'name' => 'Brand Rasa Sayang',
    'keywords' => 
    array (
      0 => 'rasa sayang',
      1 => 'kopi rasa sayang',
    ),
    'response' => '☕ *Kopi Rasa Sayang - Citarasa Tempo Doeloe*
Diracik dari biji kopi pilihan lintas generasi dengan rasa mantap dan harum khas Nusantara.',
    'options' => 
    array (
      0 => 
      array (
        'label' => 'Kopi Gold Special',
        'value' => 'Kopi Gold Special',
      ),
      1 => 
      array (
        'label' => 'Kopi Super Quality',
        'value' => 'Kopi Super Quality',
      ),
      2 => 
      array (
        'label' => 'Kopi SP Bintang',
        'value' => 'Kopi SP Bintang',
      ),
      3 => 
      array (
        'label' => 'Kopi Bali',
        'value' => 'Kopi Bali',
      ),
      4 => 
      array (
        'label' => 'Coffee Mix',
        'value' => 'Rasa Sayang Coffee Mix',
      ),
      5 => 
      array (
        'label' => '🔙 Kategori Kopi',
        'value' => '1.1',
      ),
      6 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  22 => 
  array (
    'name' => 'Brand CERIA',
    'keywords' => 
    array (
      0 => 'ceria',
      1 => 'kopi ceria',
    ),
    'response' => '☕ *Kopi CERIA*
Kopi hitam bubuk dengan gilingan halus yang menghasilkan seduhan lebih nikmat dan ampas cepat turun. Nyaman dinikmati kapan saja.',
    'options' => 
    array (
      0 => 
      array (
        'label' => 'Kopi Bubuk Halus',
        'value' => 'Kopi Bubuk Halus',
      ),
      1 => 
      array (
        'label' => '🔙 Kategori Kopi',
        'value' => '1.1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  23 => 
  array (
    'name' => 'Brand UCAFE',
    'keywords' => 
    array (
      0 => 'ucafe',
      1 => 'u cafe',
    ),
    'response' => '☕ *UCAFE Coffee Series*
Pilihan kopi modern praktis dengan rasa tebal, aftertaste bersih, dan varian instan tanpa ampas.',
    'options' => 
    array (
      0 => 
      array (
        'label' => 'Pure Coffee Series',
        'value' => 'Pure Coffee Series',
      ),
      1 => 
      array (
        'label' => 'Instant Coffee Series',
        'value' => 'Instant Coffee Series',
      ),
      2 => 
      array (
        'label' => '🔙 Kategori Kopi',
        'value' => '1.1',
      ),
      3 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  24 => 
  array (
    'name' => 'Brand Jaheku',
    'keywords' => 
    array (
      0 => 'jaheku',
      1 => 'jahe',
      2 => 'jaheku premium ginger',
    ),
    'response' => '🍵 *Jaheku Premium Ginger*
Minuman jahe alami yang dipadukan dengan bahan pendukung pilihan untuk menghangatkan dan menyegarkan tubuh dalam aktivitas sehari-hari.',
    'options' => 
    array (
      0 => 
      array (
        'label' => 'Jaheku Mix Series',
        'value' => 'Jaheku Mix',
      ),
      1 => 
      array (
        'label' => '🔙 Produk Non Kopi',
        'value' => '1.2',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  25 => 
  array (
    'name' => 'Brand Brochoco',
    'keywords' => 
    array (
      0 => 'brochoco',
      1 => 'cokelat',
      2 => 'brochoco chocolate drink',
    ),
    'response' => '🍫 *BROCHOCO Chocolate Drink*
Minuman cokelat asli yang meleleh di mulut saat diseduh. Nikmat disajikan panas maupun dingin, atau sebagai kreasi bahan kue di rumah.',
    'options' => 
    array (
      0 => 
      array (
        'label' => 'BROCHOCO Original',
        'value' => 'BROCHOCO Original',
      ),
      1 => 
      array (
        'label' => 'BROCHOCO Mix',
        'value' => 'BROCHOCO Mix',
      ),
      2 => 
      array (
        'label' => '🔙 Produk Non Kopi',
        'value' => '1.2',
      ),
      3 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  26 => 
  array (
    'name' => 'Supresso Coffee - Single Origin',
    'keywords' => 
    array (
      0 => 'single origin',
      1 => 'supresso coffee single origin',
    ),
    'response' => 'Biji kopi yang berasal dari seluruh penjuru daerah di Indonesia. Mulai Aceh, Toraja, hingga Flores Bajawa. Koleksi kopi single-origin dari Supresso menghasilkan citarasa premium dengan ciri khas yang anggun.Untuk mengetahui varian lengkapnya, kunjungi www.supresso.com atau ketik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  27 => 
  array (
    'name' => 'Supresso Coffee - Gourmet Collections',
    'keywords' => 
    array (
      0 => 'gourmet collections',
      1 => 'supresso coffee gourmet collections',
    ),
    'response' => 'Diolah dari berbagai varian biji kopi di seluruh daerah Indonesia membentuk karakter yang unik nan seimbang dengan citarasa yang kompleks. Pilihan terbaik untuk semua variasi kopi.Untuk mengetahui varian lengkapnya, kunjungi www.supresso.com atau ketik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  28 => 
  array (
    'name' => 'Supresso Coffee - The Collections',
    'keywords' => 
    array (
      0 => 'the collections',
      1 => 'supresso coffee the collections',
    ),
    'response' => 'Salah satu series yang dikhususkan untuk anda penikmat kopi yang sesungguhnya. Diolah dari bahan-bahan pilihan yang memiliki keunikan dari segi pembuatan, pengolahan, hingga proses serta hasil jadi dari biji kopi Peaberry hingga Luwak Coffee.Untuk mengetahui varian lengkapnya, kunjungi www.supresso.com atau ketik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  29 => 
  array (
    'name' => 'Supresso Coffee - BaliCafe',
    'keywords' => 
    array (
      0 => 'balicafe',
      1 => 'supresso coffee balicafe',
    ),
    'response' => 'Series khusus biji kopi asli dari pulau Dewata. Varian kopi nikmat dan unik serta sekaligus menjadi sudut rekreasi anda secara langsung dari rumah.Untuk mengetahui varian lengkapnya, kunjungi www.supresso.com atau ketik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  30 => 
  array (
    'name' => 'Supresso Coffee - World Blend',
    'keywords' => 
    array (
      0 => 'world blend',
      1 => 'supresso coffee world blend',
    ),
    'response' => 'Berasal dari mancanegara, berbagai biji kopi yang patut anda coba. Sensasi hasil bumi dari setiap dataran di belahan dunia menuju cangkir dalam genggaman anda.Untuk mengetahui varian lengkapnya, kunjungi www.supresso.com atau ketik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  31 => 
  array (
    'name' => 'Tugu Buaya - Kopi Special',
    'keywords' => 
    array (
      0 => 'kopi special',
      1 => 'tugu buaya kopi special',
    ),
    'response' => 'Terkenal sejak 1977 karena rasa pahit yang seimbang serta aroma-nya yang khas, membuatnya Nikmat Setiap Saat. Kopi hitam bubuk ini memiliki berbagai varian ukuran berat atau gramasi yang bisa disesuaikan dengan kebutuhan anda setiap hari.Untuk mengetahui varian lengkapnya, kunjungi www.indracostore.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  32 => 
  array (
    'name' => 'Tugu Buaya - Coffee Mix',
    'keywords' => 
    array (
      0 => 'coffee mix',
      1 => 'tugu buaya coffee mix',
    ),
    'response' => 'Campuran kopi dengan bahan lain yang mengakomodasi kebutuhan setiap penggemar Kopi Tugu Buaya untuk merasakan nikmatnya sensasi kopi dengan aroma dan variasi berbeda.Untuk mengetahui varian lengkapnya, kunjungi www.indracostore.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  33 => 
  array (
    'name' => 'Uang Emas - Kopi Special',
    'keywords' => 
    array (
      0 => 'kopi special',
      1 => 'uang emas kopi special',
    ),
    'response' => 'Kopi hitam bubuk dengan rasa yang pahit dengan body lebih mantap. Ampas kopi cepat turun saat di seduh membuat Kopi Uang Emas menjadi produk yang cocok untuk stok di rumah maupun untuk bisnis warung kopi anda!Untuk mengetahui varian lengkapnya, kunjungi www.indracostore.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  34 => 
  array (
    'name' => 'Rasa Sayang - Kopi Gold Special',
    'keywords' => 
    array (
      0 => 'kopi gold special',
      1 => 'rasa sayang kopi gold special',
    ),
    'response' => 'Diracik dari biji kopi pilihan dengan rasa yang mantap dan harum tercipta sajian kopi lintas generasi dalam Citarasa Tempo Doeloe Kopi Rasa SayangUntuk mengetahui varian lengkapnya, kunjungi www.indracostore.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  35 => 
  array (
    'name' => 'Rasa Sayang - Kopi Super Quality',
    'keywords' => 
    array (
      0 => 'kopi super quality',
      1 => 'rasa sayang kopi super quality',
    ),
    'response' => 'Biji kopi berkualitas tinggi yang diproses dari biji kopi pilihan, menciptakan rasa yang halus, mantap nan berciri khas di setiap isapan cangkir Kopi Rasa Sayang andaUntuk mengetahui varian lengkapnya, kunjungi www.indracostore.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  36 => 
  array (
    'name' => 'Rasa Sayang - Kopi SP Bintang',
    'keywords' => 
    array (
      0 => 'kopi sp bintang',
      1 => 'rasa sayang kopi sp bintang',
    ),
    'response' => 'Edisi spesial dari kopi hitam bubuk Rasa Sayang, dengan rasa pahit mantap yang berbeda dari yang lain untuk anda pencinta serta penjelajah rasa kopi khas Nusantara.Untuk mengetahui varian lengkapnya, kunjungi www.indracostore.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  37 => 
  array (
    'name' => 'Rasa Sayang - Kopi Bali',
    'keywords' => 
    array (
      0 => 'kopi bali',
      1 => 'rasa sayang kopi bali',
    ),
    'response' => 'Berasal dari biji kopi asli Bali, diproses dan dikemas ke dalam citarasa khas pulau Dewata untuk disajikan dalam hangatnya suasana dengan sentuhan kontemporer.Untuk mengetahui varian lengkapnya, kunjungi www.indracostore.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  38 => 
  array (
    'name' => 'Rasa Sayang - Coffee Mix',
    'keywords' => 
    array (
      0 => 'coffee mix',
      1 => 'rasa sayang coffee mix',
    ),
    'response' => 'Berbahan dasar kopi dan bahan lain pilihan tercipta series Coffee Mix khas Kopi Rasa Sayang dengan tetap mempertahankan sisi klasik ala Citarasa Tempo Doeloe.Untuk mengetahui varian lengkapnya, kunjungi www.indracostore.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  39 => 
  array (
    'name' => 'CERIA - Kopi Bubuk Halus',
    'keywords' => 
    array (
      0 => 'kopi bubuk halus',
      1 => 'ceria kopi bubuk halus',
    ),
    'response' => 'Kopi Ceria merupakan kopi hitam bubuk dengan gilingan halus yang menghasilkan seduhan lebih nikmat. Ampasnya cepat turun setelah diseduh, sehingga kopi lebih nyaman dinikmati kapan saja. Cocok untuk stok di rumah maupun untuk kebutuhan bisnis warung kopi Anda!Untuk mengetahui varian lengkapnya, kunjungi www.indracostore.com atau klik YA untuk berkomunikasi langsung dengan tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  40 => 
  array (
    'name' => 'UCAFE - Pure Coffee Series',
    'keywords' => 
    array (
      0 => 'pure coffee series',
      1 => 'ucafe pure coffee series',
    ),
    'response' => 'Diracik khusus untuk memberi rasa halus, aftertaste yang bersih, dengan aroma khas dan rasa kopi yang kuat. Series dari UCAFE yang cocok bagi anda penggemar tebalnya rasa kopi untuk konsumsi sehari-hari.Untuk mengetahui varian lengkapnya, kunjungi www.indracostore.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  41 => 
  array (
    'name' => 'UCAFE - Instant Coffee Series',
    'keywords' => 
    array (
      0 => 'instant coffee series',
      1 => 'ucafe instant coffee series',
    ),
    'response' => 'Perpaduan antara kopi, krimer, dengan bahan lain yang dikreasikan dalam beberapa varian kopi instan berkarakter serta tanpa ampas yang siap untuk dinikmati kapanpun dan dimanapun.Untuk mengetahui varian lengkapnya, kunjungi www.indracostore.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  42 => 
  array (
    'name' => 'Jaheku Premium Ginger - Mix',
    'keywords' => 
    array (
      0 => 'mix',
      1 => 'jaheku premium ginger mix',
    ),
    'response' => 'Minuman jahe yang dipadukan dengan bahan pendukung lainnya untuk menjaga tubuh tetap hangat dalam aktivitas setiap harinya.Untuk mengetahui varian lengkapnya, kunjungi www.indracostore.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  43 => 
  array (
    'name' => 'BROCHOCO Chocolate Drink - Original',
    'keywords' => 
    array (
      0 => 'original',
      1 => 'brochoco chocolate drink original',
    ),
    'response' => 'Rasa sesungguhnya dari minuman cokelat yang bisa meleleh dimulut saat diseduh. Cocok untuk dinikmati dalam keadaan panas maupun dinginatau bisa anda gunakan sebagai bahan pembuatan kue ala-ala di rumah. Untuk mengetahui varian lengkapnya, kunjungi www.indracostore.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  44 => 
  array (
    'name' => 'BROCHOCO Chocolate Drink - Mix',
    'keywords' => 
    array (
      0 => 'mix',
      1 => 'brochoco chocolate drink mix',
    ),
    'response' => 'Minuman cokelat yang di-mix dengan berbagai macam bahan yang unik untuk melengkapi kepuasan anda menikmati variasi minuman cokelat.Untuk mengetahui varian lengkapnya, kunjungi www.indracostore.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  45 => 
  array (
    'name' => 'Intirasa - Bumbu Tabur',
    'keywords' => 
    array (
      0 => 'bumbu tabur',
      1 => 'intirasa bumbu tabur',
    ),
    'response' => 'Untuk mengetahui varian lengkapnya, kunjungi www.intirasa.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  46 => 
  array (
    'name' => 'Intirasa - Bumbu Tepung',
    'keywords' => 
    array (
      0 => 'bumbu tepung',
      1 => 'intirasa bumbu tepung',
    ),
    'response' => 'Untuk mengetahui varian lengkapnya, kunjungi www.intirasa.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  47 => 
  array (
    'name' => 'Intirasa - Bumbu Pelengkap',
    'keywords' => 
    array (
      0 => 'bumbu pelengkap',
      1 => 'intirasa bumbu pelengkap',
    ),
    'response' => 'Untuk mengetahui varian lengkapnya, kunjungi www.intirasa.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  48 => 
  array (
    'name' => 'Intirasa - Krimer',
    'keywords' => 
    array (
      0 => 'krimer',
      1 => 'intirasa krimer',
    ),
    'response' => 'Untuk mengetahui varian lengkapnya, kunjungi www.intirasa.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  49 => 
  array (
    'name' => 'Intirasa - Bumbu Dasar',
    'keywords' => 
    array (
      0 => 'bumbu dasar',
      1 => 'intirasa bumbu dasar',
    ),
    'response' => 'Bahan penting dalam segala masakan maupun minuman yang akan dibuat. Mulai dari santan instan hingga bahan dasar lain yang memudahkan aktivitas dapur dan usaha anda.Untuk mengetahui varian lengkapnya, kunjungi www.intirasa.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  50 => 
  array (
    'name' => 'Intirasa - Bumbu Instan',
    'keywords' => 
    array (
      0 => 'bumbu instan',
      1 => 'intirasa bumbu instan',
    ),
    'response' => 'Untuk mengetahui varian lengkapnya, kunjungi www.intirasa.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  51 => 
  array (
    'name' => 'Intirasa - Bumbu Kaldu',
    'keywords' => 
    array (
      0 => 'bumbu kaldu',
      1 => 'intirasa bumbu kaldu',
    ),
    'response' => 'Untuk mengetahui varian lengkapnya, kunjungi www.intirasa.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  52 => 
  array (
    'name' => 'Intirasa - Bumbu Rendam',
    'keywords' => 
    array (
      0 => 'bumbu rendam',
      1 => 'intirasa bumbu rendam',
    ),
    'response' => 'Untuk mengetahui varian lengkapnya, kunjungi www.intirasa.com atau klik YA untuk berkomunikasi langsung kepada tim kami 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  53 => 
  array (
    'name' => 'Cara Menjadi Reseller/Distributor - Syarat Menjadi Reseller/ Pembelian dalam jumlah banyak',
    'keywords' => 
    array (
      0 => 'syarat menjadi reseller/ pembelian dalam jumlah banyak',
      1 => 'cara menjadi reseller/distributor syarat menjadi reseller/ pembelian dalam jumlah banyak',
    ),
    'response' => 'Terima kasih atas kepercayaan anda dengan INDRACO Group. Untuk menjadi Reseller/ pembelian dalam jumlah banyak anda cukup mempersiapkan brand dan produk apa yang anda inginkan kemudian mohon bantuannya untuk melengkapi informasi berikutNama:Nomor WhatsApp Aktif:Domisili:Nantinya, tim INDRACO dari departemen terkait akan menghubungi, mengarahkan, serta menjawab semua pertanyaan seputar menjadi Reseller/ pembelian dalam jumlah banyak secara langsung.Jika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  54 => 
  array (
    'name' => 'Cara Menjadi Reseller/Distributor - Syarat Menjadi Distributor',
    'keywords' => 
    array (
      0 => 'syarat menjadi distributor',
      1 => 'cara menjadi reseller/distributor syarat menjadi distributor',
    ),
    'response' => 'Terima kasih atas kepercayaan anda dengan INDRACO Group. Untuk menjadi Reseller/ pembelian dalam jumlah banyak anda cukup mempersiapkan brand dan produk apa yang anda inginkan kemudian mohon bantuannya untuk melengkapi informasi berikutNama:Nomor WhatsApp Aktif:Domisili:Nantinya, tim INDRACO dari departemen terkait akan menghubungi, mengarahkan, serta memberikan informasi syarat dan ketentuan menjadi distributor brand dan produk INDRACO secara langsung.Jika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  55 => 
  array (
    'name' => 'Penukaran Karton Tugu Buaya 12g - Cara Mendapatkan Hadiah',
    'keywords' => 
    array (
      0 => 'cara mendapatkan hadiah',
      1 => 'penukaran karton tugu buaya 12g cara mendapatkan hadiah',
    ),
    'response' => 'Terima kasih karena telah menjadi konsumen setia Kopi Tugu Buaya! Berikut cara mendapatkan hadiah Kopi Tugu Buaya 12g1. Kumpulkan sejumlah potongan karton Kopi Tugu Buaya 12g (Desain lama/ Desain Baru)2. Hitung berapa jumlah karton yang anda miliki untuk ditukarkan dengan hadiah sesuai daftar hadiah yang tertera pada karton3. Tukarkan ke lokasi terdekat dengan anda saat ini.Jika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  56 => 
  array (
    'name' => 'Penukaran Karton Tugu Buaya 12g - Tempat Penukaran Hadiah',
    'keywords' => 
    array (
      0 => 'tempat penukaran hadiah',
      1 => 'penukaran karton tugu buaya 12g tempat penukaran hadiah',
    ),
    'response' => 'Terima kasih karena telah menjadi konsumen setia Kopi Tugu Buaya! Untuk memudahkan anda dalam menemukan tempat penukaran hadiah terdekat dengan lokasi saat ini, mohon bantuannya untuk memberikan informasi DOMISILI saat ini dengan menulis Nama Kota/Kabupaten_TB *contoh Surabaya_TB.Jika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  57 => 
  array (
    'name' => 'Penukaran Karton Tugu Buaya 12g - Daftar Hadiah Tugu Buaya 12g',
    'keywords' => 
    array (
      0 => 'daftar hadiah tugu buaya 12g',
      1 => 'penukaran karton tugu buaya 12g daftar hadiah tugu buaya 12g',
    ),
    'response' => 'Terima kasih karena telah menjadi konsumen setia Kopi Tugu Buaya! Berikut jumlah penukaran dan daftar hadiah Kopi Tugu Buaya 12g Papan10 Lembar: 1 Gelas Tangkai20 Lembar: 1 Mug30 Lembar: 1 Piring Keramik75 Lembar: 1 Botol Minum150 Lembar: 1 Sealware Set300 Lembar: 1 Rantang Susun 4500 Lembar: 1 Emergency Lamp600 Lembar: 1 Cookware Set800 Lembar: 1 Kipas Angin1.000 Lembar: 1 Magic Com2.000 Lembar: 1 Handphone Android3.000 Lembar: 1 Tablet Android4.000 Lembar: 1 TV LED 32 Inch6.000 Lembar: 1 Kulkas 2 Pintu10.000 Lembar: 1 Laptop 14 Inch17.000 Lembar: 1 Sepeda MotorJika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  58 => 
  array (
    'name' => 'Penggunaan Voucher Toko Online - Tata Cara Penukaran Voucher',
    'keywords' => 
    array (
      0 => 'tata cara penukaran voucher',
      1 => 'penggunaan voucher toko online tata cara penukaran voucher',
    ),
    'response' => 'Terima kasih karena telah menjadi konsumen setia produk dari semua brand di INDRACO Store! Berikut tata cara penukaran voucher toko online INDRACO Store:1. Siapkan kode unik voucher2. Ketik kode unik pada bagian "masukkan voucher" sebelum checkout atau sebelum pembayaran pesanan3. Selamat voucher anda berhasil digunakan*catatan: jika voucher tidak bisa digunakan silakan cek tanggal kadaluarsa voucher atau pastikan kode unik sudah dimasukkan dengan sesuai.Jika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  59 => 
  array (
    'name' => 'Penggunaan Voucher Toko Online - Syarat dan Ketentuan Penggunaan Voucher',
    'keywords' => 
    array (
      0 => 'syarat dan ketentuan penggunaan voucher',
      1 => 'penggunaan voucher toko online syarat dan ketentuan penggunaan voucher',
    ),
    'response' => 'Terima kasih karena telah menjadi konsumen setia produk dari semua brand di INDRACO Store! Berikut syarat dan ketentuan penggunaan voucher di INDRACO Store1. Pastikan anda sudah mendaftar dan melakukan verifikasi akun2. Pastikan kode voucher belum pernah digunakan sebelumnya3. Pastikan anda telah memenuhi ketentuan penggunaan voucher4. Pastikan voucher masih berlaku sesuai tanggal periode penukaranJika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  60 => 
  array (
    'name' => 'Manufaktur - Research, Development & Innovation',
    'keywords' => 
    array (
      0 => 'research, development & innovation',
      1 => 'manufaktur research, development & innovation',
    ),
    'response' => 'Terima kasih atas kepercayaan anda dengan INDRACO Group. Perihal lowongan pekerjaan, saat ini bisa anda akses pada laman karir di tautan www.indraco.comJika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  61 => 
  array (
    'name' => 'Event/Acara - Kampus',
    'keywords' => 
    array (
      0 => 'kampus',
      1 => 'event/acara kampus',
    ),
    'response' => 'Terima kasih atas kepercayaan anda dengan INDRACO Group. Perihal penawaran kerjasama event/ acara, anda dapat mengirimkan informasi keterangan, detail dan proposal acara pada email info@indraco.com dan salting@indraco.com dengan subject "Kota/Kab_event_kategori acara"Jika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  62 => 
  array (
    'name' => 'Manufaktur - Mesin',
    'keywords' => 
    array (
      0 => 'mesin',
      1 => 'manufaktur mesin',
    ),
    'response' => 'Terima kasih atas kepercayaan anda dengan INDRACO Group. Perihal penawaran kerjasama manufaktur, anda dapat mengirimkan informasi keterangan, detail credential, atau proposal acara pada email info@indraco.com dengan subject "Manufaktur_nama perusahaan anda_kategori item (mesin/ alat berat/ dll)"Jika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  63 => 
  array (
    'name' => 'Supplier - Kopi',
    'keywords' => 
    array (
      0 => 'kopi',
      1 => 'supplier kopi',
    ),
    'response' => 'Terima kasih atas kepercayaan anda dengan INDRACO Group. Perihal penawaran kerjasama sebagai supplier, anda dapat mengirimkan informasi keterangan, detail credential, atau proposal pada email info@indraco.com dengan subject "Supplier_nama perusahaan anda_kategori item (kopi/ jahe/ dll)"Jika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  64 => 
  array (
    'name' => 'Creative - Endorsement',
    'keywords' => 
    array (
      0 => 'endorsement',
      1 => 'creative endorsement',
    ),
    'response' => 'Terima kasih atas kepercayaan anda dengan INDRACO Group. Perihal penawaran kerjasama di bidang creative, anda dapat mengirimkan informasi keterangan, detail credential, atau proposal penawaran pada email dm@indraco.com dan info@indraco.com dengan subject "Creative_nama perusahaan anda_kategori item (agency socmed/ PH/ dll)"Jika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  65 => 
  array (
    'name' => 'Kendala Checkout - Tidak bisa checkout produk yang dipilih',
    'keywords' => 
    array (
      0 => 'tidak bisa checkout produk yang dipilih',
      1 => 'kendala checkout tidak bisa checkout produk yang dipilih',
    ),
    'response' => 'Terima kasih atas kepercayaannya berbelanja di INDRACO Store! Mohon bantuannya untuk mengikuti cara berikut ini:1. Pada halaman produk, pilih Beli2. Pada halaman detail produk pilih opsi variasi yang diinginkan (jika ada)3. Pilih Beli Sekarang4. Buka Keranjang maka tampil produk yang akan dibeli5. Gunakan Voucher INDRACO Store (jika ada)6. Pilih Proses Order dengan mengisikan data penerima dan alamat tujuan7. Pilih Opsi Pengiriman yang diinginkan8. Pilih Metode Pembayaran yang diinginkan9. Proses Untuk Pembayaran.Jika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  66 => 
  array (
    'name' => 'Kendala Checkout - Tidak bisa mengakses tombol checkout',
    'keywords' => 
    array (
      0 => 'tidak bisa mengakses tombol checkout',
      1 => 'kendala checkout tidak bisa mengakses tombol checkout',
    ),
    'response' => 'Terima kasih atas kepercayaannya berbelanja di INDRACO Store! Mohon bantuannya untuk memeriksa poin berikut ini:1. Data penerima dan alamat tujuan sudah diisi2. Opsi Pengiriman yang diinginkan sudah dipilih3. Metode Pembayaran yang diinginkan sudah dipilih4. Sudah menekan tombol Proses Untuk Pembayaran.Jika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  67 => 
  array (
    'name' => 'Kendala Tambah Produk Ke Keranjang - Tidak bisa menambah quota produk',
    'keywords' => 
    array (
      0 => 'tidak bisa menambah quota produk',
      1 => 'kendala tambah produk ke keranjang tidak bisa menambah quota produk',
    ),
    'response' => 'Terima kasih atas kepercayaannya berbelanja di INDRACO Store! Mohon bantuannya untuk mengikuti cara berikut ini:1. Pada halaman produk, pilih Beli2. Pada halaman detail produk pilih Beli Sekarang atau pilih opsi variasi pilihan (jika ada)3. Buka KeranjangJika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  68 => 
  array (
    'name' => 'Kendala Tambah Produk Ke Keranjang - Produk masih ada namun tidak bisa masuk keranjang',
    'keywords' => 
    array (
      0 => 'produk masih ada namun tidak bisa masuk keranjang',
      1 => 'kendala tambah produk ke keranjang produk masih ada namun tidak bisa masuk keranjang',
    ),
    'response' => 'Terima kasih atas kepercayaannya berbelanja di INDRACO Store! Mohon bantuannya untuk memeriksa poin berikut ini:1. Pastikan anda sudah memilih produk2. Pastikan anda memilih variasi pilihan (Jika ada)3. Pastikan produk yang anda beli tidak berstatus (habis/ out of stock)Jika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  69 => 
  array (
    'name' => 'Kendala Pilihan Pengiriman - Tidak bisa memilih ekspedisi',
    'keywords' => 
    array (
      0 => 'tidak bisa memilih ekspedisi',
      1 => 'kendala pilihan pengiriman tidak bisa memilih ekspedisi',
    ),
    'response' => 'Terima kasih atas kepercayaannya berbelanja di INDRACO Store! Mohon bantuannya untuk mengikuti cara berikut ini:1. Pada halaman checkout, Isikan data penerima dan detail pengiriman2. Pilih Opsi Pengiriman yang diinginkan.Jika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  70 => 
  array (
    'name' => 'Kendala Pilihan Pengiriman - Tidak bisa memasukkan alamat lengkap',
    'keywords' => 
    array (
      0 => 'tidak bisa memasukkan alamat lengkap',
      1 => 'kendala pilihan pengiriman tidak bisa memasukkan alamat lengkap',
    ),
    'response' => 'Terima kasih atas kepercayaannya berbelanja di INDRACO Store! Mohon bantuannya untuk memeriksa poin berikut ini:1. Pastikan anda sudah menulis data alamat pada kolom yang disediakan dengan benar2. Pastikan anda sudah memilih opsi pengirimanJika masih ada pertanyaan lebih lanjut ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  71 => 
  array (
    'name' => 'Kendala Penggunaan Voucher - Voucher Tertulis Salah/ Tidak Berlaku',
    'keywords' => 
    array (
      0 => 'voucher tertulis salah/ tidak berlaku',
      1 => 'kendala penggunaan voucher voucher tertulis salah/ tidak berlaku',
    ),
    'response' => 'Terima kasih atas kepercayaannya berbelanja di INDRACO Store! Mohon bantuannya untuk memeriksa poin berikut ini:1. Pastikan voucher yang anda masukkan pada kolom isian voucher sudah sesuai dengan yang tertera2. Pastikan voucher anda masih dalam masa periode penggunaanatau jika voucher anda adalah voucher reguler, mohon melakukan langkah berikut:1. Pada halaman keranjang pilih Makin hemat pakai promo atau Ambil voucher2. Pilih Voucher yang tersediaJika terdapat kendala lain ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  72 => 
  array (
    'name' => 'Kendala Penggunaan Voucher - Batas penggunaan voucher telah habis namun masih dalam periode penukaran',
    'keywords' => 
    array (
      0 => 'batas penggunaan voucher telah habis namun masih dalam periode penukaran',
      1 => 'kendala penggunaan voucher batas penggunaan voucher telah habis namun masih dalam periode penukaran',
    ),
    'response' => 'Terima kasih atas kepercayaannya berbelanja di INDRACO Store! Mohon bantuannya untuk memeriksa poin berikut ini:1. Pastikan voucher yang anda masukkan pada kolom isian voucher tidak pernah digunakan sebelumnya2. Pastikan voucher yang anda gunakan merupakan voucher resmi dari INDRACO StoreJika terdapat kendala lain ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  73 => 
  array (
    'name' => 'Kendala Produk - Produk yang dicari tidak ada',
    'keywords' => 
    array (
      0 => 'produk yang dicari tidak ada',
      1 => 'kendala produk produk yang dicari tidak ada',
    ),
    'response' => 'Terima kasih atas kepercayaannya berbelanja di INDRACO Store! Mohon bantuannya untuk memeriksa pada halaman utama > pilih filter Kategori, Merek dan Kemasan.Jika terdapat kendala lain ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  74 => 
  array (
    'name' => 'Kendala Produk - Produk yang dibeli berbeda dengan yang di terima',
    'keywords' => 
    array (
      0 => 'produk yang dibeli berbeda dengan yang di terima',
      1 => 'kendala produk produk yang dibeli berbeda dengan yang di terima',
    ),
    'response' => 'Terima kasih atas kepercayaannya berbelanja di INDRACO Store! Mohon bantuannya untuk memeriksa poin berikut ini:1. Pastikan produk yang anda pilih pada riwayat pesanan merupakan brand, produk, varian, dan gramasi yang sama dengan yang anda terima2. Pastikan memeriksa pesan terbaru dari tim admin INDRACO Store atau catatan ketentuan pembelian salah satu produk di INDRACO StoreJika terdapat kendala lain ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  75 => 
  array (
    'name' => 'Kendala Pembayaran - Tidak bisa melanjutkan ke pembayaran setelah memilih pengiriman',
    'keywords' => 
    array (
      0 => 'tidak bisa melanjutkan ke pembayaran setelah memilih pengiriman',
      1 => 'kendala pembayaran tidak bisa melanjutkan ke pembayaran setelah memilih pengiriman',
    ),
    'response' => 'Terima kasih atas kepercayaannya berbelanja di INDRACO Store! Mohon bantuannya untuk memeriksa poin berikut ini:1. Pastikan koneksi internet anda dalam keadaan stabil2. Pastikan memeriksa produk yang diorder, isian alamat pengiriman, serta ekspedisi sudah terpilih dan terisi dengan sesuaiJika terdapat kendala lain ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  76 => 
  array (
    'name' => 'Kendala Pembayaran - Tidak menerima kode bayar',
    'keywords' => 
    array (
      0 => 'tidak menerima kode bayar',
      1 => 'kendala pembayaran tidak menerima kode bayar',
    ),
    'response' => 'Terima kasih atas kepercayaannya berbelanja di INDRACO Store! Mohon bantuannya untuk memeriksa poin berikut ini:1. Pastikan anda memilih opsi pembayaran yang disediakan (manual bank transfer atau virtual account/ credit card/ dompet digital2. Pastikan memeriksa produk yang diorder, isian alamat pengiriman, serta ekspedisi sudah terpilih dan terisi dengan sesuaiJika terdapat kendala lain ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  77 => 
  array (
    'name' => 'Kendala Pembayaran - Tidak menemukan rekening bayar',
    'keywords' => 
    array (
      0 => 'tidak menemukan rekening bayar',
      1 => 'kendala pembayaran tidak menemukan rekening bayar',
    ),
    'response' => 'Terima kasih atas kepercayaannya berbelanja di INDRACO Store! Mohon bantuannya untuk memeriksa poin berikut ini:1. Sudah melakukan langkah akhir, proses pembayaran2. Pada halaman pembayaran terakhir, anda akan melihat nomor rekening, jumlah order, dan alamat kirim3. Jika pada bagian ini anda belum menemukannya, mohon periksa pada inbox email andaJika terdapat kendala lain ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  78 => 
  array (
    'name' => 'Kendala Akun - Tidak bisa login dengan akun yang sudah terdaftar',
    'keywords' => 
    array (
      0 => 'tidak bisa login dengan akun yang sudah terdaftar',
      1 => 'kendala akun tidak bisa login dengan akun yang sudah terdaftar',
    ),
    'response' => 'Terima kasih atas kepercayaannya berbelanja di INDRACO Store! Mohon bantuannya untuk memeriksa poin berikut ini:1. Pastikan anda sudah melakukan verifikasi email pada inbox email yang digunakan untuk mendaftar2. Pastikan email dan password yang anda gunakan sudah sesuaiJika terdapat kendala lain ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  79 => 
  array (
    'name' => 'Kendala Akun - Kendala lupa password',
    'keywords' => 
    array (
      0 => 'kendala lupa password',
      1 => 'kendala akun kendala lupa password',
    ),
    'response' => 'Terima kasih atas kepercayaannya berbelanja di INDRACO Store! Mohon bantuannya untuk melakukan langkah berikut:1. Klik/ tap ikon login pada bagian pojok kanan atas halaman2. Klik/ Tap Lupa password lalu ikuti langkah yang sudah disediakanJika terdapat kendala lain ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  80 => 
  array (
    'name' => 'Kendala Akun - Aktivitas mencurigakan',
    'keywords' => 
    array (
      0 => 'aktivitas mencurigakan',
      1 => 'kendala akun aktivitas mencurigakan',
    ),
    'response' => 'Terima kasih atas kepercayaannya berbelanja di INDRACO Store! Mohon bantuannya untuk memeriksa poin berikut ini:1. Pastikan anda tidak pernah membocorkan alamat email dan password kepada orang asing2. Pastikan device yang anda gunakan untuk login tidak sedang digunakan oleh orang lainJika terdapat kendala lain ketik YA untuk berkomunikasi langsung dengan tim admin. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  81 => 
  array (
    'name' => 'Kritik dan Saran - Pengemasan atau packing orderan',
    'keywords' => 
    array (
      0 => 'pengemasan atau packing orderan',
      1 => 'kritik dan saran pengemasan atau packing orderan',
    ),
    'response' => 'Terima kasih atas kepercayaan anda berbelanja di INDRACO Store. Demi meningkatkan performa pelayanan agar semakin baik kedepannya, kami memohon bantuan anda untuk memberikan kritik dan saran kepada kami.Balas pesan ini dengan format berikut:PENGEMASAN - Sampaikan kritik dan saran anda.',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  82 => 
  array (
    'name' => 'Kritik dan Saran - Produk dan hadiah di dalam kemasan atau packing',
    'keywords' => 
    array (
      0 => 'produk dan hadiah di dalam kemasan atau packing',
      1 => 'kritik dan saran produk dan hadiah di dalam kemasan atau packing',
    ),
    'response' => 'Terima kasih atas kepercayaan anda berbelanja di INDRACO Store. Demi meningkatkan performa pelayanan agar semakin baik kedepannya, kami memohon bantuan anda untuk memberikan kritik dan saran kepada kami.Balas pesan ini dengan format berikut:PRODUK DAN HADIAH - Sampaikan kritik dan saran anda.',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  83 => 
  array (
    'name' => 'Kritik dan Saran - Gramatur, isi, dan berat produk',
    'keywords' => 
    array (
      0 => 'gramatur, isi, dan berat produk',
      1 => 'kritik dan saran gramatur, isi, dan berat produk',
    ),
    'response' => 'Terima kasih atas kepercayaan anda berbelanja di INDRACO Store. Demi meningkatkan performa pelayanan agar semakin baik kedepannya, kami memohon bantuan anda untuk memberikan kritik dan saran kepada kami.Balas pesan ini dengan format berikut:GRAMATUR - Sampaikan kritik dan saran anda.',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  84 => 
  array (
    'name' => 'Kritik dan Saran - Rasa dan aroma',
    'keywords' => 
    array (
      0 => 'rasa dan aroma',
      1 => 'kritik dan saran rasa dan aroma',
    ),
    'response' => 'Terima kasih atas kepercayaan anda berbelanja di INDRACO Store. Demi meningkatkan performa pelayanan agar semakin baik kedepannya, kami memohon bantuan anda untuk memberikan kritik dan saran kepada kami.Balas pesan ini dengan format berikut:RASA DAN AROMA - Sampaikan kritik dan saran anda.',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  85 => 
  array (
    'name' => 'Kritik dan Saran - Akses pembelian web atau area sekitar',
    'keywords' => 
    array (
      0 => 'akses pembelian web atau area sekitar',
      1 => 'kritik dan saran akses pembelian web atau area sekitar',
    ),
    'response' => 'Terima kasih atas kepercayaan anda berbelanja di INDRACO Store. Demi meningkatkan performa pelayanan agar semakin baik kedepannya, kami memohon bantuan anda untuk memberikan kritik dan saran kepada kami.Balas pesan ini dengan format berikut:WEB - Sampaikan kritik dan saran anda.',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  86 => 
  array (
    'name' => 'Temuan produk tidak laik jual - Kemasan produk rusak',
    'keywords' => 
    array (
      0 => 'kemasan produk rusak',
      1 => 'temuan produk tidak laik jual kemasan produk rusak',
    ),
    'response' => 'Terima kasih atas kepercayaan anda berbelanja di INDRACO Store. Mohon maaf untuk kendala yang terjadi 🙏.Kami senantiasa selalu menjaga kualitas produk untuk kepuasan konsumen selaku prioritas di toko online kami. Agar bisa segera menangani dan menindak-lanjuti keluhan anda, kami memohon bantuannya untuk mengirimkan video unboxing dan mengetik informasi berikut:Nama anda:Produk yang anda beli:Tanggal transaksi:Tanggal produk diterima:Toko online yang anda gunakan:Tim kami akan merespon kendala anda terkait kemasan produk rusak secara langsung, segera. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  87 => 
  array (
    'name' => 'Temuan produk tidak laik jual - Produk dalam keadaan kurang sesuai dan tidak semestinya',
    'keywords' => 
    array (
      0 => 'produk dalam keadaan kurang sesuai dan tidak semestinya',
      1 => 'temuan produk tidak laik jual produk dalam keadaan kurang sesuai dan tidak semestinya',
    ),
    'response' => 'Terima kasih atas kepercayaan anda berbelanja di INDRACO Store. Mohon maaf untuk kendala yang terjadi 🙏.Kami senantiasa selalu menjaga kualitas produk untuk kepuasan konsumen selaku prioritas di toko online kami. Agar bisa segera menangani dan menindak-lanjuti keluhan anda, kami memohon bantuannya untuk mengirimkan video unboxing dan mengetik informasi berikut:Nama anda:Produk yang anda beli:Tanggal transaksi:Tanggal produk diterima:Toko online yang anda gunakan:Tim kami akan merespon kendala anda terkait produk yang kurang sesuai secara langsung, segera. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  88 => 
  array (
    'name' => 'Temuan produk tidak laik jual - Produk tercampur dengan benda asing',
    'keywords' => 
    array (
      0 => 'produk tercampur dengan benda asing',
      1 => 'temuan produk tidak laik jual produk tercampur dengan benda asing',
    ),
    'response' => 'Terima kasih atas kepercayaan anda berbelanja di INDRACO Store. Mohon maaf untuk kendala yang terjadi 🙏.Kami senantiasa selalu menjaga kualitas produk untuk kepuasan konsumen selaku prioritas di toko online kami. Agar bisa segera menangani dan menindak-lanjuti keluhan kakak, kami memohon bantuannya untuk mengirimkan video unboxing dan mengetik informasi berikut:Nama anda:Produk yang anda beli:Tanggal transaksi:Tanggal produk diterima:Toko online yang anda gunakan:Tim kami akan merespon kendala anda terkait produk dan benda asing secara langsung, segera. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  89 => 
  array (
    'name' => 'Temuan promo yang tidak sesuai - Produk tertulis promo namun tidak terdapat hadiah atau tambahan lainnya',
    'keywords' => 
    array (
      0 => 'produk tertulis promo namun tidak terdapat hadiah atau tambahan lainnya',
      1 => 'temuan promo yang tidak sesuai produk tertulis promo namun tidak terdapat hadiah atau tambahan lainnya',
    ),
    'response' => 'Terima kasih atas kepercayaan anda berbelanja di INDRACO Store. Mohon maaf untuk kendala yang terjadi 🙏.Kami senantiasa selalu menjaga kualitas produk untuk kepuasan konsumen selaku prioritas di toko online kami. Agar bisa segera menangani dan menindak-lanjuti keluhan kakak, kami memohon bantuannya untuk mengirimkan video unboxing dan mengetik informasi berikut:Nama anda:Produk yang anda beli:Tanggal transaksi:Tanggal produk diterima:Toko online yang anda gunakan:Tim kami akan merespon kendala anda terkait promo tidak sesuai secara langsung, segera. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
  90 => 
  array (
    'name' => 'Temuan promo yang tidak sesuai - Hadiah tidak sesuai dengan keterangan promo',
    'keywords' => 
    array (
      0 => 'hadiah tidak sesuai dengan keterangan promo',
      1 => 'temuan promo yang tidak sesuai hadiah tidak sesuai dengan keterangan promo',
    ),
    'response' => 'Terima kasih atas kepercayaan anda berbelanja di INDRACO Store. Mohon maaf untuk kendala yang terjadi 🙏.Kami senantiasa selalu menjaga kualitas produk untuk kepuasan konsumen selaku prioritas di toko online kami. Agar bisa segera menangani dan menindak-lanjuti keluhan kakak, kami memohon bantuannya untuk mengirimkan video unboxing dan mengetik informasi berikut:Nama anda:Produk yang anda beli:Tanggal transaksi:Tanggal produk diterima:Toko online yang anda gunakan:Tim kami akan merespon kendala anda terkait keterangan promo secara langsung, segera. 😊🙏',
    'options' => 
    array (
      0 => 
      array (
        'label' => '💬 Bicara dengan CS Langsung',
        'value' => 'YA',
      ),
      1 => 
      array (
        'label' => '🔙 Kembali ke Menu Kategori',
        'value' => '1',
      ),
      2 => 
      array (
        'label' => '🏠 Menu Utama',
        'value' => 'MENU',
      ),
    ),
  ),
);

        // 6. Build Hierarchical Tree for Visual Decision Tree Management
        $findRule = function($val) use ($rules) {
            $valLower = mb_strtolower(trim($val));
            foreach ($rules as $r) {
                $kws = is_array($r['keywords']) ? $r['keywords'] : explode(',', $r['keywords']);
                foreach ($kws as $kw) {
                    if (mb_strtolower(trim($kw)) === $valLower) return $r;
                }
            }
            return null;
        };

        $visited = [];
        $buildNode = function($label, $value) use (&$buildNode, $findRule, &$visited) {
            $nodeKey = $value . '|' . $label;
            if (in_array($nodeKey, $visited) || (in_array($value, ['MENU', 'menu', '1', '2', '3']) && count($visited) > 10)) {
                return null;
            }
            $visited[] = $nodeKey;
            $rule = $findRule($value);
            $response = $rule ? $rule['response'] : '';
            $children = [];
            if ($rule && !empty($rule['options']) && is_array($rule['options'])) {
                foreach ($rule['options'] as $opt) {
                    $optVal = $opt['value'] ?? '';
                    if (in_array(strtoupper($optVal), ['MENU', 'YA', '1', '2', '3'])) continue;
                    $child = $buildNode($opt['label'] ?? $optVal, $optVal);
                    if ($child) $children[] = $child;
                }
            }
            return [
                'id'       => 'node_' . substr(md5($value . $label), 0, 8),
                'label'    => $label,
                'value'    => $value,
                'response' => $response,
                'children' => $children,
            ];
        };

        $tree = [];
        foreach ($rules[0]['options'] as $ro) {
            $visited = [];
            $node = $buildNode($ro['label'], $ro['value']);
            if ($node) $tree[] = $node;
        }

        // 7. Save or Update Widget Settings
        $widgetSetting = WidgetSetting::updateOrCreate(
            ['project_id' => $project->id],
            [
                'primary_color'       => '#1A1A1A',
                'accent_color'        => '#D4AF37',
                'position'            => 'bottom-right',
                'greeting_title'      => 'INDRACO Store',
                'greeting_subtitle'   => 'Layanan Pelanggan & Tanya Jawab Produk',
                'is_online'           => true,
                'bot_enabled'         => true,
                'bot_mode_query'       => true,
                'bot_mode_options'     => true,
                'bot_name'            => 'INDRACO Assistant',
                'bot_welcome_message' => $welcomeMessage,
                'bot_welcome_options' => $rules[0]['options'],
                'bot_offline_message' => 'Halo! Layanan konsultasi CS kami saat ini di luar jam operasional. Anda tetap dapat menggunakan bot otomatis atau meninggalkan pesan & nomor WhatsApp. Tim kami akan segera menghubungi Anda kembali.',
                'bot_rules'           => $rules,
                'bot_tree'            => $tree,
            ]
        );

        // 7. Output Integration Instructions & Embed Code
        $this->command->info('');
        $this->command->info('========================================================================');
        $this->command->info('  🎉 SEEDER BERHASIL: Chatbot INDRACO Store Siap Digunakan!');
        $this->command->info('========================================================================');
        $this->command->line('  • Tenant       : ' . $tenant->name . ' (' . $tenant->slug . ')');
        $this->command->line('  • Project      : ' . $project->name . ' (ID: ' . $project->id . ')');
        $this->command->line('  • Public Key   : ' . $apiKey->public_key);
        $this->command->line('  • Total Rules  : ' . count($rules) . ' aturan interaktif berhasil disemai.');
        $this->command->line('  • Bot Name     : ' . $widgetSetting->bot_name);
        $this->command->line('  • Mode         : Interactive Option Buttons (Mode Opsi Klik)');
        $this->command->info('------------------------------------------------------------------------');
        $this->command->info('  📋 KODE EMBED SCRIPT UNTUK WEBSITE (https://indracostore.com/):');
        $this->command->info('------------------------------------------------------------------------');
        $this->command->line('<script');
        $this->command->line('    src="https://beantalk.indracoglobal.com/widget/v1/chat.js"');
        $this->command->line('    data-project-key="' . $apiKey->public_key . '"');
        $this->command->line('    defer>');
        $this->command->line('</script>');
        $this->command->info('========================================================================');
        $this->command->info('');
    }
}
