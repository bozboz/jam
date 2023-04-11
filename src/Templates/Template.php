<?php

namespace Bozboz\Jam\Templates;

use Bozboz\Admin\Base\DynamicSlugTrait;
use Bozboz\Admin\Base\Model;
use Bozboz\Admin\Base\ModelInterface;
use Bozboz\Admin\Base\SanitisesInputTrait;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Fields\Field;
use Bozboz\Jam\Fields\Text;
use Bozboz\Jam\Revisionable;
use Bozboz\Jam\Types\Type;

class Template extends Model
{
	use DynamicSlugTrait;
	use Revisionable;

	protected $table = 'entity_templates';

	protected $fillable = [
		'name',
		'view',
		'listing_view',
		'alias',
		'type_alias',
		'max_uses',
		'tabs',
	];

	protected $nullable = [
		'view',
		'listing_view',
		'max_uses',
		'tabs',
	];

	static public function boot()
	{
		parent::boot();
		static::created(function($template) {
			if ($template->type()->isVisible()) {
				$template->fields()->create([
					'type_alias' => 'text',
					'name' => 'meta_title',
					'validation' => 'required',
					'help_text' => 'We suggest a <a href="https://moz.com/learn/seo/title-tag">max of 55 chars</a>',
				]);
				$template->fields()->create([
					'type_alias' => 'text',
					'name' => 'meta_description',
					'help_text' => 'We suggest a <a href="https://moz.com/learn/seo/meta-description">max of 160 chars</a>',
				]);
			}
		});
	}

	protected function getSlugSourceField()
	{
		return 'name';
	}

	protected function generateUniqueSlug($slug)
	{
		return $slug;
	}

	/**
	 * Attribute to store the slug in slug.
	 *
	 * @return string
	 */
	protected function getSlugField()
	{
		return 'alias';
	}

	public function sortBy()
	{
		return 'name';
	}

	public function getValidator()
	{
		return new TemplateValidator;
	}

	public function fields()
	{
		return $this->hasMany(Field::class);
	}

	public function entities()
	{
		return $this->hasMany(Entity::class);
	}

	public function type()
	{
		return Entity::getMapper()->get($this->type_alias);
	}

	public function getTemplateForRevision()
	{
		return $this;
	}

    /*
     * return an array of tabs
     */
    public function getTabsForForm($for_select = true)
    {
        $tabs = [];
        if ($this->tabs){
            $tabs_string = $this->tabs;
            if ($tabs_string){
                $tabs_split = preg_split("/\r\n|\n|\r/", $tabs_string); // split text by newlines
                foreach ($tabs_split as $tab) {
                    $clean_tab = strtolower(trim($tab)); // lowercase stripped of leading and trailing
                    if ($for_select) {
                        // For a form we want a select dropdown 
                        $tabs[$clean_tab] = ucfirst($clean_tab);
                    } else {
                        $tabs[$clean_tab] = [];
                    }
                }
            }
        }
        return $tabs;
    }
}
