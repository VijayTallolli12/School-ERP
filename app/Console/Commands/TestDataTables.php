<?php

namespace App\Console\Commands;

use App\Modules\Fees\Models\FeeCategory;
use App\Modules\Fees\Repositories\FeeRepository;
use App\Modules\Notifications\Repositories\NotificationRepository;
use Illuminate\Console\Command;
use Yajra\DataTables\Facades\DataTables;

class TestDataTables extends Command
{
    protected $signature = 'test:datatables';
    protected $description = 'Test DataTable responses';

    public function handle(): int
    {
        $feeRepo = app(FeeRepository::class);
        $notifRepo = app(NotificationRepository::class);

        $this->info('=== Categories (no auth) ===');
        $catQ = $feeRepo->feeCategoriesQuery();
        $cats = $catQ->get();
        $this->info('Count: ' . $cats->count());

        $this->info('=== Categories DataTables JSON ===');
        $json = DataTables::of(clone $catQ)
            ->addColumn('actions', fn (FeeCategory $row) => '<button>Edit</button>')
            ->rawColumns(['actions'])
            ->toJson();
        $decoded = json_decode($json->content(), true);
        $this->info('recordsTotal: ' . ($decoded['recordsTotal'] ?? 'MISSING'));
        $this->info('data count: ' . count($decoded['data'] ?? []));
        if (!empty($decoded['data'])) {
            $this->info('First row keys: ' . json_encode(array_keys($decoded['data'][0])));
        } else {
            $this->info('data is empty! Full response: ' . $json->content());
        }

        $this->info('=== Notifications (no auth) ===');
        $notifQ = $notifRepo->dataTableQuery();
        $notifs = $notifQ->get();
        $this->info('Count: ' . $notifs->count());

        $json2 = DataTables::of(clone $notifQ)
            ->addColumn('type_label', fn ($n) => $n->type_label)
            ->addColumn('user_count', fn ($n) => $n->user_count ?? 0)
            ->addColumn('unread_count', fn ($n) => $n->unread_count ?? 0)
            ->addColumn('actions', fn ($n) => '<button>View</button>')
            ->rawColumns(['actions'])
            ->toJson();
        $decoded2 = json_decode($json2->content(), true);
        $this->info('recordsTotal: ' . ($decoded2['recordsTotal'] ?? 'MISSING'));
        $this->info('data count: ' . count($decoded2['data'] ?? []));
        if (!empty($decoded2['data'])) {
            $this->info('First row keys: ' . json_encode(array_keys($decoded2['data'][0])));
        } else {
            $this->info('data is empty!');
        }

        return 0;
    }
}
