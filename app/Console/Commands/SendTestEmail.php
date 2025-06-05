<?php

namespace App\Console\Commands;

use App\Mail\TestEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {to : The recipient email} {--name=Client : The recipient name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email using a markdown Blade template';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $to = $this->argument('to');
        $name = $this->option('name');

        Mail::to($to)->send(new TestEmail($name));

        $this->info("✅ Test email sent to {$to}.");
    }
}
