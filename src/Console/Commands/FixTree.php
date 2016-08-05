<?php

namespace Bozboz\Jam\Console\Commands;

use Bozboz\Jam\Entities\Entity;
use Illuminate\Console\Command;

class FixTree extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jam:fixtree';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the fixtree command over the entity nest';

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
        $this->info('Completed form ' . Entity::withTrashed()->fixTree() . ' entities');
    }
}
