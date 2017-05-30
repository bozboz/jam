<?php

namespace Bozboz\Jam\Templates;

use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Reports\Filters\DateFilter;
use Bozboz\Admin\Reports\Filters\RelationFilter;
use Bozboz\Jam\Templates\TemplateDecorator;
use Illuminate\Database\Eloquent\Builder;

class TemplateRevisionDecorator extends ModelAdminDecorator
{
    private $templates;

    public function __construct(TemplateRevision $model, TemplateDecorator $templates)
    {
        parent::__construct($model);
        $this->templates = $templates;
    }

    public function getLabel($instance)
    {
        return $instance->revisionable_name;
    }

    public function getColumns($instance)
    {
        return [
            'Template' => "<strong>{$instance->template_name}</strong>",
            'Type' => $instance->revisionable_type,
            'Name' => $this->getLabel($instance),
            'Action' => $instance->action,
            'Changes' => $this->getChanges($instance),
            'User' => $instance->user_name,
            'Date' => $instance->created_at,
        ];
    }

    protected function getChanges($instance)
    {
        $old = $instance->decoded_old ? collect($instance->decoded_old) : null;
        $new = $instance->decoded_new ? collect($instance->decoded_new) : null;
        return '<a href="#" onClick="$(this).next().toggle()">View</a><ul style="display:none">'
            . collect(array_keys(array_merge($instance->getDecodedOldAttribute(true), $instance->getDecodedNewAttribute(true))))
            ->map(function($attribute) use ($old, $new) {
                if ($old && $new) {
                    $oldValue = ! is_null($old->get($attribute)) ? $old->get($attribute) : 'NULL';
                    $newValue = ! is_null($new->get($attribute)) ? $new->get($attribute) : 'NULL';
                    return "<li><strong>{$attribute}:</strong> {$oldValue} - {$newValue}</li>";
                } elseif ($old) {
                    $oldValue = ! is_null($old->get($attribute)) ? $old->get($attribute) : 'NULL';
                    return "<li><strong>{$attribute}:</strong> {$oldValue}</li>";
                } elseif ($new) {
                    $newValue = ! is_null($new->get($attribute)) ? $new->get($attribute) : 'NULL';
                    return "<li><strong>{$attribute}:</strong> {$newValue}</li>";
                }
            })->implode('') . '</ul>';
    }

    public function getFields($instance)
    {
        return [];
    }

    public function modifyListingQuery(Builder $query)
    {
        parent::modifyListingQuery($query);
        $query->with('revisionable', 'user', 'template');
    }

    public function getListingFilters()
    {
        return [
            new RelationFilter($this->model->template(), $this->templates),
            new DateFilter('created_at'),
        ];
    }
}