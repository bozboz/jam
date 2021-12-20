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
            return $this->youtube($value->value) ?: Embed::create($value->value);
        }) : null;
    }

    private function youtube($url)
    {
        preg_match('/^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$/', $url, $matches);
        if (! $videoId = Arr::get($matches, 5)) {
            return false;
        }

        $embed = Embed::create($url);

        $code = <<<HTML
        <iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/{$videoId}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
HTML;

        return (object)[
            'code' => $code,
            'image' => $embed->image,
        ];

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
