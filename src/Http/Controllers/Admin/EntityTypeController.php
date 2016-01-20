<?php

namespace Bozboz\Entities\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Entities\Types\TypeDecorator;
use Bozboz\Entities\Types\TypeReport;

class EntityTypeController extends ModelAdminController
{
	public function __construct(TypeDecorator $decorator)
	{
		parent::__construct($decorator);
	}

	/**
	 * Get an instance of a report to display the model listing
	 *
	 * @return Bozboz\Admin\Reports\Report
	 */
	protected function getListingReport()
	{
		return new TypeReport($this->decorator);
	}
}
