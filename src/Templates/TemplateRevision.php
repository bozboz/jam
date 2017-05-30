<?php

namespace Bozboz\Jam\Templates;

use Bozboz\Admin\Base\Model;
use Bozboz\Admin\Users\User;
use Bozboz\Jam\Templates\Template;

class TemplateRevision extends Model
{
    protected $table = 'entity_template_history';

    protected $guarded = ['id'];

    public function revisionable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function getRevisionableNameAttribute()
    {
        if ($this->revisionable) {
            return $this->revisionable->name;
        }
        if ($this->decoded_old && property_exists($this->decoded_old, 'name')) {
            return $this->decoded_old->name;
        }
        if ($this->decoded_new && property_exists($this->decoded_new, 'name')) {
            return $this->decoded_new->name;
        }
    }

    public function getUserNameAttribute()
    {
        return $this->user ? "{$this->user->first_name} {$this->user->last_name}" : 'Unknown';
    }

    public function getTemplateNameAttribute()
    {
        return $this->template ? $this->template->name : 'Unknown';
    }

    public function setOldAttribute($value)
    {
        $this->attributes['old'] = json_encode($value);
    }

    public function setNewAttribute($value)
    {
        $this->attributes['new'] = json_encode($value);
    }

    public function getDecodedOldAttribute($asArray = false)
    {
        return json_decode($this->attributes['old'], $asArray);
    }

    public function getDecodedNewAttribute($asArray = false)
    {
        return json_decode($this->attributes['new'], $asArray);
    }
}