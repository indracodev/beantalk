<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\Project;
use App\Models\ProjectDomain;
use App\Models\Tenant;
use App\Models\WidgetSetting;
use Illuminate\Database\Seeder;

class SupressoCoffeeChatbotSeeder extends Seeder
{
    /**
     * Seed Supresso Coffee chatbot knowledge tree & widget settings.
     * Target: SUPRESSO (supresso.com / Singapore & Global).
     * Strictly avoids altering or interfering with supresso.co.id or any other integration.
     * Option labels are clean and direct without numbers.
     *
     * Source: chatbot SSG.pdf / chatbot_flow_tree_markdown_transcript.md
     */
    public function run()
    {
        // 1. Ensure Tenant exists (Supresso is an INDRACO brand)
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'indraco'],
            [
                'name'      => 'PT Indraco Jaya Perkasa',
                'plan'      => 'enterprise',
                'is_active' => true,
            ]
        );

        app()->instance('current_tenant_id', $tenant->id);

        // 2. Identify Supresso Project safely (ONLY target 'supresso', strictly avoid 'supressocoid' / 'supresso.co.id')
        $project = Project::where('slug', 'supresso')
            ->orWhere(function ($query) {
                $query->where('name', 'SUPRESSO')
                      ->where('slug', '!=', 'supressocoid');
            })
            ->first();

        if (!$project) {
            $project = Project::create([
                'tenant_id' => $tenant->id,
                'name'      => 'SUPRESSO',
                'slug'      => 'supresso',
                'is_active' => true,
            ]);
        }

        // 3. Whitelist Domains for Supresso
        $domains = [
            'supresso.com',
            'www.supresso.com',
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

        // 4. API Key (Preserve existing key if already created, e.g. pk_live_supressoco_1970)
        $apiKey = ApiKey::where('project_id', $project->id)->first();
        if (!$apiKey) {
            $apiKey = ApiKey::create([
                'tenant_id'  => $tenant->id,
                'project_id' => $project->id,
                'name'       => 'SUPRESSO Web Widget Key',
                'public_key' => 'pk_live_supressoco_1970',
                'is_active'  => true,
            ]);
        }

        // 5. Bot Welcome Message & Interactive Options (Direct options, without numbers)
        $welcomeMessage = "Hi there! Thank you for reaching out to *Supresso Coffee*.
How may we assist you today? :)

