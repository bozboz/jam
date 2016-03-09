<?php

namespace Bozboz\Entities\Entities\Fields;

use Bozboz\Admin\Fields\DateTimeField;
use Bozboz\Admin\Fields\Field;
use Bozboz\Admin\Fields\FieldGroup;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Entities\Entities\Revision;

class PublishField extends SelectField
{
	protected $dateInput;

	function __construct($attributesOrName, $attributes = [])
	{
		parent::__construct($attributesOrName, $attributes);

		$this->options = $this->getPublishingOptions();
		$this->class = $this->class . ' js-publish-dropdown';

		$this->dateInput = new DateTimeField('published_at');
		$this->dateInput->class = $this->dateInput->class . ' js-publish-date';
	}

	public function getInput()
	{
		return parent::getInput() . $this->getScheduleInput();
	}

	public function getScheduleInput()
	{
		return $this->dateInput->getInput();
	}

	public function getJavascript()
	{
		return $this->dateInput->getJavascript() . <<<JAVASCRIPT
			// $(function() {
			// 	$('js-publish-dropdown').change
			// });
JAVASCRIPT;
	}

	protected function getPublishingOptions()
	{
		return [
			Revision::UNPUBLISHED => 'Unpublished',
			Revision::PUBLISHED => 'Published',
			Revision::SCHEDULED => 'Schedule',
		];
	}
}