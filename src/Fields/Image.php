<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\MediaBrowser;
use Bozboz\Admin\Media\Media;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;

class Image extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
		return new MediaBrowser($this->getValue($value), [
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel()
		]);
	}

	public function injectValue(Entity $entity, Value $value)
	{
		$value = parent::injectValue($entity, $value);
		$entity->setAttribute($value->key, $this->getValue($value)->first());
	}

	public function getvalue(Value $value)
	{
		return Media::forModel($value, 'value');
	}
}
