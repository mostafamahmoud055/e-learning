<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeleteUserUnverified extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-user-unverified';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete user unverified every day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        User::where('email_verified_at', null)->delete();
    }
}
