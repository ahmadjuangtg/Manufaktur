<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

class PruneActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activitylog:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pembersihan log aktivitas yang berumur lebih dari 5 hari untuk efisiensi database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pembersihan log aktivitas...');
        
        $deleted = ActivityLog::where('created_at', '<', now()->subDays(5))->delete();
        
        $this->info("Pembersihan selesai! {$deleted} log aktivitas berhasil dihapus.");
    }
}
