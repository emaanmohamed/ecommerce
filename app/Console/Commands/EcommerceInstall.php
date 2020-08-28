<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class EcommerceInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ecommerce:install {--force : Do not ask for user information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install dummy data';

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
     * @return mixed
     */
    public function handle()
    {

        if ($this->confirm('This will delete all your current data and install the default dummy data. Are you sure?'))
        File::deleteDirectory('storage/products/dummy');
        $this->callSilent('storage:link');
        $copySuccess = File::copyDirectory(public_path('img/products'), public_path('storage/products/dummy'));
        if ($copySuccess) {
            $this->info('Image successfully copied to storage folder.');
        }
        $this->call('migrate:fresh', [
           '--seed' => true,
        ]);

        $this->call('db:seed', [
           '--class' => 'VoyagerDatabaseSeeder',
        ]);

        $this->call('db:seed', [
            '--class' => 'VoyagerDummyDatabaseSeeder',
        ]);

        $this->call('db:seed', [
            '--class' => 'MenusTableSeederCustom',
        ]);

        $this->info('Dummy data installed.');
    }
}
