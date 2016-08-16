<?php

namespace Bozboz\Jam\Entities\Contracts;

use Spatie\SearchIndex\Searchable;

interface Indexable extends Searchable
{
    /**
     * Flag for if current instance should be indexed (e.g. depending on the
     * status of the model)
     *
     * @return boolean
     */
    public function shouldIndex();
}
