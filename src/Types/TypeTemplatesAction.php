<?php

namespace Bozboz\Entities\Types;

use Bozboz\Admin\Reports\Actions\LinkAction;

class TypeTemplatesAction extends LinkAction
{
	public function getUrl($row)
	{
		return action($this->action, ['type_id' => $row->getid()]);
	}
}