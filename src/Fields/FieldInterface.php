<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\Revision;
use Bozboz\Entities\Entities\Value;

interface FieldInterface
{
	public function getAdminField(Value $value);
	public function injectValue(Entity $entity, Revision $revision, $valueKey);
	public function getInputName();
	public function saveValue(Revision $revision, Value $value);
}