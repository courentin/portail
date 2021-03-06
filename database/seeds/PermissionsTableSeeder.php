<?php

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\User;
use App\Models\Asso;
use App\Models\Group;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            [
                'type' => 'treasury',
                'name' => 'Trésorerie',
                'description' => 'Gestion de la trésorerie de l\'association',
                'owned_by' => new Asso,
            ],
            [
                'type' => 'ticketing',
                'name' => 'Billetterie',
                'description' => 'Gestion de la billetterie de l\'association',
                'owned_by' => new Asso,
            ],
            [
                'type' => 'calendar',
                'name' => 'Calendrier',
                'description' => 'Gestion des calendriers de l\'association',
                'owned_by' => new Asso,
            ],
            [
                'type' => 'event',
                'name' => 'Evènement',
                'description' => 'Gestion des évènements de l\'association',
                'owned_by' => new Asso,
            ],
            [
                'type' => 'data',
                'name' => 'Informations',
                'description' => 'Gestion des informations concernant l\'association',
                'owned_by' => new Asso,
            ],
            [
                'type' => 'contact',
                'name' => 'Contact',
                'description' => 'Gestion des moyens de contact de l\'association',
                'owned_by' => new Asso,
            ],
            [
                'type' => 'article',
                'name' => 'Article',
                'description' => 'Gestion des articles de l\'association',
                'owned_by' => new Asso,
            ],
            [
                'type' => 'comment',
                'name' => 'Commentaire',
                'description' => 'Rédiger des commentaires de l\'association',
                'owned_by' => new Asso,
            ],
            [
                'type' => 'booking',
                'name' => 'Réservation',
                'description' => 'Gestion des réservations',
                'owned_by' => new Asso,
            ],
            [
                'type' => 'role',
                'name' => 'Rôle',
                'description' => 'Gestion des rôles',
                'owned_by' => new Asso,
            ],
            [
                'type' => 'permission',
                'name' => 'Permission',
                'description' => 'Gestion des permissions',
                'owned_by' => new Asso,
            ],
            [
                'type' => 'bobby',
                'name' => 'Service de réservation Bobby',
                'description' => 'Gestion du service Bobby',
                'owned_by' => new Asso,
            ],
            [
                'type' => 'access',
                'name' => 'Demande d\'accès',
                'description' => 'Gestion des demandes d\'accès',
                'owned_by' => new Asso,
            ],
            [
                'type' => 'member',
                'name' => 'Membre',
                'description' => 'Gestion des membres du groupe',
                'owned_by' => new Group,
            ],
            [
                'type' => 'calendar',
                'name' => 'Calendrier',
                'description' => 'Gestion des calendriers du groupe',
                'owned_by' => new Group,
            ],
            [
                'type' => 'event',
                'name' => 'Evènement',
                'description' => 'Gestion des évènements du groupe',
                'owned_by' => new Group,
            ],
            [
                'type' => 'contact',
                'name' => 'Contact',
                'description' => 'Gestion des contacts du groupe',
                'owned_by' => new Group,
            ],
            [
                'type' => 'role',
                'name' => 'Rôle',
                'description' => 'Gestion des rôles',
                'owned_by' => new Group,
            ],
            [
                'type' => 'permission',
                'name' => 'Permission',
                'description' => 'Gestion des permissions',
                'owned_by' => new Group,
            ],
            [
                'type' => 'superadmin',
                'name' => 'Super-Administrateur',
                'description' => 'Super administration',
                'owned_by' => new User,
            ],
            [
                'type' => 'admin',
                'name' => 'Administrateur',
                'description' => 'Administration',
                'owned_by' => new User,
            ],
            [
                'type' => 'user',
                'name' => 'Utilisateur',
                'description' => 'Gestion des utilisateurs',
                'owned_by' => new User,
            ],
            [
                'type' => 'asso',
                'name' => 'Association',
                'description' => 'Gestion des associations',
                'owned_by' => new User,
            ],
            [
                'type' => 'service',
                'name' => 'Service',
                'description' => 'Gestion des services',
                'owned_by' => new User,
            ],
            [
                'type' => 'group',
                'name' => 'Groupe',
                'description' => 'Gestion des groupes',
                'owned_by' => new User,
            ],
            [
                'type' => 'client',
                'name' => 'Client',
                'description' => 'Gestion des clients',
                'owned_by' => new User,
            ],
            [
                'type' => 'room',
                'name' => 'Salle de réservation',
                'description' => 'Gestion des salles de réservations',
                'owned_by' => new User,
            ],
            [
                'type' => 'role',
                'name' => 'Rôle',
                'description' => 'Gestion des rôles',
                'owned_by' => new User,
            ],
            [
                'type' => 'permission',
                'name' => 'Permission',
                'description' => 'Gestion des permissions',
                'owned_by' => new User,
            ],
            [
                'type' => 'bobby',
                'name' => 'Service de réservation Bobby',
                'description' => 'Gestion du service Bobby',
                'owned_by' => new User,
            ],
            [
                'type' => 'access',
                'name' => 'Demande d\'accès',
                'description' => 'Gestion des demandes d\'accès',
                'owned_by' => new User,
            ],
            [
                'type' => 'handle-access',
                'name' => 'Gestion des accès',
                'description' => 'Gestion des attributions d\'accès',
                'owned_by' => new User,
            ],
            [
                'type' => 'handle-assos-members',
                'name' => 'Gestion des membres associatifs',
                'description' => 'Gestion des validations, rôles',
                'owned_by' => new User,
            ],
            [
                'type' => 'handle-users-roles',
                'name' => 'Gestion des rôles utilisateurs',
                'description' => 'Gestion des rôles utilisateurs',
                'owned_by' => new User,
            ],
            [
                'type' => 'handle-users-permissions',
                'name' => 'Gestion des permissions utilisateurs',
                'description' => 'Gestion des permissions utilisateurs',
                'owned_by' => new User,
            ],
            [
                'type' => 'search',
                'name' => 'Recherche',
                'description' => 'Rechercher des utilisateurs',
                'owned_by' => new User,
            ],
            [
                'type' => 'user-impersonate',
                'name' => 'Personnification',
                'description' => 'Devenir un autre utilisateur',
                'owned_by' => new User,
            ],
            [
                'type' => 'user-contributeBde',
                'name' => 'Cotisation BDE',
                'description' => 'Gestion de la cotisation BDE',
                'owned_by' => new User,
            ],
            [
                'type' => 'article',
                'name' => 'Article',
                'description' => 'Gestion des articles',
                'owned_by' => new User,
            ],
            [
                'type' => 'article-action',
                'name' => 'Action sur un article',
                'description' => 'Gestion des actions sur les articles',
                'owned_by' => new User,
            ],
            [
                'type' => 'asso-access',
                'name' => 'Accès des associations',
                'description' => 'Gestion des accès par association',
                'owned_by' => new User,
            ],
            [
                'type' => 'asso-type',
                'name' => 'Type des associations',
                'description' => 'Gestion des types d\'association',
                'owned_by' => new User,
            ],
            [
                'type' => 'semester',
                'name' => 'Semestre',
                'description' => 'Gestion des semestres',
                'owned_by' => new User,
            ],
            [
                'type' => 'place',
                'name' => 'Emplacement',
                'description' => 'Gestion des emplacements',
                'owned_by' => new User,
            ],
            [
                'type' => 'auth',
                'name' => 'Authentification',
                'description' => 'Gestion des systèmes d\'authentification',
                'owned_by' => new User,
            ],
            [
                'type' => 'event',
                'name' => 'Evénement',
                'description' => 'Gestion des événements',
                'owned_by' => new User,
            ],
            [
                'type' => 'user-preference',
                'name' => 'Préférences utilisateurs',
                'description' => 'Gestion des préférences des utilisateurs',
                'owned_by' => new User,
            ],
            [
                'type' => 'partner',
                'name' => 'Partenaires',
                'description' => 'Gestion des partenaires',
                'owned_by' => new User,
            ],
            [
                'type' => 'contact-type',
                'name' => 'Types de contact',
                'description' => 'Gestion des types des contacts',
                'owned_by' => new User,
            ],
            [
                'type' => 'session',
                'name' => 'Session',
                'description' => 'Gestion des sessions',
                'owned_by' => new User,
            ],
            [
                'type' => 'tag',
                'name' => 'Tag',
                'description' => 'Gestion des tags',
                'owned_by' => new User,
            ],
            [
                'type' => 'visibility',
                'name' => 'Visibilité',
                'description' => 'Gestion des visibilités',
                'owned_by' => new User,
            ],
            [
                'type' => 'contact',
                'name' => 'Moyen de contact',
                'description' => 'Gestion des moyens de contact',
                'owned_by' => new User,
            ],
            [
                'type' => 'user-detail',
                'name' => 'Détails utilisateurs',
                'description' => 'Gestion des détails utilisateurs',
                'owned_by' => new User,
            ],
            [
                'type' => 'booking',
                'name' => 'Réservation',
                'description' => 'Gestion des réservations',
                'owned_by' => new User,
            ],
            [
                'type' => 'comment',
                'name' => 'Commentaire',
                'description' => 'Gestion des commentaires',
                'owned_by' => new User,
            ],
            [
                'type' => 'calendar',
                'name' => 'Calendrier',
                'description' => 'Gestion des calendriers',
                'owned_by' => new User,
            ],
            [
                'type' => 'booking-type',
                'name' => 'Type de réservation',
                'description' => 'Gestion des types des réservations',
                'owned_by' => new User,
            ],
            [
                'type' => 'location',
                'name' => 'Lieux',
                'description' => 'Gestion des lieux',
                'owned_by' => new User,
            ],
            [
                'type' => 'event-detail',
                'name' => 'Détail événement',
                'description' => 'Gestion des détails événements',
                'owned_by' => new User,
            ],
            [
                'type' => 'notification',
                'name' => 'Notification',
                'description' => 'Gestion des notifications',
                'owned_by' => new User,
            ],
        ];

        foreach ($permissions as $permission) {
            $model = Permission::create([
                'type' => $permission['type'],
                'name' => $permission['name'],
                'description' => $permission['description'],
                'owned_by_id' => $permission['owned_by']->id,
                'owned_by_type' => get_class($permission['owned_by']),
            ]);
        }
    }
}
