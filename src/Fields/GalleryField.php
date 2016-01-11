<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\MediaBrowser;
use Bozboz\Entities\Entities\Value;

class GalleryField extends Field implements FieldInterface
{
	public function getAdminField(Value $value)
	{
		return new MediaBrowser($value->gallery(), [
			'name' => $this->getInputName(),
			'label' => preg_replace('/([A-Z])/', ' $1', studly_case($this->name))
		]);
	}

	public function injectValue($entity, $revision, $valueKey)
	{
		$value = parent::injectValue($entity, $revision, $valueKey);
		$entity->setAttribute($this->getInputName(), $value->gallery()->getRelatedIds()->all());
	}

	public function getInputName()
	{
		return e($this->name).'_relationship';
	}

	public function saveValue($revision, $value)
	{
		$valueObj = parent::saveValue($revision, $value);

		$data = @array_filter($value);
		$valueObj->gallery()->sync(is_array($data) ? $data : []);
	}
}
