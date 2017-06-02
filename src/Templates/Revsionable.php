<?php

namespace Bozboz\Jam\Templates;

use Bozboz\Jam\Templates\TemplateRevision;
use Illuminate\Support\Facades\Auth;

trait Revisionable
{
    public static function bootRevisionable()
    {
        static::created(function($item) {
            $item->logRevision('created');
        });
        static::updated(function($item) {
            $item->logRevision('updated');
        });
        static::deleted(function($item) {
            $item->logRevision('deleted');
        });
    }

    public function logRevision($action)
    {
        $this->revisions()->create([
            'action' => $action,
            'old' => json_encode(array_intersect_key($this->getOriginal(), $this->getDirty())),
            'new' => json_encode($this->getDirty()),
            'user_id' => Auth::user()->id,
        ]);
    }

    public function revisions()
    {
        return $this->morphMany(TemplateRevision::class);
    }
}
