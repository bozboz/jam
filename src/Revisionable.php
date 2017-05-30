<?php

namespace Bozboz\Jam;

use Bozboz\Jam\Templates\TemplateRevision;
use Illuminate\Support\Facades\Auth;

trait Revisionable
{
    public static function bootRevisionable()
    {
        static::created(function($item) {
            $item->logCreate();
        });
        static::updated(function($item) {
            $item->logUpdate();
        });
        static::deleted(function($item) {
            $item->logDelete();
        });
    }

    protected function onlyFillable($attributes)
    {
        return array_intersect_key($attributes, array_combine($this->getFillable(), $this->getFillable()));
    }

    public function logCreate()
    {
        $this->logRevision('created', [], $this->onlyFillable($this->toArray()));
    }

    public function logUpdate()
    {
        if ($this->wasRecentlyCreated) {
            return;
        }

        $this->logRevision('updated',
            $this->onlyFillable(array_intersect_key($this->getOriginal(), $this->getDirty())),
            $this->onlyFillable($this->getDirty())
        );
    }

    public function logDelete()
    {
        $this->logRevision(
            'deleted',
            $this->onlyFillable($this->toArray()),
            []
        );
    }

    public function logRevision($action, $old, $new)
    {
        $this->revisions()->create([
            'action' => $action,
            'old' => $old,
            'new' => $new,
            'user_id' => Auth::user()->id,
            'template_id' => $this->getTemplateForRevision()->id,
        ]);
    }

    protected function getTemplateForRevision()
    {
        return $this->template;
    }

    public function revisions()
    {
        return $this->morphMany(TemplateRevision::class, 'revisionable');
    }
}
