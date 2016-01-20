<?php

namespace Bozboz\Entities\Templates;

use Bozboz\Admin\Reports\LinkAction;
use Bozboz\Admin\Reports\NestedReport;
use Bozboz\Entities\Http\Controllers\Admin\EntityTemplateFieldController;
use Bozboz\Entities\Templates\TemplateFieldsAction;

class TemplateReport extends NestedReport
{
	public function getRowActions($params)
	{
		return array_merge([
			new TemplateFieldsAction([
				'permission' => $params['canEdit'],
				'action' => '\\'.EntityTemplateFieldController::class.'@index',
				'label' => 'Fields',
				'icon' => 'fa fa-file-o',
				'class' => 'btn-default'
			])
		], parent::getRowActions($params));
	}
}
