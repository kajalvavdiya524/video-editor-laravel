<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mail;

class HourlyUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hour:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an hourly email to all the users';

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
        Mail::raw("This is automatically generated Hourly Update", function($message)
        {
            $message->from('bayclimber@gmail.com');
            $message->to('cesar.rdz.work@gmail.com')->subject('Hourly Update');
        });
        $this->info('Hourly Update has been send successfully');
    }
}
