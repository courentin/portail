<?php
/**
 * Service authentification de base.
 *
 * @author Alexandre Brasseur <abrasseur.pro@gmail.com>
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2018, SiMDE-UTC
 * @license GNU GPL-3.0
 */

namespace App\Services\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\Session;

abstract class BaseAuth
{
    /**
     * Attributs à définir.
     */
    protected $name;
    protected $config;

    /**
     * Renvoie un lien vers le formulaire de login.
     *
     * @param Request $request
     * @return mixed
     */
    public function showLoginForm(Request $request)
    {
        return view(
            'auth.'.$this->name.'.login',
            ['provider' => $this->name, 'redirect' => $request->query('redirect', url()->previous())]
        );
    }

    /**
     * Renvoie un lien vers le formulaire d'enregistrement.
     *
     * @param Request $request
     * @return mixed
     */
    public function showRegisterForm(Request $request)
    {
        if ($this->config['registrable']) {
            return view(
	            'auth.'.$this->name.'.register',
	            ['provider' => $this->name, 'redirect' => $request->query('redirect', url()->previous())]
            );
        } else {
            return redirect()->route(
	            'register.show',
	            ['redirect' => $request->query('redirect', url()->previous())]
            )->cookie('auth_provider', '', config('portail.cookie_lifetime'));
        }
    }

    /**
     * Méthode de connexion.
     *
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        return null;
    }

    /**
     * Méthode d'inscription.
     *
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request)
    {
        return null;
    }

    /**
     * Méthode de déconnexion.
     *
     * @param Request $request
     * @return mixed
     */
    public function logout(Request $request)
    {
        return null;
    }

    /**
     * Retrouve l'utilisateur via le modèle qui correspond au mode d'authentification.
     *
     * @param string $key
     * @param string $value
     * @return mixed
     */
    protected function findUser(string $key, string $value)
    {
        return resolve($this->config['model'])::where($key, $value)->first();
    }

    /**
     * Crée l'utilisateur et son mode de connexion auth_{provider}.
     *
     * @param Request $request
     * @param array   $userInfo
     * @param array   $authInfo
     * @return mixed
     */
    protected function create(Request $request, array $userInfo, array $authInfo)
    {
        // Création de l'utilisateur avec les informations minimales.
        try {
            $user = $this->createUser($userInfo);
        } catch (\Exception $e) {
            return $this->error($request, null, null, 'Cette adresse mail est déjà utilisée');
        }

        // On crée le système d'authentification.
        $userAuth = $this->createAuth($user->id, $authInfo);

        return $this->connect($request, $user, $userAuth);
    }

    /**
     * Met à jour les informations de l'utilsateur et de son mode de connexion auth_{provider}.
     *
     * @param Request $request
     * @param string  $user_id
     * @param array   $userInfo
     * @param array   $authInfo
     * @return mixed
     */
    protected function update(Request $request, string $user_id, array $userInfo=[], array $authInfo=[])
    {
        // Actualisation des informations.
        $user = $this->updateUser($user_id, $userInfo);

        // On actualise le système d'authentification.
        $userAuth = $this->updateAuth($user_id, $authInfo);

        return $this->connect($request, $user, $userAuth);
    }

    /**
     * Crée ou ajuste les infos de l'utilisateur et son mode de connexion auth_{provider}.
     *
     * @param Request $request
     * @param string  $key
     * @param string  $value
     * @param array   $userInfo
     * @param array   $authInfo
     * @return mixed
     */
    protected function updateOrCreate(Request $request, string $key, string $value, array $userInfo=[], array $authInfo=[])
    {
        // On cherche l'utilisateur.
        $userAuth = $this->findUser($key, $value);

        if ($userAuth === null) {
            $user = isset($userInfo['email']) ? User::where('email', $userInfo['email'])->first() : null;

            if ($user === null) {
                try {
                    return $this->create($request, $userInfo, $authInfo);
                    // Si inconnu, on le crée et on le connecte.
                } catch (\Exception $e) {
                    return $this->error(
                        $request, null, null,
                    	'Cette adresse mail est déjà utilisé mais n\'est pas relié au bon compte'
                    );
                }
            } else {
                $user = $this->updateUser($user->id, $userInfo);
                $userAuth = $this->createAuth($user->id, $authInfo);

                return $this->connect($request, $user, $userAuth);
            }
        } else {
            return $this->update($request, $userAuth->user_id, $userInfo, $authInfo);
            // Si connu, on actualise ses infos et on le connecte.
        }
    }

