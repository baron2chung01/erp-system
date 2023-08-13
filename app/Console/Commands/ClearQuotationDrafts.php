<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearQuotationDrafts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear-quotation-drafts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $directory = public_path('quotation-draft');
        if (file_exists($directory)) {
            $this->info('Directory found.');
            $files = glob("$directory/*");
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        } else {
            $this->info('Directory not found.');
        }
        $this->info('Quotation drafts cleared successfully!');
    }
}
