<?php

namespace Bozboz\Jam\Entities\Jobs;

use Illuminate\Bus\Queueable;
use Bozboz\Jam\Entities\Entity;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Database\ModelIdentifier;

class UpdatePaths implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    private $entity;

    function __construct(Entity $entity)
    {
        $this->entity = $entity;
    }

    public function handle()
    {
        $this->entity->template->type()->updatePaths($this->entity);
    }

    protected function getRestoredPropertyValue($value)
    {
        if (! $value instanceof ModelIdentifier) {
            return $value;
        }

        return is_array($value->id)
                ? $this->restoreCollection($value)
                : (new $value->class)->newQuery()->withTrashed()->useWritePdo()->findOrFail($value->id);
    }}
