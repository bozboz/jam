<?php

namespace Bozboz\Entities\Fields;

interface FieldInterface
{
	public function injectValue($entity, $revision, $valueKey);
	public function getInputName();
	public function saveValue($revision, $value);
}