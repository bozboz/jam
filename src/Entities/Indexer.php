<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Jam\Entities\IndexableEntity;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Illuminate\Contracts\Container\Container;

class Indexer
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

        $indexableEntity->name = $this->getName($entity);
        $indexableEntity->path = $this->getPath($entity);
        $indexableEntity->preview_data = $this->getPreviewData($indexableEntity);
        $indexableEntity->searchable_data = $this->getSearchableData($indexableEntity);

        if ($indexableEntity->shouldIndex()) {
            $this->upsertIndex($indexableEntity);
        } else {
            $this->deleteIndex($indexableEntity);
        }
    }

    protected function getName($entity)
    {
        return $entity->name;
    }

    protected function getPath($entity)
    {
        return $entity->canonical_path;
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
        $this->search->upsertToIndex($entity);
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
