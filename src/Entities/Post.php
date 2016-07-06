<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Jam\Entities\Entity;

class Post extends Entity
{
    public function sortBy()
    {
        return 'currentRevision.published_at';
    }

    public function scopeOrdered($query)
    {
        $this->scopeOrderByPublishedAt($query);
    }
}