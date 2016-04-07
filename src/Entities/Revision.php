<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Base\ModelInterface;
use Bozboz\Admin\Base\SanitisesInputTrait;
use Bozboz\Admin\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Revision extends Model implements ModelInterface
{
	use SoftDeletes;
	use SanitisesInputTrait;

	protected $table = 'entity_revisions';

	protected $fillable = [
		'entity_id',
		'published_at',
		'user_id'
	];

	protected $dates = ['published_at', 'deleted_at'];

	const PUBLISHED = 1;
	const SCHEDULED = 2;

	public function duplicate()
	{
		$newRevision = $this->replicate();
		$newRevision->save();

		$this->fieldValues->each(function($value) use ($newRevision) {
			$newValue = $value->duplicate($newRevision);
		});

		return $newRevision;
	}

	public function entity()
	{
		return $this->belongsTo(Entity::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function getUsernameAttribute()
	{
		if ($this->user) {
			return $this->user->first_name ? $this->user->first_name . ' ' . $this->user->last_name : $this->user->email;
		}
	}

	public function fieldValues()
	{
		return $this->hasMany(Value::class);
	}

	public function scopeIsPublished($query)
	{
		$query->where('published_at', '<', $this->freshTimestamp());
	}

	public function getStatusAttribute()
	{
		if ($this->published_at && $this->published_at->isFuture()) {
			return static::SCHEDULED;
		} else {
			return static::PUBLISHED;
		}
	}

	public function getFormattedPublishedAtAttribute($format = null)
	{
		if ($this->published_at) {
			return $this->published_at->format($format?:'d-m-Y H:i');
		}
	}
}
