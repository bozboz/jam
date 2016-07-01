<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\MediaBrowser;
use Bozboz\Admin\Media\Media;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;

class Image extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
		return new MediaBrowser($this->relation($value), [
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel()
		]);
	}

	public function relation(Value $value)
	{
		return Media::forModel($value, 'foreign_key');
	}

	public function getvalue(Value $value)
	{
		return $value->{$value->key};
	}

    protected function usesForeignKey()
    {
        return true;
    }
}
