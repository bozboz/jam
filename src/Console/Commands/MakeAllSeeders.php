<?php

namespace Bozboz\Jam\Console\Commands;

use Bozboz\Jam\Templates\Template;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MakeAllSeeders extends Command
{
    protected $signature = 'jam:generate-seeders';

    protected $description = 'Generate seeders for all templates';

    public function handle()
    {
        if ($this->confirm('Clear old Jam seeders?')) {
            array_map('unlink', glob(database_path('seeds/*JamTemplate.php')));
        }
        $bar = $this->output->createProgressBar(Template::count());
        Template::all()->map(function ($template) use ($bar) {
            $this->comment($template->name);
            $this->call('jam:make-seeder', ['type' => $template->type_alias, 'template' => $template->alias]);
            $bar->advance();
        });
        $bar->finish();
    }
}
