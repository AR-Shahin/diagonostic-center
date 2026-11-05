<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BipCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bip_command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //  $this->info('Bip command executed successfully!');

        // You can add more functionality here as needed
        // For example, you could
        //  dd(3);

        Log::info('Bip command executed successfully!');
        $this->info('Bip command executed successfully!');
    }
}
