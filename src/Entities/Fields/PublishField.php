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
		$publishedValue = Revision::PUBLISHED;
		return $this->dateInput->getJavascript() . <<<JAVASCRIPT
			$(function() {
				var publishDropdown = $('.js-publish-dropdown');

				function toggleDateInput(select, first) {
					var date = select.next();
					date.find('label').html('Scheduled Date');
					if (!first) {
						date.find('.js-datetimepicker').datetimepicker('setDate',  null);
					}
					switch (select.val()) {
						case '{$publishedValue}':
							date.find('label').html('Published Date');
						case '{$scheduleValue}':
							date.removeClass('hidden');
						break;

						default:
							date.addClass('hidden');
					}
				}

				publishDropdown.change(function() {
					toggleDateInput($(this));
				});

				toggleDateInput(publishDropdown, true);
			});
JAVASCRIPT;
	}

	protected function getPublishingOptions()
	{
		return [
			'' => 'Hidden',
			Revision::PUBLISHED => 'Published',
			Revision::SCHEDULED => 'Scheduled',
		];
	}
}