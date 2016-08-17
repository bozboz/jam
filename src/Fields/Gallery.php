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
			'label' => $this->getInputLabel(),
            'help_text_title' => $this->help_text_title,
            'help_text' => $this->help_text,
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

		$syncData = [];

		foreach($data as $i => $id) {
			$syncData[$id] = [
				'sorting' => $i,
			];
		}

		$this->relation($valueObj)->sync($syncData);
	}

	public function relation(Value $value)
	{
		return Media::forModel($value);
	}
}
