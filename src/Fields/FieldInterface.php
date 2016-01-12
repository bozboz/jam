<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Revision;
use Bozboz\Entities\Entities\Value;

interface FieldInterface
{
	public function getAdminField(EntityDecorator $decorator, Value $value);
	public function injectValue(Entity $entity, Revision $revision);
	public function getInputName();
	public function saveValue(Revision $revision, $value);
	public function getValue(Value $value);
}