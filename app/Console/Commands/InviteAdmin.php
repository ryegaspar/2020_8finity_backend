<?php

namespace App\Console\Commands;

use App\Facades\InvitationCode;
use App\Models\Invitation;
use Illuminate\Console\Command;

class InviteAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '8finity:invite-admin {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invite an admin';

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
        $email = $this->argument('email');
        $invitation = Invitation::where('email', $email)->first();

        if ($invitation) {
            if ($invitation->hasBeenUsed()) {
                $this->error('that email is already a registered admin');

                return;
            }

            $invitation->send();

            return;
        }

        Invitation::create([
            'code'  => InvitationCode::generate(),
            'email' => $email
        ])->send();
    }
}
