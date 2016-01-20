<?php

namespace Bozboz\Entities\Templates;

use Bozboz\Admin\Reports\Actions\LinkAction;

class TemplateFieldsAction extends LinkAction
{
	public function getUrl($row)
	{
		return action($this->action, ['template_id' => $row->getid()]);
	}
}