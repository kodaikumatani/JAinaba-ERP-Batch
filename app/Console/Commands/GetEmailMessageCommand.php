<?php

namespace App\Console\Commands;

use App\Service\ManageMailboxes;
use Illuminate\Console\Command;

class GetEmailMessageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gmail:read';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Read a message in UNREAD box';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        dump(ManageMailboxes::getMessage());
        //return Command::SUCCESS;
    }
}