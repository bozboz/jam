<?php

namespace Bozboz\Jam\Console\Commands;

use Bozboz\Jam\Repositories\Contracts\EntityRepository;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\Container;

class RecalculatePaths extends Command
{
    private $repo;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jam:recalculate-paths
                            {type : The entity type to recalculate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate paths for an entity type';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(EntityRepository $repo)
    {
        parent::__construct();
        $this->repo = $repo;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $type = $this->argument('type');

        $entities = $this->repo->forType($type)->with('template')->get();

        $type = $entities->first()->template->type();

        $bar = $this->output->createProgressBar($entities->count());

        $entities->each(function($entity) use ($bar, $type) {
            $type->updatePaths($entity);

            $bar->advance();
        });

        $bar->finish();
    }
}
