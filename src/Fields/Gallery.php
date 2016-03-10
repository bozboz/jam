<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\MediaBrowser;
use Bozboz\Admin\Media\Media;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;

class Gallery extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
		return new MediaBrowser($this->getValue($value), [
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel()
		]);
	}

	public function injectValue(Entity $entity, Revision $revision, $realValue)
	{
		$value = parent::injectValue($entity, $revision, $realValue);
		$entity->setAttribute($this->getInputName(), $this->getValue($value)->getRelatedIds()->all());

		if (!$realValue) {
			$entity->setAttribute($value->key, $this->getValue($value)->get());
		}
	}

	public function getInputName()
	{
		return e($this->name).'_relationship';
	}

	public function saveValue(Revision $revision, $value)
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
