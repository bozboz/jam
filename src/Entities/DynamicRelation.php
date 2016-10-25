<?php

namespace Bozboz\Jam\Entities;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\Relation;

class DynamicRelation extends Relation
{
    protected $relations = [];

    public function __construct()
    {

    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        foreach($models as $model) {
            $relation = $model->newQuery()->getRelation('relation');
            if ($relation) {
                $this->relations[$model->key] = $relation;
                $this->modelsPerRelation[$model->key][] = $model;
            }
        }

        foreach($this->relations as $key => $relation) {
            $relation->addEagerConstraints($this->modelsPerRelation[$key]);
        }
    }

    /**
     * Initialize the relation for the compiled set of relations
     *
     * @param  array   $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach($this->relations as $key => $relation) {
            $relation->initRelation($this->modelsPerRelation[$key], $key);
        }

        return $models;
    }

    /**
     * Get results for each relationship for eager loading.
     *
     * @return \Bozboz\Jam\Entities\Collection
     */
    public function getEager()
    {
        return Collection::make($this->relations)->map(function($relation) {
            return $relation->getEager();
        });
    }

    /**
     * For each relation, match the eagerly loaded results to their parents.
     *
     * @param  array   $models
     * @param  \Bozboz\Jam\Entities\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, EloquentCollection $results, $relation)
    {
        foreach($this->relations as $key => $relation) {
            $models = $relation->match($models, $results[$key], $key);
        }

        return $models;
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @throws \RuntimeException
     */
    public function addConstraints()
    {
        throw new \RuntimeException(__CLASS__ . ' can only be used for eager loading');
    }

    /**
     * Get the results of the relationship.
     *
     * @throws \RuntimeException
     */
    public function getResults()
    {
        throw new \RuntimeException(__CLASS__ . ' can only be used for eager loading');
    }
}
