<?php
/**
 * Pivot correspondant à la relation roles - utlisateurs.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019, SiMDE-UTC
 * @license GNU GPL-3.0
 */

namespace App\Pivots;

use App\Models\{
	User, Semester, Role
};

class UserRole extends Pivot
{
    protected $table = 'users_roles';

    protected $attributes = [
        'user_id', 'semester_id', 'role_id', 'validated_by_id', 'created_at', 'updated_at'
    ];

    protected $hidden = [
        'user_id', 'semester_id', 'role_id', 'validated_by_id'
    ];

    protected $with = [
        'user',
        'semester',
        'role',
        'validated_by',
    ];

    /**
     * Relation avec l'accès.
     *
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le semestre.
     *
     * @return mixed
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Relation avec l'accès.
     *
     * @return mixed
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relation avec l'utilisateur ayant validé.
     *
     * @return mixed
     */
    public function validated_by()
    {
        return $this->belongsTo(User::class);
    }
}
