<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Base\Model;
use Bozboz\Admin\Base\ModelInterface;
use Bozboz\Admin\Base\SanitisesInputTrait;
use Bozboz\Admin\Users\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Revision extends Model
{
	use SoftDeletes;
	use SanitisesInputTrait;

	protected $table = 'entity_revisions';

	protected $fillable = [
		'entity_id',
		'published_at',
		'expired_at',
		'user_id'
	];

	protected $dates = ['published_at', 'deleted_at', 'expired_at'];

	const PUBLISHED = 1;
	const SCHEDULED = 2;
	const PUBLISHED_WITH_DRAFTS = 3;
	const EXPIRED = 4;

	public static function boot()
	{
		parent::boot();

		static::created(function($revision) {
			$pastRevisionsQuery = static::whereEntityId($revision->entity->id);
			if ($revision->entity->currentRevision) {
				$pastRevisionsQuery->where('created_at', '<', $revision->entity->currentRevision->created_at);
			}
			static::whereIn(
				'id',
				$pastRevisionsQuery->orderBy('created_at', 'desc')
					->withTrashed()
					->skip(config('jam.revision_history_length'))->take(100)
					->pluck('id')
			)->forceDelete();
		});
	}

	public function duplicate($entity = null)
	{
		$newRevision = $this->replicate();

		if ($entity) {
			$newRevision->entity()->associate($entity);
		}

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
		$query->where(function($query) {
			$query->where('published_at', '<', $this->freshTimestamp())
				->orWhere('expired_at', '>', $this->freshTimestamp())
				->orWhere(function($query) {
					$query->whereNull('expired_at');
				});
		});
	}

	public function getStatusAttribute()
	{
		if ($this->entity->latestRevision()->id !== $this->id) {
			return static::PUBLISHED_WITH_DRAFTS;
		} elseif ($this->published_at && $this->published_at->isFuture()) {
			return static::SCHEDULED;
		} elseif ($this->expired_at && $this->expired_at->isPast()){
			return static::EXPIRED;
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

	public function getFormattedExpiredAtAttribute($format = null)
	{
		if ($this->expired_at) {
			return $this->expired_at->format($format?:'d-m-Y H:i');
		}
	}
}
