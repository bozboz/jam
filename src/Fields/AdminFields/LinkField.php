<?php

namespace Bozboz\Jam\Fields\AdminFields;

use Bozboz\Admin\Fields\Field;
use Bozboz\Admin\Fields\TextField;
use View;

class LinkField extends Field
{
    protected $legend;
    protected $fields;
    protected $view = 'jam::admin.partials.link-field';

    public function __construct($name, $attributes=[])
    {
        if (is_array($name)) {
            $attributes = $name;
            $name = $attributes['name'];
        }

        if (key_exists('label', $attributes)) {
            $this->legend = $attributes['label'];
        } else {
            $this->legend = $name;
        }

        $this->fields = $this->getFields($name);
        $this->attributes = $attributes;
    }

    private function getFields($name)
    {
        return collect([
            new TextField($name.'[label]', ['label' => 'Label']),
            new TextField($name.'[url]', ['label' => 'URL']),
        ]);
    }

    public function getInput()
    {
        return View::make($this->view)->with([
            'legend' => $this->legend,
            'fields' => $this->fields,
            'attributes' => $this->attributes,
        ]);
    }

    public function render($errors)
    {
        return '<div class="form-group">'.$this->getInput().'</div>';
    }
}
