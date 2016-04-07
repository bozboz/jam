<?php
namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Reports\Report;

class RevisionReport extends Report
{
    public function __construct(RevisionDecorator $decorator, $entityId)
    {
        $this->decorator = $decorator;
        $this->rows = $this->decorator->getListingForEntity($entityId);
    }
}
