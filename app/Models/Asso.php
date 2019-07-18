<?php
/**
 * Modèle correspondant aux associations.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 * @author Natan Danous <natous.danous@hotmail.fr>
 * @author Alexandre Brasseur <abrasseur.pro@gmail.com>
 * @author Rémy Huet <remyhuet@gmail.com>
 *
 * @copyright Copyright (c) 2018, SiMDE-UTC
 * @license GNU GPL-3.0
 */

namespace App\Models;

use Cog\Contracts\Ownership\CanBeOwner;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\{
    HasMembers, HasStages, HasDeletedSelection
};
use Illuminate\Notifications\Notifiable;
use App\Interfaces\Model\{
    CanHaveContacts, CanHaveEvents, CanHaveCalendars, CanHaveArticles, CanHaveRooms,
    CanHaveBookings, CanNotify, CanHaveRoles, CanHavePermissions, CanComment
};
use Illuminate\Support\Collection;
use App\Exceptions\PortailException;
use App\Pivots\AssoMember;

class Asso extends Model implements CanBeOwner, CanHaveContacts, CanHaveCalendars, CanHaveEvents, CanHaveArticles,
	CanNotify, CanHaveRooms, CanHaveBookings, CanHaveRoles, CanHavePermissions, CanComment
{
    use HasStages, HasMembers, SoftDeletes, HasDeletedSelection, Notifiable {
        HasMembers::members as membersAndFollowers;
        HasMembers::currentMembers as currentMembersAndFollowers;
        HasMembers::joiners as protected joinersFromHasMembers;
        HasMembers::currentJoiners as currentJoinersFromHasMembers;
        HasMembers::getUserRoles as getUsersRolesInThisAssociation;
    }

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    protected $fillable = [
        'name', 'shortname', 'login', 'image', 'description', 'type_id', 'parent_id',
    ];

    protected $hidden = [
        'type_id', 'parent_id',
    ];

    protected $with = [
        'type',
    ];

    protected $optional = [
        'children', 'parent'
    ];

    protected $must = [
        'name', 'shortname', 'login', 'image', 'deleted_at',
    ];

    // Children dans le cas où on affiche en mode étagé.
    protected $selection = [
        'order' => [
            'default' => 'oldest',
            'columns' => [
                'name' => 'shortname',
            ],
        ],
        'deleted' => 'without',
        'filter' => [],
        'stage' => null,
        'stages' => null,
    ];

    protected $roleRelationTable = 'assos_members';

    /**
     * Appelé à la création du modèle.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            // On crée automatiquement des moyens de contacts !
            $model->contacts()->create([
                'name' => 'Adresse email',
                'value' => $model->login.'@assos.utc.fr',
                'type_id' => ContactType::where('name', 'Adresse email')->first()->id,
                'visibility_id' => Visibility::findByType('public')->id,
            ]);

            $model->contacts()->create([
                'name' => 'Site Web',
                'value' => 'https://assos.utc.fr/'.$model->login.'/',
                'type_id' => ContactType::where('name', 'Url')->first()->id,
                'visibility_id' => Visibility::findByType('public')->id,
            ]);

            // On crée un calendrier pour chaque association.
            $model->calendars()->create([
                'name' => 'Evénements',
                'description' => 'Calendrier regroupant les événements de l\'associations',
                'visibility_id' => Visibility::findByType('public')->id,
                'created_by_id' => $model->id,
                'created_by_type' => Asso::class,
            ]);
        });
    }

    /**
     * Retrouve une association par son login.
     *
     * @param  mixed  $query
     * @param  string $login
     * @return mixed
     */
    public function scopeFindByLogin($query, string $login)
    {
        $asso = $query->where('login', $login)->first();

        if ($asso) {
            return $asso;
        }

        throw new PortailException('Association non existante');
    }

    /**
     * Relation avec le type d'association.
     *
     * @return mixed
     */
    public function type()
    {
        return $this->belongsTo(AssoType::class, 'type_id');
    }

    /**
     * Relation avec l'association parent.
     *
     * @return mixed
     */
    public function parent()
    {
        return $this->hasOne(Asso::class, 'id', 'parent_id');
    }

    /**
     * Relation avec les associations enfants.
     *
     * @return mixed
     */
    public function children()
    {
        return $this->hasMany(Asso::class, 'parent_id', 'id');
    }

    /**
     * Relation avec les accès de l'association.
     *
     * @return mixed
     */
    public function access()
    {
        return $this->hasMany(AssoAccess::class);
    }

    /**
     * Relation avec les membres de l'association.
     *
     * @return mixed
     */
    public function members()
    {
        return $this->membersAndFollowers()->wherePivot('role_id', '!=', null);
    }

    /**
     * Relation avec les membres du semestre actuel de l'association.
     *
     * @return mixed
     */
    public function currentMembers()
    {
        return $this->currentMembersAndFollowers()->wherePivot('role_id', '!=', null)->using(AssoMember::class);
    }

    /**
     * Relation avec les membres en attente de validation de l'association.
     *
     * @return mixed
     */
    public function joiners()
    {
        return $this->joinersFromHasMembers()->wherePivot('role_id', '!=', null);
    }

    /**
     * Relation avec les membres en attente de validation du semestre actuel de l'association.
     *
     * @return mixed
     */
    public function currentJoiners()
    {
        return $this->currentJoinersFromHasMembers()->wherePivot('role_id', '!=', null);
    }

    /**
     * Relation avec les suiveurs de l'association.
     *
     * @return mixed
     */
    public function followers()
    {
        return $this->membersAndFollowers()->wherePivot('role_id', null);
    }

    /**
     * Relation avec les suiveurs du semestre actuel de l'association.
     *
     * @return mixed
     */
    public function currentFollowers()
    {
        return $this->currentMembersAndFollowers()->wherePivot('role_id', null);
    }

    /**
     * Notifie les membres de l'association.
     *
     * @param  mixed        $notification
     * @param  string|array $restrictToRoleIds
     * @return void
     */
    public function notifyMembers($notification, $restrictToRoleIds=null)
    {
        $members = $this->currentMembers();

        if ($restrictToRoleIds) {
            $members->wherePivotIn('role_id', (array) $restrictToRoleIds);
        }

        foreach ($members->get() as $member) {
            $member->notify($notification);
        }
    }

    /**
     * Donne l'adresse email de notification.
     *
     * @param  mixed $notification
     * @return string
     */
    public function routeNotificationForMail($notification)
    {
        return $this->contacts()->keyExistsInDB('CONTACT_EMAIL') ? $this->contacts()->valueOf('CONTACT_EMAIL') : null;
    }

    /**
     * Donne l'icône de notification en tant que créateur.
     *
     * @param  Notification $notification
     * @return string
     */
    public function getNotificationIcon(Notification $notification)
    {
        return $this->image;
    }

    /**
     * Donne le dernier utilisateur avec un rôle.
     *
     * @param  mixed $role
     * @return User|null
     */
    public function getLastUserWithRole($role)
    {
        $members = $this->members()->wherePivot('role_id', Role::getRole($role, $this)->id)->get();

        $latestMember = null;
        foreach ($members as $member) {
            if (!$latestMember) {
                $latestMember = $member;
                continue;
            }

            $date = Semester::find($member->pivot->semester_id)->end_at;
            $lastDate = Semester::find($latestMember->pivot->semester_id)->end_at;

            if ($date > $lastDate) {
                $latestMember = $member;
            }
        }

        return $latestMember;
    }

    /**
     * Indique si un rôle est affichable ou non.
     * On affiche toujours le rôle des membres, ce n'est pas un secret.
     *
     * @param  string $user_id
     * @return boolean
     */
    public function isRoleAccessibleBy(string $user_id): bool
    {
        return true;
    }

    /**
     * Indique si un rôle est modifiable ou non.
     * Un rôle est modifiable uniquement par un membre ayant le droit.
     *
     * @param  string $user_id
     * @return boolean
     */
    public function isRoleManageableBy(string $user_id): bool
    {
        return User::find($user_id)->hasOnePermission('role');
    }

    /**
     * Indique si une permission est affichable ou non.
     * On affiche toujours les permissions des membres, ce n'est pas un secret.
     *
     * @param  string $user_id
     * @return boolean
     */
    public function isPermissionAccessibleBy(string $user_id): bool
    {
        return true;
    }

    /**
     * Indique si une permission est gérable ou non.
     * Une permission est modifiable uniquement par un membre ayant la permission.
     *
     * @param  string $user_id
     * @return boolean
     */
    public function isPermissionManageableBy(string $user_id): bool
    {
        return User::find($user_id)->hasOnePermission('permission');
    }

    /**
     * Relation avec les moyens de contacts de l'association.
     *
     * @return mixed
     */
    public function contacts()
    {
        return $this->morphMany(Contact::class, 'owned_by');
    }

    /**
     * Indique si le moyen de contact est accessible.
     * Affichable uniquement pour les membres (données privées).
     *
     * @param  string $user_id
     * @return boolean
     */
    public function isContactAccessibleBy(string $user_id): bool
    {
        return $this->currentMembers()->wherePivot('user_id', $user_id)->exists();
    }

    /**
     * Indique si le moyen de contact est gérable.
     * Modifiable uniquement par un membre ayant la permission.
     *
     * @param  string $user_id
     * @return boolean
     */
    public function isContactManageableBy(string $user_id): bool
    {
        return $this->hasOnePermission('contact', [
            'user_id' => $user_id,
        ]);
    }

    /**
     * Relation avec les calendriers.
     *
     * @return mixed
     */
    public function calendars()
    {
        return $this->morphMany(Calendar::class, 'owned_by');
    }

    /**
     * Indique si le calendrier est gérable.
     * Seulement les membres ayant la permission peuvent modifier les calendriers privées.
     *
     * @param  string $user_id
     * @return boolean
     */
    public function isCalendarManageableBy(string $user_id): bool
    {
        return $this->hasOnePermission('calendar', [
            'user_id' => $user_id,
        ]);
    }

    /**
     * Relation avec les évènements.
     *
     * @return mixed
     */
    public function events()
    {
        return $this->morphMany(Event::class, 'owned_by');
    }

    /**
     * Indique si un évènement est gérable.
     * Seulement les membres ayant la permission peuvent modifier les évènements privés.
     *
     * @param  string $user_id
     * @return boolean
     */
    public function isEventManageableBy(string $user_id): bool
    {
        return $this->hasOnePermission('event', [
            'user_id' => $user_id,
        ]);
    }

    /**
     * Relation avec les articles.
     *
     * @return mixed
     */
    public function articles()
    {
        return $this->morphMany(Article::class, 'owned_by');
    }

    /**
     * Indique si un article est gérable.
     * Seulement les membres peuvent modifier les articles privés.
     *
     * @param  string $user_id
     * @return boolean
     */
    public function isArticleManageableBy(string $user_id): bool
    {
        return $this->hasOnePermission('article', [
            'user_id' => $user_id,
        ]);
    }

    /**
     * Relation avec les salles.
     *
     * @return mixed
     */
    public function rooms()
    {
        return $this->morphMany(Room::class, 'owned_by');
    }

    /**
     * Indique si une salle est gérable.
     * Seulement les membres peuvent modifier les salles privées.
     *
     * @param  string $user_id
     * @return boolean
     */
    public function isRoomManageableBy(string $user_id): bool
    {
        return User::find($user_id)->hasOnePermission('room');
    }

    /**
     * Indique si la salle est réservable.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return boolean
     */
    public function isRoomReservableBy(\Illuminate\Database\Eloquent\Model $model): bool
    {
        if (!($model instanceof Asso)) {
            throw new PortailException('Seules les associations peuvent réserver une salle appartenant à une association', 503);
        }

        // On regarde si l'asso est un enfant de celle possédant la salle (ex: Picsart peut réserver du PAE).
        $toMatch = $model;
        while ($toMatch) {
            if ($toMatch->id === $this->id) {
                return true;
            }

            $toMatch = $toMatch->parent;
        }

        // Correspond aux assos parents.
        return $this->isBookingValidableBy($model);
    }

    /**
     * Relation avec les réservations.
     *
     * @return mixed
     */
    public function bookings()
    {
        return $this->morphMany(Booking::class, 'owned_by');
    }

    /**
     * Indique si une réservation est accessible.
     * Seulement les membres peuvent voir les réservations privées.
     *
     * @param  string $user_id
     * @return boolean
     */
    public function isBookingAccessibleBy(string $user_id): bool
    {
        return $this->currentMembers()->wherePivot('user_id', $user_id)->exists();
    }

    /**
     * Indique si une réservation est gérable.
     * Seulement les membres peuvent modifier les réservations privées.
     *
     * @param  string $user_id
     * @return boolean
     */
    public function isBookingManageableBy(string $user_id): bool
    {
        return $this->hasOnePermission('booking', [
            'user_id' => $user_id,
        ]);
    }

    /**
     * Indique si une réservation est validable.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return boolean
     */
    public function isBookingValidableBy(\Illuminate\Database\Eloquent\Model $model): bool
    {
        if ($model instanceof Asso) {
            // On regarde si l'asso possédant la salle est un enfant de celle qui fait la demande (ex: BDE à le droit sur PAE).
            $toMatch = $this;
            while ($toMatch) {
                if ($toMatch->id === $model->id) {
                    return true;
                }

                $toMatch = $toMatch->parent;
            }

            return false;
        } else if ($model instanceof User) {
            return $this->hasOnePermission('booking', [
                'user_id' => $model->id,
            ]);
        } else if ($model instanceof Client) {
            return $model->asso->id === $this->id;
        } else {
            throw new PortailException('Seules les utilisateurs,
				associations et clients peuvent valider une salle appartenant à une association', 503);
        }
    }

    /**
     * Indique si un commentaire est rédigeable.
     * Les commentaires écrits par une asso se font uniquement par les gens pouvant en rédiger.
     *
     * @param  string $user_id
     * @return boolean
     */
    public function isCommentWritableBy(string $user_id): bool
    {
        return $this->hasOnePermission('comment', [
            'user_id' => $user_id,
        ]);
    }

    /**
     * Indique si un commentaire est modifiable.
     * Les commentaires écrits par une asso se font uniquement par les gens pouvant en modifier.
     *
     * @param  string $user_id
     * @return boolean
     */
    public function isCommentEditableBy(string $user_id): bool
    {
        return $this->isCommentWritableBy($user_id);
    }

    /**
     * Indique si un commentaire est supprimable.
     * Les commentaires écrits par une asso se font uniquement par les gens pouvant en supprimer.
     *
     * @param  string $user_id
     * @return boolean
     */
    public function isCommentDeletableBy(string $user_id): bool
    {
        return $this->isCommentEditableBy($user_id);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
