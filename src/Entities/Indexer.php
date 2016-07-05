<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Jam\Entities\Contracts\Indexable;
use Bozboz\Jam\Entities\IndexableEntity;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Illuminate\Contracts\Container\Container;

class Indexer implements Indexable
{
    private $search;

    public function __construct(Container $container)
    {
        $this->search = $container->make('searchIndex');
    }

    public function index($entity)
    {
        $indexableEntity = new IndexableEntity;
        $indexableEntity->make($entity);

        $indexableEntity->preview_data = $this->getPreviewData($indexableEntity);
        $indexableEntity->searchable_data = $this->getSearchableData($indexableEntity);

        if ($entity->exists) {
            $this->upsertIndex($indexableEntity);
        } else {
            $this->deleteIndex($indexableEntity);
        }
    }

    protected function getPreviewData($indexableEntity)
    {
        return [];
    }

    protected function getSearchableData($indexableEntity)
    {
        return '';
    }

    protected function upsertIndex($entity)
    {
        if ($entity->shouldIndex()) {
            $this->search->upsertToIndex($entity);
        } else {
            $this->deleteIndex($entity);
        }
    }

    protected function deleteIndex($entity)
    {
        try {
            $this->search->removeFromIndex($entity);
        } catch (Missing404Exception $e) {
            // Ignore 404 errors in Elastic Search
        }
    }
}