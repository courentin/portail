<?php

namespace App\Models;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;

class Place extends Model
{
    use SpatialTrait;

    protected $fillable = [
		'name', 'address', 'city', 'country', 'position',
	];

    protected $spatialFields = [
        'position',
    ];

	public function hideData(array $params = []): Model {
		return $this; // TODO
	}

    public function locations() {
        return $this->hasMany(Location::class);
    }
}
