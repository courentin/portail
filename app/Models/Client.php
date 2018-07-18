<?php

namespace App\Models;

use Laravel\Passport\Client as PassportClient;
use App\Interfaces\Model\CanHideData;
use App\Interfaces\Controller\v1\CanHaveCalendars;
use App\Interfaces\Controller\v1\CanHaveEvents;

class Client extends PassportClient implements CanHideData, CanHaveCalendars, CanHaveEvents
{
    protected $fillable = [
        'user_id', 'name', 'secret', 'redirect', 'personal_access_client', 'password_client', 'revoked', 'created_at', 'updated_at', 'asso_id', 'scopes'
    ];

    public function hideData(array $params = []): Model {
        return $this->makeHidden(['user_id', 'redirect', 'personal_access_client', 'password_client', 'revoked', 'asso_id', 'created_at', 'updated_at', 'scopes']);
    }

    public function asso() {
        return $this->belongsTo(Asso::class);
    }

    public function calendars() {
    	return $this->morphMany(Calendar::class, 'owned_by');
    }

    public function events() {
    	return $this->morphMany(Event::class, 'owned_by');
    }

	public function isCalendarAccessibleBy(int $user_id): bool {
		return $this->asso()->currentMembers->wherePivot('user_id', $user_id)->exists();
	}

	public function isCalendarManageableBy(int $user_id): bool {
		return $this->asso()->hasOneRole('developer', ['user_id' => $user_id]);
	}

	public function isEventAccessibleBy(int $user_id): bool {
		return $this->asso()->currentMembers->wherePivot('user_id', $user_id)->exists();
	}

	public function isEventManageableBy(int $user_id): bool {
		return $this->asso()->hasOneRole('developer', ['user_id' => $user_id]);
	}
}
