<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Models\Base;
use Bozboz\Entities\Entity;
use Bozboz\Entities\Fields\Field;
use Bozboz\Entities\Fields\FieldMapper;
use Bozboz\Entities\Templates\Template;

class Field extends Base
{
	protected $table = 'entity_template_fields';

	protected $fillable = [
		'name',
		'validation',
		'template_id',
		'type_alias'
	];

	public function getValidator()
	{
		return new FieldValidator;
	}

	public function template()
	{
		return $this->belongsTo(Template::class);
	}
}
