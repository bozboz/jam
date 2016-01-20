<?php

namespace Bozboz\Entities\Types;

use Bozboz\Admin\Reports\LinkAction;
use Bozboz\Admin\Reports\Report;
use Bozboz\Entities\Http\Controllers\Admin\EntityTemplateController;

class TypeReport extends Report
{
	public function getRowActions($params)
	{
		return array_merge([
			new TypeTemplatesAction([
				'permission' => $params['canEdit'],
				'action' => '\\'.EntityTemplateController::class.'@index',
				'label' => 'Templates',
				'icon' => 'fa fa-file-o',
				'class' => 'btn-default'
			])
		], parent::getRowActions($params));
	}
}
