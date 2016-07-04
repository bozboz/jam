<?php

namespace Bozboz\Jam\Entities\Listeners;

use Bozboz\Jam\Entities\Events\EntitySaved;

class UpdatePaths
{
    public function handle(EntitySaved $event)
    {
        $event->entity->template->type()->updatePaths($event->entity);
    }
}