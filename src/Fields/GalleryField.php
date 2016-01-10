<?php

namespace Bozboz\Entities\Fields;

class GalleryField extends Field implements FieldInterface
{
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
