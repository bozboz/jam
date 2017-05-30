<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Jam\Templates\TemplateRevisionDecorator;

class TemplateHistoryController extends ModelAdminController
{
    protected $useActions = true;

    function __construct(TemplateRevisionDecorator $decorator)
    {
        parent::__construct($decorator);
    }

    public function getReportActions()
    {
        return [];
    }

    public function getRowActions()
    {
        return [];
    }
}