Please choose an option below or type your inquiry:";

        $welcomeOptions = [
            ['label' => 'Order / How to Buy', 'value' => 'How to Buy'],
            ['label' => 'Order Issue', 'value' => 'Order Issue'],
            ['label' => 'Product', 'value' => 'Product'],
            ['label' => 'Membership', 'value' => 'Membership'],
            ['label' => 'About Event', 'value' => 'About Event'],
            ['label' => 'Checkout', 'value' => 'Checkout'],
            ['label' => 'Global Shipping', 'value' => 'Global Shipping'],
            ['label' => 'Coffee Recommendations', 'value' => 'Coffee Recommendations'],
            ['label' => 'Promotions & Deals', 'value' => 'Promotions & Deals'],
            ['label' => 'Marketing', 'value' => 'Marketing'],
            ['label' => 'Customer Feedback', 'value' => 'Customer Feedback'],
            ['label' => 'Chat with CS', 'value' => 'CS'],
        ];

        $rules = [
            // Root / Main Menu
            [
                'name'     => 'Main Menu',
                'keywords' => ['menu', 'main menu', 'bantuan', 'help', 'mulai', 'start', 'halo', 'hi', 'hey', 'hello', 'options', 'pilihan'],
                'response' => $welcomeMessage,
                'options'  => $welcomeOptions,
            ],

            // Branch 1: How to Buy (Order / Purchase Coffee)
            [
                'name'     => 'How to Buy Our Coffee',
                'keywords' => ['how to buy', 'how to buy our coffee', 'order', 'orders', 'buy', 'beli', 'pesan', 'beli kopi', 'order coffee', 'where to buy', 'cara beli', 'cara pesan'],
        'response' => "*How to Buy Our Coffee*\nYou can purchase Supresso Coffee directly through our official channels below:\n\n• Website: supresso.com (Worldwide shipping & promotions)\n• Shopee: Official Store (Singapore)\n• Lazada: Flagship Store (Singapore)\n\n_If you have an issue with an existing order, please choose *Order Issue*._",
                'options'  => [
                    ['label' => 'Buy from Website', 'value' => 'Buy from Website'],
                    ['label' => 'Buy from Shopee', 'value' => 'Buy from Shopee'],
                    ['label' => 'Buy from Lazada', 'value' => 'Buy from Lazada'],
                    ['label' => 'Order Issue', 'value' => 'Order Issue'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Buy from Website',
                'keywords' => ['buy from website', 'official website', 'website', 'supresso.com', 'web'],
                'response' => "Kindly check our website https://www.supresso.com/ for your reference.\n\nShould you have any questions, please do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Buy from Shopee', 'value' => 'Buy from Shopee'],
                    ['label' => 'Buy from Lazada', 'value' => 'Buy from Lazada'],
                    ['label' => 'Order Issue', 'value' => 'Order Issue'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Buy from Shopee',
                'keywords' => ['buy from shopee', 'shopee', 'shopee singapore', 'toko shopee'],
                'response' => "Kindly check our official store on Shopee Singapore (https://shopee.sg/supresso) for your reference.\n\nShould you have any questions, please do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Buy from Website', 'value' => 'Buy from Website'],
                    ['label' => 'Buy from Lazada', 'value' => 'Buy from Lazada'],
                    ['label' => 'Order Issue', 'value' => 'Order Issue'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Buy from Lazada',
                'keywords' => ['buy from lazada', 'lazada', 'lazada singapore', 'toko lazada'],
                'response' => "Kindly check our official store on Lazada Singapore (https://www.lazada.sg/shop/supresso) for your reference.\n\nShould you have any questions, please do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Buy from Website', 'value' => 'Buy from Website'],
                    ['label' => 'Buy from Shopee', 'value' => 'Buy from Shopee'],
                    ['label' => 'Order Issue', 'value' => 'Order Issue'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],

            // Branch 2: Order Issue (Order Assistance & Issues)
            [
                'name'     => 'Order Issue Support',
                'keywords' => ['order issue', 'order assistance', 'order problem', 'issue', 'masalah pesanan', 'kendala pesanan', 'kendala order', 'bantuan pesanan'],
        'response' => "*Order Issue & Assistance*\nWe are here to help your order issue. Please choose an option below:",
                'options'  => [
                    ['label' => 'Order Status', 'value' => 'Order Status'],
                    ['label' => 'Cancellation', 'value' => 'Cancellation'],
                    ['label' => 'Return Policy', 'value' => 'Return Policy'],
                    ['label' => 'Package Not Received', 'value' => 'Package Not Received'],
                    ['label' => 'Shipment Issue', 'value' => 'Shipment Issue'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Order Status',
                'keywords' => ['order status', 'track order', 'tracking', 'track', 'where is my order', 'cek resi', 'status pesanan'],
                'response' => "Orders are typically fulfilled within 2-3 business days. Once your order has been fulfilled, you will receive an email with tracking information.\n\nPlease expect additional time for delivery during high volume sale or holiday periods.\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Cancellation', 'value' => 'Cancellation'],
                    ['label' => 'Return Policy', 'value' => 'Return Policy'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Cancellation',
                'keywords' => ['cancellation', 'cancelation', 'cancel order', 'cancel', 'batal', 'batalkan pesanan'],
                'response' => "We process and complete orders as quickly as possible. If you have any modification requests or order cancelations, please notify us immediately through our chat.\n\nWe will do our best to accommodate your request, but please note that we can not cancel orders that have been shipped.\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Order Status', 'value' => 'Order Status'],
                    ['label' => 'Return Policy', 'value' => 'Return Policy'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Return Policy',
                'keywords' => ['return policy', 'return', 'refund', 'retur', 'pengembalian', 'exchange', 'rusak'],
                'response' => "All forms of order errors and damage to goods during delivery are not our responsibility. Please do an unboxing video to claim and ask our staff to find solutions.\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Order Status', 'value' => 'Order Status'],
                    ['label' => 'Cancellation', 'value' => 'Cancellation'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],

            // Branch 2: Product
            [
                'name'     => 'Product Assistance',
                'keywords' => ['product', 'products', 'produk', 'produk supresso', 'katalog produk', 'product assistance'],
        'response' => "*Product Assistance*\nThank you for shopping with Supresso Coffee. We are here to assist you with our products. Please select what you need:",
                'options'  => [
                    ['label' => 'Cannot Find Product', 'value' => 'Cannot Find Product'],
                    ['label' => 'Add to Cart Error', 'value' => 'Add to Cart Error'],
                    ['label' => 'Price Adjustment', 'value' => 'Price Adjustment'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Cannot Find Product',
                'keywords' => ['cannot find product', 'find product', 'search product', 'cari produk', 'produk tidak ada'],
                'response' => "Thank you for shopping with Supresso Coffee.\n\nKindly fill up below the product information:\nProduct name:\n\nWe'll get back to you with further information :)\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Add to Cart Error', 'value' => 'Add to Cart Error'],
                    ['label' => 'Price Adjustment', 'value' => 'Price Adjustment'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Add to Cart Error',
                'keywords' => ['add to cart error', 'add to cart', 'cart error', 'keranjang', 'tidak bisa tambah keranjang', 'tambah keranjang'],
                'response' => "Thank you for shopping with Supresso Coffee.\n\nKindly fill up below the information:\nUsername:\n\nWe'll check with our team and get back to you the soonest!\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Cannot Find Product', 'value' => 'Cannot Find Product'],
                    ['label' => 'Price Adjustment', 'value' => 'Price Adjustment'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Price Adjustment',
                'keywords' => ['price adjustment', 'price match', 'penyesuaian harga', 'harga', 'price'],
                'response' => "supresso.com does not have a price match guarantee.\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Cannot Find Product', 'value' => 'Cannot Find Product'],
                    ['label' => 'Add to Cart Error', 'value' => 'Add to Cart Error'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],

            // Branch 3: Membership
            [
                'name'     => 'Membership & Account',
                'keywords' => ['membership', 'account', 'akun', 'member', 'membership & account'],
        'response' => "*Membership & Account*\nWe are here to assist you with your Supresso membership and account. Please select an option below:",
                'options'  => [
                    ['label' => 'Account Login Issue', 'value' => 'Account Login Issue'],
                    ['label' => 'Suspicious Activity', 'value' => 'Suspicious Activity'],
                    ['label' => 'Unsubscribe', 'value' => 'Unsubscribe'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Account Login Issue',
                'keywords' => ['account login issue', 'account login', 'login issue', 'kendala akun', 'login', 'masalah login'],
        'response' => "*Account Login*\nPlease let us know the issue you are facing with your account:",
                'options'  => [
                    ['label' => 'Cannot Login', 'value' => 'Cannot Login'],
                    ['label' => 'Reset Password', 'value' => 'Reset Password'],
                    ['label' => 'Forgot Password', 'value' => 'Forgot Password'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Cannot Login',
                'keywords' => ['cannot login', 'cant login', 'gagal login', 'tidak bisa login'],
                'response' => "Thank you for shopping with Supresso Coffee.\n\nKindly fill up below the information:\nUsername / Email:\n\nWe'll check with our team and get back to you the soonest!",
                'options'  => [
                    ['label' => 'Reset Password', 'value' => 'Reset Password'],
                    ['label' => 'Forgot Password', 'value' => 'Forgot Password'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Reset Password',
                'keywords' => ['reset password', 'reset kata sandi', 'ganti password'],
                'response' => "To reset your password:\n1. Go to the login page on supresso.com\n2. Click on 'Forgot your password?'\n3. Enter your registered email address\n4. Follow the instructions sent to your email to set a new password.\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Forgot Password', 'value' => 'Forgot Password'],
                    ['label' => 'Cannot Login', 'value' => 'Cannot Login'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Forgot Password',
                'keywords' => ['forgot password', 'lupa password', 'lupa kata sandi'],
                'response' => "If you forgot your password, please click 'Forgot Password' on the login screen and enter your email address. A password reset link will be sent to your inbox shortly.\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Reset Password', 'value' => 'Reset Password'],
                    ['label' => 'Cannot Login', 'value' => 'Cannot Login'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Suspicious Activity',
                'keywords' => ['suspicious activity', 'suspicious', 'hack', 'keamanan', 'mencurigakan'],
                'response' => "Kindly provide your email address to proceed further, e.g. yourname@mail.com.\n\nOur security team will investigate immediately.",
                'options'  => [
                    ['label' => 'Account Login Issue', 'value' => 'Account Login Issue'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Unsubscribe',
                'keywords' => ['unsubscribe', 'berhenti langganan', 'stop email', 'stop newsletter', 'unsubscribe newsletter'],
                'response' => "We're sorry to see you go! If you're still interested in purchasing our products, you can find them on supresso.com, Shopee, and Lazada. We'll also be offering more deals soon. Hope to see you again!\n\nTo unsubscribe from our newsletter, click the 'Unsubscribe' link at the bottom of our emails or send 'Unsubscribe' to our WhatsApp at +65 8792 0780.\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],

            // Branch 4: About Event & Partnership
            [
                'name'     => 'About Event & Partnership',
                'keywords' => ['about event', 'event', 'partnership or bulk', 'partnership', 'kerjasama', 'distributor'],
        'response' => "*About Event, Partnership & Bulk Purchase*\nInterested in partnering or purchasing in bulk with Supresso Coffee? Please choose an option below:",
                'options'  => [
                    ['label' => 'How to be a Distributor', 'value' => 'How to be a Distributor'],
                    ['label' => 'Promote Our Products', 'value' => 'Promote Our Products'],
                    ['label' => 'Office Needs', 'value' => 'Office Needs'],
                    ['label' => 'Bulk Order', 'value' => 'Bulk Order'],
                    ['label' => 'Event Supply', 'value' => 'Event Supply'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'How to be a Distributor',
                'keywords' => ['how to be a distributor', 'distributor', 'distributor supresso', 'reseller', 'keagenan'],
                'response' => "For Supresso Coffee, our distributors will place orders directly for our fresh coffee from our manufacturer in Indonesia, with a minimum order of 1 pallet. Please feel free to send your inquiries of the product you're interested in to our email:\nsg@supresso.com / adm_si@supresso.com for more information, or simply chat with us here. Thank you!\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Bulk Order', 'value' => 'Bulk Order'],
                    ['label' => 'Office Needs', 'value' => 'Office Needs'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Promote Our Products',
                'keywords' => ['promote our products', 'promote', 'endorsement', 'influencer', 'kol', 'affiliate'],
                'response' => "Supresso Coffee is open for endorsement. Kindly send your proposal to our email sg@supresso.com / adm_si@supresso.com to get more info or simply chat with us here. Thank you!\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'How to be a Distributor', 'value' => 'How to be a Distributor'],
                    ['label' => 'Event Supply', 'value' => 'Event Supply'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Office Needs',
                'keywords' => ['office needs', 'office', 'kantor', 'coffee for office', 'hotel'],
                'response' => "Supresso Coffee is available to supply coffee for offices, hotel and other business sectors.\n\nGet a quote by sending us email at sg@supresso.com / adm_si@supresso.com or contact us at +65 8792 0780 for more information.",
                'options'  => [
                    ['label' => 'Bulk Order', 'value' => 'Bulk Order'],
                    ['label' => 'Event Supply', 'value' => 'Event Supply'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Bulk Order',
                'keywords' => ['bulk order', 'bulk', 'wholesale', 'grosir', 'partai besar'],
                'response' => "Supresso Coffee is available for wholesale purchase with minimum order quantity. The shipment will be shipped directly from our manufacturer in Indonesia to your door (terms and conditions apply).\n\nPlease send your inquiry to sg@supresso.com / adm_si@supresso.com or contact us at +65 8792 0780.",
                'options'  => [
                    ['label' => 'How to be a Distributor', 'value' => 'How to be a Distributor'],
                    ['label' => 'Office Needs', 'value' => 'Office Needs'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Event Supply',
                'keywords' => ['event supply', 'wedding', 'private parties', 'acara', 'pesta'],
                'response' => "Supresso Coffee is available to supply coffee for your events such as weddings, private parties, etc.\n\nPlease contact us at +65 8792 0780 or email sg@supresso.com / adm_si@supresso.com to fill in your order details and we will get back to you with quotation.\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Office Needs', 'value' => 'Office Needs'],
                    ['label' => 'Bulk Order', 'value' => 'Bulk Order'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],

            // Branch 5: Checkout
            [
                'name'     => 'Checkout Assistance',
                'keywords' => ['checkout', 'checkout assistance', 'bayar', 'proses bayar'],
        'response' => "*Checkout Assistance*\nWe are here to assist you with your checkout and payment. Please select an option:",
                'options'  => [
                    ['label' => 'Kris+ Voucher', 'value' => 'Kris+ Voucher'],
                    ['label' => 'Checkout Button', 'value' => 'Checkout Button'],
                    ['label' => 'Payment Methods', 'value' => 'Payment Methods'],
                    ['label' => 'Order Issue', 'value' => 'Order Issue'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Kris+ Voucher',
                'keywords' => ['kris+ voucher', 'kris voucher', 'kris+', 'voucher', 'promo code', 'discount code', 'diskon'],
                'response' => "Redeem your Kris+ vouchers for an extra 20% OFF* exclusive on our website!\n\nT&C*:\n1. This SGD 10 promo code must be redeemed by 30 June 2025.\n2. No extensions are allowed.\n3. Valid for redemption online at Supresso official website (supresso.com).\n4. Promo codes are valid only for full-priced items and cannot be applied to discounted or promotional items, it is only valid for transactions above SGD 30.\n5. Orders above SGD 30 will be entitled to free shipping.\n6. Limited to 1 redemption per order (one-time usage only).\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Checkout Button', 'value' => 'Checkout Button'],
                    ['label' => 'Payment Methods', 'value' => 'Payment Methods'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Checkout Button Issue',
                'keywords' => ['checkout button', 'cannot checkout', 'cant checkout', 'tombol checkout', 'gagal checkout'],
                'response' => "Thank you for shopping with Supresso Coffee.\n\nIf the checkout button is not responding:\n1. Ensure all mandatory fields (name, full shipping address, postal code) are filled.\n2. Check if selected items are currently in stock.\n3. Try refreshing your browser or clearing cache.\n\nIf the problem persists, kindly provide your username and screenshot to our team.",
                'options'  => [
                    ['label' => 'Payment Methods', 'value' => 'Payment Methods'],
                    ['label' => 'Order Issue', 'value' => 'Order Issue'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Payment Methods',
                'keywords' => ['payment methods', 'payment', 'pembayaran', 'cara bayar', 'metode pembayaran'],
        'response' => "*Payment Methods at Supresso Coffee*\nWe support various secure payment methods worldwide. Please select for details:",
                'options'  => [
                    ['label' => 'Credit/Debit Cards', 'value' => 'Credit/Debit Cards'],
                    ['label' => 'Shop Pay Installment', 'value' => 'Shop Pay Installment'],
                    ['label' => 'Price Adjustment', 'value' => 'Price Adjustment'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Credit/Debit Cards',
                'keywords' => ['credit/debit cards', 'credit card', 'debit card', 'visa', 'mastercard', 'paypal', 'apple pay', 'google pay'],
                'response' => "• Credit/Debit Cards: We accept Visa, Mastercard, and American Express worldwide.\n• Paypal: Available worldwide. At checkout, choose Paypal as the payment method and log into your PayPal account.\n• Apple Pay: Available in supported countries on mobile version.\n• Google Pay: Available worldwide.\n• Union Pay: Available in supported countries.\n• Shop Pay: Available in the United States.\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Shop Pay Installment', 'value' => 'Shop Pay Installment'],
                    ['label' => 'Kris+ Voucher', 'value' => 'Kris+ Voucher'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Shop Pay Installment',
                'keywords' => ['shop pay installment', 'shop pay', 'cicilan', 'installment'],
                'response' => "To pay for an order using Shop Pay Installments, the following eligibility criteria apply:\n\n• You need to have a United States billing address, and be signed up for Shop Pay.\n• Your order needs to be between 50 USD and 20,000 USD, including shipping and taxes.\n• If you're ordering a physical product that requires shipping, then you need to provide a United States shipping address. If you're only purchasing digital products, then a United States shipping address isn't required.",
                'options'  => [
                    ['label' => 'Credit/Debit Cards', 'value' => 'Credit/Debit Cards'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Package Not Received',
                'keywords' => ['package not received', 'i haven\'t received my order', 'belum terima pesanan', 'belum sampai', 'package'],
                'response' => "Thank you for shopping with Supresso Coffee.\n\nKindly fill up below the information:\nOrder ID:\nFull Name:\nContact Number:\n\nWe'll check with our logistics partner and get back to you the soonest!",
                'options'  => [
                    ['label' => 'Shipment Issue', 'value' => 'Shipment Issue'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Shipment Issue',
                'keywords' => ['shipment issue', 'shipment', 'masalah pengiriman', 'kendala alamat', 'address error'],
        'response' => "*Shipment Assistance*\nWe are here to help your shipment issue:",
                'options'  => [
                    ['label' => 'Cannot Add Full Address', 'value' => 'Cannot Add Full Address'],
                    ['label' => 'Shipping Options', 'value' => 'Shipping Options'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Cannot Add Full Address',
                'keywords' => ['cannot add full address', 'cannot add full address for shipment', 'alamat tidak lengkap', 'gagal simpan alamat'],
                'response' => "Thank you for shopping with Supresso Coffee.\n\nKindly fill up below the information:\nUsername / Email:\nComplete Shipping Address:\nPostal Code:\n\nOur team will assist in updating your address manually :)",
                'options'  => [
                    ['label' => 'Shipping Options', 'value' => 'Shipping Options'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Information & Partnership',
                'keywords' => ['information & partnership', 'information and partnership', 'what do you need', 'informasi'],
                'response' => "*Information & Partnership*\nWhat do you need assistance with?",
                'options'  => [
                    ['label' => 'Catalog', 'value' => 'Catalog'],
                    ['label' => 'Current Promotion', 'value' => 'Current Promotion'],
                    ['label' => 'How to Buy Our Coffee', 'value' => 'How to Buy Our Coffee'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Catalog',
                'keywords' => ['catalog', 'katalog', 'daftar produk', 'menu kopi'],
                'response' => "Kindly check our website https://www.supresso.com/ for our complete coffee catalog.\n\nShould you have any questions, please do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Current Promotion', 'value' => 'Current Promotion'],
                    ['label' => 'How to Buy Our Coffee', 'value' => 'How to Buy Our Coffee'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Current Promotion',
                'keywords' => ['current promotion', 'promotions & deals', 'promotion', 'coffee deals', 'deals', 'promo', 'diskon'],
        'response' => "*Current Promotions & Coffee Deals Just For You!*\nCheck out our ongoing promotions:",
                'options'  => [
                    ['label' => 'Summer Sales', 'value' => 'Summer Sales'],
                    ['label' => 'Father\'s Day Promo', 'value' => 'Father\'s Day Promo'],
                    ['label' => 'Payday Promo', 'value' => 'Payday Promo'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Summer Sales',
                'keywords' => ['summer sales', 'summer sale', 'summer promo'],
                'response' => "Enjoy special Summer Sales discounts on selected Supresso blends and single origins! Check https://www.supresso.com/ for the latest coupon codes and bundle deals.\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Father\'s Day Promo', 'value' => 'Father\'s Day Promo'],
                    ['label' => 'Payday Promo', 'value' => 'Payday Promo'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Father\'s Day Promo',
                'keywords' => ['father\'s day promo', 'father\'s day', 'fathers day', 'hari ayah'],
                'response' => "Special Father's Day coffee bundle gift sets are available! Discover curated roasts packaged in premium gift boxes on https://www.supresso.com/.\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Summer Sales', 'value' => 'Summer Sales'],
                    ['label' => 'Payday Promo', 'value' => 'Payday Promo'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Payday Promo',
                'keywords' => ['payday promo', 'payday', 'payday sale', 'gajian'],
                'response' => "Payday coffee treat! Enjoy complimentary shipping and exclusive voucher codes every month during Payday on supresso.com.\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Summer Sales', 'value' => 'Summer Sales'],
                    ['label' => 'Father\'s Day Promo', 'value' => 'Father\'s Day Promo'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'How to Buy Our Coffee',
                'keywords' => ['how to buy our coffee', 'how to buy', 'cara beli', 'dimana beli', 'where to buy'],
                'response' => "Supresso Coffee is available through several official channels. Where would you like to shop?",
                'options'  => [
                    ['label' => 'Buy from Shopee', 'value' => 'Buy from Shopee'],
                    ['label' => 'Buy from Lazada', 'value' => 'Buy from Lazada'],
                    ['label' => 'Buy from Website', 'value' => 'Buy from Website'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Buy from Shopee',
                'keywords' => ['buy from shopee', 'shopee', 'toko shopee'],
                'response' => "Visit our official Supresso store on Shopee Singapore for exciting flash deals, cashback vouchers, and fast delivery!\n\nShopee: https://shopee.sg/supressocoffee",
                'options'  => [
                    ['label' => 'Buy from Lazada', 'value' => 'Buy from Lazada'],
                    ['label' => 'Buy from Website', 'value' => 'Buy from Website'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Buy from Lazada',
                'keywords' => ['buy from lazada', 'lazada', 'toko lazada'],
                'response' => "Visit our official Supresso flagship store on Lazada Singapore for LazMall authentic guarantees and exclusive brand vouchers!\n\nLazada: https://www.lazada.sg/shop/supresso-coffee",
                'options'  => [
                    ['label' => 'Buy from Shopee', 'value' => 'Buy from Shopee'],
                    ['label' => 'Buy from Website', 'value' => 'Buy from Website'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Buy from Website',
                'keywords' => ['buy from website', 'website', 'official website', 'supresso.com'],
                'response' => "Shop directly at our official website https://www.supresso.com/ to enjoy our full single origin lineup, Kris+ redemption, and complimentary shipping on orders over SGD 30!",
                'options'  => [
                    ['label' => 'Buy from Shopee', 'value' => 'Buy from Shopee'],
                    ['label' => 'Buy from Lazada', 'value' => 'Buy from Lazada'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],

            // Branch 6: Global Shipping
            [
                'name'     => 'Global Shipping',
                'keywords' => ['global shipping', 'shipping', 'pengiriman', 'ongkir', 'delivery', 'international shipping'],
                'response' => "Most international orders will ship within 2 business days after placing your order. International transit times is vary based on destination but generally take between 7-15 business days depending on custom clearance. Please expect additional time for delivery during high-volume sale or holiday periods. Once your order has shipped, you will receive an email with a link.\n\nShipping options:\nDHL International Express\nNinja Van: Singapore, Malaysia, Indonesia only\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Shipping Options', 'value' => 'Shipping Options'],
                    ['label' => 'Currency', 'value' => 'Currency'],
                    ['label' => 'Taxes & Duties', 'value' => 'Taxes & Duties'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Shipping Options',
                'keywords' => ['shipping options', 'shipping option', 'free shipping', 'complimentary shipping', 'ninjavan', 'j&t'],
                'response' => "Enjoy Complimentary Shipping on orders over S$30!\n\nShipping options: Ninjavan, J&T, QExpress.\n\nPlease allow 3 to 10 business days from the time you place your order to receive your package. During times of high order volume OR public holidays, extra time may be needed for your order to arrive.\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Currency', 'value' => 'Currency'],
                    ['label' => 'Taxes & Duties', 'value' => 'Taxes & Duties'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Currency',
                'keywords' => ['currency', 'mata uang', 'sgd', 'usd', 'payment currency'],
                'response' => "We display and collect payment in your local currency. Please ensure you've selected your shipping destination in the country selector in the header to see prices and checkout in your local currency and payment methods.",
                'options'  => [
                    ['label' => 'Shipping Options', 'value' => 'Shipping Options'],
                    ['label' => 'Taxes & Duties', 'value' => 'Taxes & Duties'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Taxes & Duties',
                'keywords' => ['taxes & duties', 'taxes and duties', 'taxes', 'duties', 'pajak', 'gst', 'customs'],
                'response' => "Duties and taxes are included or paid at checkout for most countries.\n\nSingapore: Duties are included in the product price and taxes are paid at checkout for orders shipping to Singapore. You will not be charged additional duties and taxes at delivery.",
                'options'  => [
                    ['label' => 'Shipping Options', 'value' => 'Shipping Options'],
                    ['label' => 'Currency', 'value' => 'Currency'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],

            // Branch 8: Product Recommendation / Coffee Variety
            [
                'name'     => 'Coffee Recommendations',
                'keywords' => ['coffee recommendations', 'product recommendation', 'coffee variety', 'rekomendasi', 'rekomendasi kopi', 'variety', 'houseblend', 'arabica', 'robusta'],
        'response' => "*Supresso Coffee Recommendations*\nDiscover the finest Indonesian and world coffees crafted to perfection. Choose a variety to see our recommendations:",
                'options'  => [
                    ['label' => 'Houseblend Coffee', 'value' => 'Houseblend Coffee'],
                    ['label' => 'Arabica Coffee', 'value' => 'Arabica Coffee'],
                    ['label' => 'Robusta Coffee', 'value' => 'Robusta Coffee'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Houseblend Coffee',
                'keywords' => ['houseblend coffee', 'our houseblend coffee', 'houseblend', 'house blend', 'equilibre', 'espresso'],
                'response' => "Here are our houseblend recommendations:\n• Equilibre Coffee\n• Espresso Coffee\n• Lumiere Coffee\n• Exotic Coffee\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Arabica Coffee', 'value' => 'Arabica Coffee'],
                    ['label' => 'Robusta Coffee', 'value' => 'Robusta Coffee'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Arabica Coffee',
                'keywords' => ['arabica coffee', 'our arabica coffee', 'arabica', 'luwak', 'toraja', 'mandheling', 'flores', 'gayo'],
                'response' => "Here are our Arabica single-origin recommendations:\n• Luwak Coffee\n• Toraja Kalosi Coffee\n• Sumatra Mandheling Coffee\n• Flores Bajawa Coffee\n• Aceh Gayo Coffee\n• Signature Coffee\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Houseblend Coffee', 'value' => 'Houseblend Coffee'],
                    ['label' => 'Robusta Coffee', 'value' => 'Robusta Coffee'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Robusta Coffee',
                'keywords' => ['robusta coffee', 'our robusta coffee', 'robusta', 'lampung', 'nanyang', 'ombre', 'west java', 'java coffee'],
                'response' => "Here are our Robusta recommendations:\n• Lampung Coffee\n• Nanyang Coffee\n• Ombre Coffee\n• West Java Coffee\n• Java Coffee\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Houseblend Coffee', 'value' => 'Houseblend Coffee'],
                    ['label' => 'Arabica Coffee', 'value' => 'Arabica Coffee'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],

            // Branch 9: Marketing
            [
                'name'     => 'Marketing',
                'keywords' => ['marketing', 'promotional information', 'newsletter', 'promosi'],
        'response' => "*Marketing & Promotional Information*\nStay updated with our latest releases, exclusive discounts, and coffee brewing tips:",
                'options'  => [
                    ['label' => 'Email Marketing', 'value' => 'Email Marketing'],
                    ['label' => 'WhatsApp Marketing', 'value' => 'WhatsApp Marketing'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Email Marketing',
                'keywords' => ['email marketing', 'newsletter', 'email newsletter', 'subscribe email'],
                'response' => "Scroll to the bottom of the login page on the website or visit supresso.com to subscribe to our email newsletter. As a subscriber, you'll be first to know about exclusive offers, product launches, and more.\n\nPlease note that terms & conditions apply.\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'WhatsApp Marketing', 'value' => 'WhatsApp Marketing'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'WhatsApp Marketing',
                'keywords' => ['whatsapp marketing', 'whatsapp', 'wa marketing', 'subscribe whatsapp'],
                'response' => "To subscribe or unsubscribe from our WhatsApp promotional broadcasts, please send a WhatsApp message or reply 'Unsubscribe' to +65 8792 0780.\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Email Marketing', 'value' => 'Email Marketing'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],

            // Branch 10: Feedback
            [
                'name'     => 'Customer Feedback',
                'keywords' => ['customer feedback', 'feedback', 'customer experience', 'komplain', 'saran', 'product care', 'ulasan'],
        'response' => "*Customer Experience & Product Care*\nYour satisfaction is our priority. Please select an option:",
                'options'  => [
                    ['label' => 'Customer Experience', 'value' => 'Customer Experience'],
                    ['label' => 'Product Care', 'value' => 'Product Care'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Customer Experience',
                'keywords' => ['customer experience', 'experience', 'order feedback', 'keluhan'],
                'response' => "Thank you for shopping with Supresso Coffee.\n\nKindly fill up below information:\nOrder no:\nBest before date:\n\nWe'll check and get back to you the soonest!",
                'options'  => [
                    ['label' => 'Product Care', 'value' => 'Product Care'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
            [
                'name'     => 'Product Care',
                'keywords' => ['product care', 'care', 'storage', 'penyimpanan', 'simpan kopi', 'cara simpan'],
                'response' => "Store in a cool dry place away from direct sunlight and high temperatures.\n\nPlease refrain from consuming the product if the packaging appears to be unsealed.\n\nShould you have anymore questions, do not hesitate to contact us!",
                'options'  => [
                    ['label' => 'Customer Experience', 'value' => 'Customer Experience'],
                    ['label' => 'Main Menu', 'value' => 'MENU'],
                    ['label' => 'Chat with CS', 'value' => 'CS'],
                ],
            ],
        ];

        // 6. Build Hierarchical bot_tree for Visual Admin Graph / Visualizer
        $visited = [];
        $buildNode = function ($label, $value) use (&$buildNode, $rules, &$visited) {
            if (in_array($value, $visited)) return null;
            $visited[] = $value;

            $rule = null;
            foreach ($rules as $r) {
                $kws = array_map('strtolower', $r['keywords']);
                if (in_array(strtolower($value), $kws) || strtolower($r['name']) === strtolower($value)) {
                    $rule = $r;
                    break;
                }
            }

            $response = $rule ? $rule['response'] : '';
            $children = [];
            if ($rule && !empty($rule['options']) && is_array($rule['options'])) {
                foreach ($rule['options'] as $opt) {
                    $optVal = $opt['value'] ?? '';
                    if (in_array(strtoupper($optVal), ['MENU', 'YA', 'CS', 'MAIN MENU'])) continue;
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
        foreach ($welcomeOptions as $ro) {
            if (in_array(strtoupper($ro['value']), ['MENU', 'YA', 'CS'])) continue;
            $visited = [];
            $node = $buildNode($ro['label'], $ro['value']);
            if ($node) $tree[] = $node;
        }

        // 7. Save or Update Widget Settings safely without disturbing existing social channels, sound, or hours
        $widgetSetting = WidgetSetting::where('project_id', $project->id)->first();
        if (!$widgetSetting) {
            $widgetSetting = new WidgetSetting(['project_id' => $project->id]);
            $widgetSetting->primary_color = '#ff5000'; // Supresso brand orange
            $widgetSetting->accent_color  = '#FFFFFF';
            $widgetSetting->position      = 'bottom-right';
            $widgetSetting->language      = 'en';
            $widgetSetting->greeting_title = 'Supresso Coffee';
            $widgetSetting->greeting_subtitle = 'How may we assist you today? Feel free to ask!';
            $widgetSetting->support_title = 'Customer Support';
            $widgetSetting->is_online     = true;
        }

        $widgetSetting->bot_enabled         = true;
        $widgetSetting->bot_mode_query       = true;
        $widgetSetting->bot_mode_options     = true;
        $widgetSetting->bot_name            = $widgetSetting->bot_name && $widgetSetting->bot_name !== 'BeanBot' ? $widgetSetting->bot_name : 'Supresso Assistant';
        $widgetSetting->bot_welcome_message = $welcomeMessage;
        $widgetSetting->bot_welcome_options = $welcomeOptions;
        $widgetSetting->bot_rules           = $rules;
        $widgetSetting->bot_tree            = $tree;
        if (empty($widgetSetting->bot_offline_message)) {
            $widgetSetting->bot_offline_message = "Thank you for reaching out to Supresso Coffee. Our team is currently offline. Please leave your message and contact details, and we will get back to you shortly!";
        }
        $widgetSetting->save();

        // 8. Output Summary
        if ($this->command) {
            $this->command->info('');
            $this->command->info('========================================================================');
            $this->command->info('  [SUCCESS] SEEDER BERHASIL: Chatbot SUPRESSO Siap Digunakan!');
            $this->command->info('========================================================================');
            $this->command->line('  • Tenant       : ' . $tenant->name . ' (' . $tenant->slug . ')');
            $this->command->line('  • Project      : ' . $project->name . ' (Slug: ' . $project->slug . ', ID: ' . $project->id . ')');
            $this->command->line('  • Public Key   : ' . $apiKey->public_key);
            $this->command->line('  • Total Rules  : ' . count($rules) . ' aturan interaktif disemai.');
            $this->command->line('  • Bot Name     : ' . $widgetSetting->bot_name);
            $this->command->line('  • Opsi Tampilan: Langsung tanpa nomor sesuai instruksi');
            $this->command->info('========================================================================');
        }
    }
}
