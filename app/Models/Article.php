<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Cog\Contracts\Ownership\Ownable as OwnableContract;
use Cog\Laravel\Ownership\Traits\HasMorphOwner;
use App\Traits\Model\HasCreatorSelection;
use App\Traits\Model\HasOwnerSelection;
use App\Models\ArticleAction;

class Article extends Model implements OwnableContract
{
	use HasMorphOwner, HasCreatorSelection, HasOwnerSelection;

	protected $fillable = [
		'title', 'description', 'content', 'image', 'event_id', 'visibility_id', 'created_by_id', 'created_by_type', 'owned_by_id', 'owned_by_type',
	];

	protected $with = [
		'created_by', 'owned_by', 'tags', 'visibility', 'event',
	];

	protected $withModelName = [
		'created_by', 'owned_by',
	];

	protected $must = [
		'title', 'description', 'content', 'owned_by', 'created_at',
	];

	protected $hidden = [
		'created_by_id', 'created_by_type', 'owned_by_id', 'owned_by_type', 'visibility_id', 'event_id',
	];

	protected $selection = [
		'paginate' 	=> 10,
		'order'		=> [
			'default' 	=> 'latest',
			'columns'	=> [
				'name' 	=> 'title',
			],
		],
		'month'		=> [],
		'week'		=> [],
		'day'		=> [],
		'interval'	=> [],
		'date'		=> [],
		'dates'		=> [],
		'creator' 	=> [],
		'owner' 	=> [],
		'action'	=> [],
		'filter'	=> [],
	];

	public function scopeOrder(Builder $query, string $order) {
		if ($order === 'liked' || $order === 'disliked') {
		 	$actionTable = (new ArticleAction)->getTable();

			$query = $query->where($actionTable.'.key', 'LIKED')
				->join($actionTable, $actionTable.'.article_id', '=', $this->getTable().'.id')
				->groupBy($this->getTable().'.id')
				->orderByRaw('SUM(IF('.$actionTable.'.value='.((string) true).', 10, -5)) '.($order === 'liked' ? 'desc' : 'asc'));

			return $query->selectRaw($this->getTable().'.*');
		}
		else
			return parent::scopeOrder($query, $order);
	}

	public function scopeAction(Builder $query, string $action) {
		$actionTable = (new ArticleAction)->getTable();

		if (substr($action, 0, 1) === '!') {
			$action = substr($action, 1);

			return $query->whereNotExists(function ($query) use ($action, $actionTable) {
				if (\Auth::id())
					$query = $query->where($actionTable.'.user_id', \Auth::id());

				return $query->selectRaw('NULL')
					->from($actionTable)
					->where($actionTable.'.key', strtoupper($action))
					->whereRaw($actionTable.'.article_id = '.$this->getTable().'.id');
			});
		}
		else if (substr($action, 0, 2) === 'un' || substr($action, 0, 3) === 'dis') {
			$action = substr($action, 0, 2) === 'un' ? substr($action, 2) : substr($action, 3);

			$query = $query->where($actionTable.'.key', strtoupper($action))
				->where($actionTable.'.value', '<', 1)
				->join($actionTable, $actionTable.'.article_id', '=', $this->getTable().'.id');
		}
		else {
			$query = $query->where($actionTable.'.key', strtoupper($action))
				->where($actionTable.'.value', '>', 0)
				->join($actionTable, $actionTable.'.article_id', '=', $this->getTable().'.id');
		}

		if (\Auth::id())
			$query = $query->where($actionTable.'.user_id', \Auth::id());

		return $query;
	}

	public function getDescriptionAttribute() {
		$description = $this->getOriginal('description');

		if ($description)
			return $description;
		else
			return trimText($this->getAttribute('content'), validation_max('string'));
	}

	public function tags() {
		return $this->morphToMany(Tag::class, 'used_by', 'tags_used');
	}

	public function visibility() {
		return $this->belongsTo(Visibility::class, 'visibility_id');
	}

	public function comments() {
		return $this->morphMany('App\Models\Comment', 'commentable');
	}

	public function event() {
		return $this->belongsTo(Event::class);
	}

	public function actions() {
		return $this->hasMany(ArticleAction::class);
	}
}
