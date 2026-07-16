<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Apartment;
use App\Models\BankIntegration;
use App\Models\BankTransaction;
use App\Models\BuildingBlock;
use App\Models\Due;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@aidat.local'],
            [
                'name' => 'Mehmet Yılmaz',
                'password' => Hash::make('Admin123!'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $site = Site::updateOrCreate(
            ['name' => 'Nilüfer Sitesi'],
            ['address' => 'Nilüfer / Bursa']
        );

        $blocks = collect(['A Blok', 'B Blok', 'C Blok'])
            ->mapWithKeys(fn (string $name) => [
                $name => BuildingBlock::updateOrCreate(
                    ['site_id' => $site->id, 'name' => $name],
                    ['site_id' => $site->id, 'name' => $name]
                ),
            ]);

        $apartments = [
            ['A Blok', 'A-01', 1, 'occupied', 'Ayşe Aksoy'],
            ['A Blok', 'A-02', 1, 'occupied', 'Kemal Özkan'],
            ['A Blok', 'A-07', 3, 'occupied', 'Murat Demir'],
            ['A Blok', 'A-12', 6, 'occupied', 'Elif Kaya'],
            ['A Blok', 'A-21', 10, 'occupied', 'Emre Şahin'],
            ['B Blok', 'B-04', 2, 'occupied', 'Ahmet Koç'],
            ['B Blok', 'B-11', 5, 'occupied', 'Osman Arslan'],
            ['B Blok', 'B-16', 8, 'occupied', 'Deniz Eren'],
            ['C Blok', 'C-02', 1, 'occupied', 'Zeynep Acar'],
            ['C Blok', 'C-08', 4, 'empty', null],
            ['C Blok', 'C-18', 9, 'occupied', 'Selin Taş'],
            ['C Blok', 'C-20', 10, 'occupied', 'Burak Yıldız'],
        ];

        foreach ($apartments as [$blockName, $number, $floor, $status, $residentName]) {
            $apartment = Apartment::updateOrCreate(
                ['building_block_id' => $blocks[$blockName]->id, 'number' => $number],
                ['floor_no' => $floor, 'status' => $status]
            );

            if ($residentName) {
                Resident::updateOrCreate(
                    ['apartment_id' => $apartment->id, 'full_name' => $residentName],
                    [
                        'phone' => '0555' . str_pad((string) $apartment->id, 7, '0', STR_PAD_LEFT),
                        'email' => 'sakin' . $apartment->id . '@example.com',
                        'resident_type' => $apartment->id % 2 === 0 ? 'tenant' : 'owner',
                        'is_active' => true,
                    ]
                );
            }
        }

        $dueRows = [
            ['A-01', 2450, '2026-07-25', 'Temmuz aidatı'],
            ['A-02', 2450, '2026-07-25', 'Temmuz aidatı'],
            ['A-07', 2450, '2026-07-25', 'Temmuz aidatı'],
            ['A-12', 2450, '2026-07-25', 'Temmuz aidatı'],
            ['A-21', 2450, '2026-07-25', 'Temmuz aidatı'],
            ['B-04', 2450, '2026-07-25', 'Temmuz aidatı'],
            ['B-11', 7350, '2026-07-10', 'Üç dönem gecikmiş bakiye'],
            ['B-16', 1820, '2026-07-25', 'Yakıt farkı'],
            ['C-02', 4900, '2026-07-10', 'İki dönem gecikmiş bakiye'],
            ['C-08', 2450, '2026-07-25', 'Boş daire aidatı'],
            ['C-18', 2450, '2026-07-25', 'Temmuz aidatı'],
            ['C-20', 2450, '2026-07-25', 'Temmuz aidatı'],
        ];

        foreach ($dueRows as [$apartmentNumber, $amount, $dueDate, $note]) {
            $apartment = Apartment::where('number', $apartmentNumber)->firstOrFail();

            Due::updateOrCreate(
                [
                    'apartment_id' => $apartment->id,
                    'period_year' => 2026,
                    'period_month' => 7,
                ],
                [
                    'amount' => $amount,
                    'due_date' => $dueDate,
                    'note' => $note,
                ]
            );
        }

        $payments = [
            ['A-01', 2450, 'bank', 'RCP-2026-0001', '2026-07-04 10:20:00'],
            ['A-07', 2450, 'card', 'RCP-2026-0002', '2026-07-13 09:45:00'],
            ['A-12', 2450, 'bank', 'RCP-2026-0003', '2026-07-13 11:10:00'],
            ['B-04', 1300, 'cash', 'RCP-2026-0004', '2026-07-13 14:25:00'],
            ['C-18', 2450, 'eft', 'RCP-2026-0005', '2026-07-09 16:00:00'],
        ];

        foreach ($payments as [$apartmentNumber, $amount, $method, $receiptNo, $paidAt]) {
            $due = Due::whereHas('apartment', fn ($query) => $query->where('number', $apartmentNumber))
                ->where('period_year', 2026)
                ->where('period_month', 7)
                ->firstOrFail();

            Payment::updateOrCreate(
                ['receipt_no' => $receiptNo],
                [
                    'due_id' => $due->id,
                    'amount' => $amount,
                    'method' => $method,
                    'paid_at' => $paidAt,
                ]
            );
        }

        foreach ([
            ['Bakım', 'Asansör periyodik bakım', 5800, '2026-07-08'],
            ['Temizlik', 'Temizlik personeli ödemesi', 12500, '2026-07-10'],
            ['Güvenlik', 'Güvenlik hizmet bedeli', 18000, '2026-07-12'],
        ] as [$category, $description, $amount, $date]) {
            Expense::updateOrCreate(
                ['site_id' => $site->id, 'description' => $description, 'expense_date' => $date],
                ['category' => $category, 'amount' => $amount]
            );
        }

        foreach ([
            ['Asansör bakımı', 'A Blok asansör bakımı 16 Temmuz 10:00 - 12:00 arasında yapılacaktır.', '2026-07-13'],
            ['Temmuz aidatı', 'Temmuz aidatı için son ödeme tarihi 25 Temmuz 2026 olarak belirlenmiştir.', '2026-07-01'],
        ] as [$title, $content, $date]) {
            Announcement::updateOrCreate(
                ['site_id' => $site->id, 'title' => $title],
                ['content' => $content, 'publish_date' => $date]
            );
        }

        $integration = BankIntegration::updateOrCreate(
            ['site_id' => $site->id, 'provider' => 'vakifbank'],
            [
                'environment' => 'test',
                'customer_no' => '000000000001',
                'account_no' => '00000000000000001',
                'iban' => 'TR000000000000000000000001',
                'corporate_username' => 'demo_kurum',
                'service_url' => 'https://vbtestservice.vakifbank.com.tr/HesapHareketleri.OnlineEkstre/SOnlineEkstreServis.svc?wsdl',
                'sync_interval_minutes' => 5,
                'last_synced_at' => now()->subMinutes(5),
                'is_active' => false,
            ]
        );

        $a01Due = Due::whereHas('apartment', fn ($query) => $query->where('number', 'A-01'))->where('period_year', 2026)->where('period_month', 7)->first();
        $a01Payment = $a01Due ? Payment::where('due_id', $a01Due->id)->first() : null;

        foreach ([
            [
                'VB-TX-0001',
                '88420001',
                '2026-07-04 10:20:00',
                2450,
                'Ayşe Aksoy',
                'TR110001500158000000000001',
                'AIDAT A-01 TEMMUZ',
                'matched',
                $a01Due?->id,
                $a01Payment?->id,
                'Açıklamadaki daire kodu ve tutar eşleşti.',
                null,
            ],
            [
                'VB-TX-0002',
                '88420002',
                '2026-07-13 17:45:00',
                2450,
                'Kemal Özkan',
                'TR220001500158000000000002',
                'aidat',
                'needs_review',
                null,
                null,
                null,
                'Açıklama genel. Daire kodu yok; gönderen bilgisiyle manuel onay bekliyor.',
            ],
            [
                'VB-TX-0003',
                '88420003',
                '2026-07-13 18:10:00',
                1800,
                'Bilinmeyen Gönderen',
                'TR990001500158000000000099',
                'site ödeme',
                'unmatched',
                null,
                null,
                null,
                'Gönderen adı, IBAN, tutar ve açıklama açık borçlarla güvenli eşleşmedi.',
            ],
        ] as [$bankId, $operationNo, $date, $amount, $senderName, $senderIban, $description, $status, $dueId, $paymentId, $matchReason, $failureReason]) {
            BankTransaction::updateOrCreate(
                ['provider' => 'vakifbank', 'bank_transaction_id' => $bankId],
                [
                    'bank_integration_id' => $integration->id,
                    'site_id' => $site->id,
                    'operation_no' => $operationNo,
                    'transaction_date' => $date,
                    'amount' => $amount,
                    'direction' => 'A',
                    'sender_name' => $senderName,
                    'sender_iban' => $senderIban,
                    'description' => $description,
                    'status' => $status,
                    'matched_due_id' => $dueId,
                    'matched_payment_id' => $paymentId,
                    'match_reason' => $matchReason,
                    'failure_reason' => $failureReason,
                    'processed_at' => in_array($status, ['matched', 'manual_matched'], true) ? $date : null,
                    'raw_payload' => ['source' => 'seed', 'provider' => 'vakifbank'],
                ]
            );
        }
    }
}
