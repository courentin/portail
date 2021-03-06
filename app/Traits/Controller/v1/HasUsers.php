<?php
/**
 * Ajoute au controlleur un accès aux utilisateurs.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2018, SiMDE-UTC
 * @license GNU GPL-3.0
 */

namespace App\Traits\Controller\v1;

use App\Exceptions\PortailException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

trait HasUsers
{
    /**
     * Renvoie les informations sur un utilisateur via son id ou sur l'utilisateur actuellement connecté.
     *
     * @param Request     $request
     * @param string|null $user_id
     * @param boolean     $accessOtherUsers
     * @return User
     */
    protected function getUser(Request $request, string $user_id=null, bool $accessOtherUsers=false): User
    {
        if (\Uuid::validate($user_id)) {
            $user = User::find($user_id);
        } else {
            $user = User::where('email', $user_id)->first();
        }

        if (\Scopes::isUserToken($request)) {
            if (is_null($user_id)) {
                $user = \Auth::user();
            } else if (!$accessOtherUsers && $user->id !== \Auth::id()) {
                abort(403, 'Vous n\'avez pas le droit d\'accéder aux données d\'un autre utilisateur');
            }
        }

        if ($user) {
            return $user;
        } else {
            abort(404, "Utilisateur non trouvé");
        }
    }
}
