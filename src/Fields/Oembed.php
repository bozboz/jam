<?php

/**
 * Requires embed/embed (https://github.com/oscarotero/Embed)
 */

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\TextField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;
use Illuminate\Support\Facades\Cache;
use Netcarver\Textile\Parser;
use Embed\Embed;

class Oembed extends Text
{
    public function getValue(Value $value)
    {
        return Cache::rememberForever($this->getCacheKey($value), function() use ($value) {
            return Embed::create($value->value);
        });
    }

    protected function getCacheKey($value)
    {
        return 'jam:oembed-' . $value->id;
    }

    static public function getDescriptiveName()
    {
        return 'Media embed (via embed/embed)';
    }
}