<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;

abstract class SortedBelongsToMany extends BelongsToMany
{
    protected function getAdminFieldAttributes()
    {
        return parent::getAdminFieldAttributes() + [
            'data-sortable' => 1,
        ];
    }

    protected function filterAdminQuery($query, $value) {
        parent::filterAdminQuery($query, $value);

        $pivot = $this->getPivot();
        $query->leftJoin($pivot->table . ' as pivot_sorting', function($join) use ($value, $pivot) {
            $join->on('pivot_sorting.' . $pivot->other_key, '=', $this->getRelationTable() . '.id');
            $join->where('pivot_sorting.' . $pivot->foreign_key, '=', $value->id);
        });

        $query->orderBy('pivot_sorting.sorting');
    }

    public function relation(Value $value)
    {
        return parent::relation($value)
            ->withPivot('sorting')->orderBy($this->getPivot()->table . '.sorting');
    }

    public function saveValue(Revision $revision, $value)
    {
        $valueObj = $this->parentSaveValue($revision, json_encode($value));
        $syncData = [];

        if (is_array($value)) {
            foreach($value as $i => $entityId) {
                $syncData[$entityId] = [
                    'sorting' => $i
                ];
            }
        }

        $this->relation($valueObj)->sync($syncData);

        return $valueObj;
    }

    public function duplicateValue(Value $oldValue, Value $newValue)
    {
        $syncData = $this->relation($oldValue)->withPivot('sorting')->pluck($this->getPivot()->other_key, 'sorting')->toArray();
        $this->relation($newValue)->sync($syncData);
    }
}
