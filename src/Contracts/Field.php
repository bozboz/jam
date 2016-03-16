<?php

namespace Bozboz\Jam\Contracts;

use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;

interface Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value);
	public function injectValue(Entity $entity, Value $value);
	public function injectAdminValue(Entity $entity, Revision $revision);
	public function getInputName();
	public function saveValue(Revision $revision, $value);
	public function getValue(Value $value);
}