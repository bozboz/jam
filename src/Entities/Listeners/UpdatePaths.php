<?php

namespace Bozboz\Jam\Entities\Listeners;

class UpdatePaths
{
    public function handle($event)
    {
        $event->entity->template->type()->updatePaths($event->entity);
    }
}