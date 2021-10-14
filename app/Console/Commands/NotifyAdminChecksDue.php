<?php

namespace App\Console\Commands;

use App\Models\Check;
use App\Notifications\CheckDue;
use Illuminate\Console\Command;

class NotifyAdminChecksDue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '8finity:notify_check_due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify admin of the checks that are due';

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
        Check::whereDate('due_date', '=', now())
            ->each(function ($check) {
                $check->admin->notify(new CheckDue($check));
            });
    }
}
