<?php

namespace Bozboz\Entities\Contracts;

use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Revision;
use Bozboz\Entities\Entities\Value;

interface Field
{
	public function getAdminField(EntityDecorator $decorator, Value $value);
	public function injectValue(Entity $entity, Revision $revision, $realValue);
	public function getInputName();
	public function saveValue(Revision $revision, $value);
	public function getValue(Value $value);
}