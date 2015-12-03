<?php

namespace Bozboz\Entities\Fields;

interface FieldMapperInterface
{
	public function has($alias);

	public function get($alias);
}