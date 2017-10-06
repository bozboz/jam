<?php

namespace Bozboz\Jam\Entities\Listeners;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Bozboz\Jam\Entities\Jobs\UpdatePaths as Job;

class UpdatePaths
{
    use DispatchesJobs;

    public function handle($event)
    {
        $this->dispatch(new Job($event->entity));
    }
}
