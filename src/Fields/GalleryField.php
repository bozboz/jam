<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\MediaBrowser;
use Bozboz\Admin\Media\Media;
use Bozboz\Entities\Entities\Value;

class GalleryField extends Field implements FieldInterface
{
	public function getAdminField(Value $value)
	{
		return new MediaBrowser($this->getValue($value), [
			'name' => $this->getInputName(),
			'label' => preg_replace('/([A-Z])/', ' $1', studly_case($this->name))
		]);
	}

	public function injectValue($entity, $revision, $valueKey)
	{
		$value = parent::injectValue($entity, $revision, $valueKey);
		$entity->setAttribute($this->getInputName(), $this->getValue($value)->getRelatedIds()->all());
	}

	public function getInputName()
	{
		return e($this->name).'_relationship';
	}

	public function saveValue($revision, $value)
	{
		$valueObj = parent::saveValue($revision, $value);

		$data = @array_filter($value);
		$this->getValue($valueObj)->sync(is_array($data) ? $data : []);
	}

	public function getValue(Value $value)
	{
		return Media::forModel($value);
	}
}
