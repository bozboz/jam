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
		return new MediaBrowser($this->relation($value), [
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel()
		]);
	}

	public function injectValue(Entity $entity, Value $value)
	{
		$entity->setAttribute($value->key, $this->getValue($value));
	}

	public function injectAdminValue(Entity $entity, Revision $revision)
	{
		$value = parent::injectAdminValue($entity, $revision);
		$entity->setAttribute($this->getInputName(), $this->relation($value)->getRelatedIds()->all());
	}

	public function getInputName()
	{
		return e($this->name).'_relationship';
	}

	public function saveValue(Revision $revision, $value)
	{
		$valueObj = parent::saveValue($revision, $value);

		$data = array_filter(is_array($value) ? $value : []);
		$this->relation($valueObj)->sync($data);
	}

	public function relation(Value $value)
	{
		return Media::forModel($value);
	}

	/**
	 * @todo come up with a better solution for this
	 */
	public function getValue(Value $value)
	{
		return $value->{$value->key};
	}
}