    /**
     * Crée l'utilisateur User.
     *
     * @param array $info
     * @return mixed
     */
    protected function createUser(array $info)
    {
        $user = User::create([
            'email' => $info['email'],
            'lastname' => $info['lastname'],
            'firstname' => $info['firstname'],
            'is_active' => true,
        ]);

        return $user;
    }

    /**
     * Met à jour l'utilisateur User.
     *
     * @param string $user_id
     * @param array  $info
     * @return mixed
     */
    protected function updateUser(string $user_id, array $info=[])
    {
        $user = User::find($user_id);

        if ($user === null) {
            return null;
        }

        if ($info !== []) {
            $user->lastname = $info['lastname'];
            $user->firstname = $info['firstname'];
            $user->save();
        }

        return $user;
    }

    /**
     * Création ou mis à jour de l'utilisateur User.
     *
     * @param array $info
     * @return mixed
     */
    protected function updateOrCreateUser(array $info)
    {
        $user = User::findByEmail($info['email']);

        if ($user) {
            return $this->updateUser($user->id, $info);
        } else {
            return $this->createUser($info);
        }
    }

    /**
     * Crée la connexion auth.
     *
     * @param string $user_id
     * @param array  $info
     * @return mixed
     */
    public function addAuth(string $user_id, array $info)
    {
        return resolve($this->config['model'])::create(array_merge($info, [
            'user_id' => $user_id,
        ]));
    }

    /**
     * Crée la connexion auth.
     *
     * @param string $user_id
     * @param array  $info
     * @return mixed
     */
    protected function createAuth(string $user_id, array $info=[])
    {
        return resolve($this->config['model'])::updateOrCreate([
            'user_id' => $user_id,
        ], array_merge($info, [
            'last_login_at' => new \DateTime(),
        ]));
    }

    /**
     * Met à jour la connexion auth.
     *
     * @param string $user_id
     * @param array  $info
     * @return mixed
     */
    protected function updateAuth(string $user_id, array $info=[])
    {
        $userAuth = resolve($this->config['model'])::find($user_id);

        foreach ($info as $key => $value) {
            $userAuth->$key = $value;
        }

        $userAuth->save();

        return $userAuth;
    }

    /**
     * Permet de se connecter.
     *
     * @param Request          $request
     * @param User             $user
     * @param \App\Models\Auth $userAuth
     * @return mixed
     */
    protected function connect(Request $request, User $user=null, \App\Models\Auth $userAuth=null)
    {
        // Si tout est bon, on le connecte.
        if ($user && $userAuth) {
            if (!$user->is_active) {
                return $this->error($request, $user, $userAuth, 'Ce compte a été désactivé');
            }

            $user->timestamps = false;
            $user->last_login_at = new \DateTime();
            $user->save();

            $userAuth->timestamps = false;
            $userAuth->last_login_at = new \DateTime();
            $userAuth->save();

            Auth::guard('web')->login($user);
            \Session::put('auth_provider', $this->name);

            return $this->success($request, $user, $userAuth);
        } else {
            return $this->error($request, $user, $userAuth);
        }
    }

    /**
     * Redirige vers la bonne page en cas de succès.
     *
     * @param Request          $request
     * @param User             $user
     * @param \App\Models\Auth $userAuth
     * @param string           $message
     * @return mixed
     */
    protected function success(Request $request, User $user=null, \App\Models\Auth $userAuth=null, string $message=null)
    {
        if ($message === null) {
            return redirect(\Session::get('url.intended', '/'));
        } else {
            return redirect(\Session::get('url.intended', '/'))->withSuccess($message);
        }
    }

    /**
     * Redirige vers la bonne page en cas d'erreur.
     *
     * @param Request          $request
     * @param User             $user
     * @param \App\Models\Auth $userAuth
     * @param string           $message
     * @return mixed
     */
    protected function error(Request $request, User $user=null, \App\Models\Auth $userAuth=null, string $message=null)
    {
        if ($message === null) {
            return redirect()->route(
	            'login.show',
	            ['provider' => $this->name]
            )->withError('Il n\'a pas été possible de vous connecter')->withInput();
        } else {
            return redirect()->route('login.show', ['provider' => $this->name])->withError($message)->withInput();
        }
    }
}
