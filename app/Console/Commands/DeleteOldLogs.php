<?php

namespace App\Console\Commands;

use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '8finity:delete-old-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete old logs';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $logs = Log::whereDate('created_at', '<=', Carbon::now()->subDays(config('app.log_delete_after')));

        $logs->delete();
    }
}
