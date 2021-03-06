<?php
/**
 * Modèle abstrait correspondant aux authentifications.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2018, SiMDE-UTC
 * @license GNU GPL-3.0
 */

namespace App\Models;

use App\Traits\Model\{
    HasHiddenData, IsLogged
};
use NastuzziSamy\Laravel\Traits\HasSelection;

abstract class Auth extends Model
{
    use HasHiddenData, HasSelection, IsLogged;

    public $incrementing = false;

    /**
     * Relation avec l'utilisateur.
     *
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Permet de vérifier la connexion d'un utilisateur en fonction des différents types d'authentification.
     *
     * @param string $username
     * @return mixed
     */
    abstract public function getUserByIdentifiant(string $username);

    /**
     * Permet de vérifier la connexion d'un utilisateur en fonction des différents types d'authentification.
     *
     * @param string $password
     * @return boolean
     */
    abstract public function isPasswordCorrect(string $password);
}
