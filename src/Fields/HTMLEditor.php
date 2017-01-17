<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\HTMLEditorField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Value;

class HTMLEditor extends Textarea
{
	public function getOption($key)
    {
        if ($key == 'wysiwyg') {
            return 1;
        }
        return parent::getOption($key);
    }

    public function getOptionFields()
    {
        return [];
    }
}