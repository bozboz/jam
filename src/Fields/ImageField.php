<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\MediaBrowser;
use Bozboz\Admin\Media\Media;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Revision;
use Bozboz\Entities\Entities\Value;

class ImageField extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
		return new MediaBrowser($this->getValue($value), [
			'name' => $this->name
		]);
	}

	public function injectValue(Entity $entity, Revision $revision, $realValue)
	{
		$value = parent::injectValue($entity, $revision, $realValue);

		if (!$realValue) {
			$entity->setAttribute($value->key, $this->getValue($value)->first());
		}
	}

	public function getvalue(Value $value)
	{
		return Media::forModel($value, 'value');
	}
}
