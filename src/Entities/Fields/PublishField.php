<?php

namespace Bozboz\Jam\Entities\Fields;

use Bozboz\Admin\Fields\DateTimeField;
use Bozboz\Admin\Fields\Field;
use Bozboz\Admin\Fields\FieldGroup;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Jam\Entities\Revision;

class PublishField extends SelectField
{
	protected $dateInput, $instance;

	function __construct($attributesOrName, $instance, $attributes = [])
	{
		parent::__construct($attributesOrName, $attributes);

		$this->options = $this->getPublishingOptions();
		$this->class = $this->class . ' js-publish-dropdown';

		$this->instance = $instance;

		$this->dateInput = new DateTimeField('currentRevision[published_at]');
	}

	public function getInput()
	{
		return parent::getInput() . $this->getScheduleInput();
	}

	public function getScheduleInput()
	{
		return '<div class="hidden" style="margin-top:20px;"><label for="published_at">Scheduled Date</label>'.$this->dateInput->getInput().'</div>';
	}

	public function getJavascript()
	{
		$scheduleValue = Revision::SCHEDULED;
		$scheduledDate = $this->instance->currentRevision->published_at;
		return $this->dateInput->getJavascript() . <<<JAVASCRIPT
			$(function() {
				var publishDropdown = $('.js-publish-dropdown');

				function toggleDateInput(select) {
					if (select.val() == {$scheduleValue}) {
						select.next().removeClass('hidden');
					} else {
						select.next().addClass('hidden');
					}
				}

				publishDropdown.change(function() {
					toggleDateInput($(this));
				});

				toggleDateInput(publishDropdown);
			});
JAVASCRIPT;
	}

	protected function getPublishingOptions()
	{
		return [
			Revision::UNPUBLISHED => 'Hidden',
			Revision::PUBLISHED => 'Published',
			Revision::SCHEDULED => 'Scheduled',
		];
	}
}