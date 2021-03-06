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
use Embed\Embed;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Netcarver\Textile\Parser;

class Oembed extends Text
{
    public function getValue(Value $value)
    {
        return $value->value ? Cache::rememberForever($this->getCacheKey($value), function() use ($value) {
            return Embed::create($value->value);
        }) : null;
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
