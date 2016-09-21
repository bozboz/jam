<?php

namespace Bozboz\Jam\Console\Commands;

use Bozboz\Jam\Entities\Entity;
use Illuminate\Console\Command;

class CountErrors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jam:counterrors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the countErrors command over the entity nest';

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
        $this->info(var_export(Entity::withTrashed()->countErrors(), true));
    }
}
