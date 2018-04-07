<?php

namespace App\Services\Visible;

use Auth;
use Ginger;
use App\Models;

/**
 * Fonction permettant de traiter la visibilité des informations
 */
class Visible {
    /**
     * Fonction permettant de renvoyer toutes les informations tout en cachant celles privées
     * @param  Collection/Model $collection Collection ou modèle sur lequel travailler
     * @param  int $user_id    id de l'utilisateur dont on veut connaître sa visibilité
     * @return Collection/Model Collection ou modèle avec les informations privées cachées
     */
    public static function hide($collection, $user_id = null) {
        if ($user_id === null && Auth::user() !== null)
			$user_id = Auth::user()->id;

		$visibilities = Models\Visibility::all();

		if (get_class($collection) === 'Illuminate\Database\Eloquent\Collection') {
			foreach ($collection as $key => $model) {
				if (!self::isVisible($visibilities, $model, $user_id)) {
					$collection[$key] = self::hideData($visibilities, $model);
				}
			}

			return $collection;
		}
		else {
			if (!self::isVisible($visibilities, $collection, $user_id))
				return self::hideData($visibilities, $collection);
			else
				return $collection;
		}
    }

	/**
	 * Fonction permettant de renvoyer toutes les informations visibles
	 * @param  Collection/Model $collection Collection ou modèle sur lequel travailler
	 * @param  int $user_id    id de l'utilisateur dont on veut connaître sa visibilité
	 * @return Collection/Model Collection ou modèle visible
	 */
	public static function with($collection, $user_id = null) {
	    return self::remove($collection, $user_id, false);
	}

	/**
	 * Fonction permettant de renvoyer toutes les informations non-visibles
	 * @param  Collection/Model $collection Collection ou modèle sur lequel travailler
	 * @param  int $user_id    id de l'utilisateur dont on veut connaître sa visibilité
	 * @return Collection/Model Collection ou modèle non-visible
	 */
	public static function without($collection, $user_id = null) {
	    return self::remove($collection, $user_id, true);
	}

	/**
	 * Fonction permettant de renvoyer toutes les informations non-visibles et cachées
	 * @param  Collection/Model $collection Collection ou modèle sur lequel travailler
	 * @param  int $user_id    id de l'utilisateur dont on veut connaître sa visibilité
	 * @return Collection/Model Collection ou modèle non-visible avec les informations cachées
	 */
	public static function hideAndWithout($collection, $user_id = null) {
		if ($user_id === null && Auth::user() !== null)
			$user_id = Auth::user()->id;

		$visibilities = Models\Visibility::all();

		if (get_class($collection) === 'Illuminate\Database\Eloquent\Collection') {
			foreach ($collection as $key => $model) {
				if (!self::isVisible($visibilities, $model, $user_id)) {
					$collection[$key] = self::hideData($visibilities, $model);
				}
				else
					$collection->forget($key);
			}

			return $collection;
		}
		else {
			if (!self::isVisible($visibilities, $collection, $user_id)) {
				return self::hideData($visibilities, $collection);
			}
			else
				return null;
		}
	}

	public static function getType($user_id = null) {
		$visibilities = Models\Visibility::all();
		$visibility_id = $visibilities->first()->id;

		if ($user_id === null)
			return 'public';

		$result = 'public';

		while ($visibility_id !== null) {
			$visibility = $visibilities->find($visibility_id);

			if ($visibility === null)
				return false;

			$type = 'is'.ucfirst($visibility->type);

			if (method_exists(get_class(), $type) && self::$type(null, $user_id))
				$result = $visibility->type;
			else
				break;

			$visibility_id = $visibility->parent_id;
		}

		return $result;
	}

	/**
	 * Fonction permettant de retirer toutes les informations visbiles ou non-visibles
	 * @param  Collection/Model $collection Collection ou modèle sur lequel travailler
	 * @param  int $user_id    id de l'utilisateur dont on veut connaître sa visibilité
	 * @param  boolean  $visible    Indique quel type d'infos à supprimer
	 * @return Collection/Model Collection ou modèle visible ou non-visible
	 */
    protected static function remove($collection, $user_id, $visible) {
        if ($user_id === null && Auth::user() !== null)
			$user_id = Auth::user()->id;

		$visibilities = Models\Visibility::all();

		if (get_class($collection) === 'Illuminate\Database\Eloquent\Collection') {
			foreach ($collection as $key => $model) {
				if (self::isVisible($visibilities, $model, $user_id) === $visible)
					$collection->forget($key);
			}

			return $collection;
		}
		else {
			if (self::isVisible($visibilities, $collection, $user_id) === $visible)
				return null;
			else
				return $collection;
		}
    }

	/**
	 * Fonction permettant de cacher les infos d'un modèle
	 * @param  Collection $visibilities Liste des visibilités
	 * @param  Model $model        Model sur lequel travailler
	 * @return Model               Liste des infos cachées
	 */
    protected static function hideData($visibilities, $model) {
        return [
			'id' => $model->id,
			'hidden' => true,
			'visibility' => $visibilities->find($model->visibility_id),
		];
    }

	/**
	 *  Fonction permettant d'indiquer si la ressource peut-être visible ou non pour la personne
	 * @param  Collection $visibilities Liste des visibilités
	 * @param  Model $model        Model sur lequel travailler
	 * @param  int $user_id    id de l'utilisateur dont on veut connaître sa visibilité
	 *  @return boolean	           Visible ou non
	 */
	protected static function isVisible($visibilities, $model, $user_id = null) {
		if ($visibilities === null || $visibilities->count() === 0 || $visibilities === null)
			return true;

		$visibility_id = $model->visibility_id;

		if ($visibility_id === null)
			$visibility_id = $visibilities->first()->id;

		if ($user_id === null)
			return $visibilities->find($visibility_id)->type === 'public'; // Si on est pas co, on check si la visibilité est publique ou non

		while ($visibility_id !== null) {
			$visibility = $visibilities->find($visibility_id);

			if ($visibility === null)
				return false;

			$type = 'is'.ucfirst($visibility->type);

			if (method_exists(get_class(), $type) && self::$type($model, $user_id))
				return true;

			$visibility_id = $visibility->parent_id;
		}

	    return false;
	}

	protected static function isPublic($model, $user_id) {
		return true;
	}

	protected static function isLogged($model, $user_id) {
		return Models\User::find($user_id)->exists();
	}

	protected static function isCasOrWasCas($model, $user_id) {
		return Models\AuthCas::find($user_id)->exists();
	}

	protected static function isCas($model, $user_id) {
		return Models\AuthCas::find($user_id)->where('is_active', true)->exists();
	}

	protected static function isContributor($model, $user_id) {
		return Ginger::userExists(Models\AuthCas::find($user_id)->login);
	}

	protected static function isPrivate($model, $user_id) {
		if ($model === null)
			return false;

		try {
			$memberModel = resolve(get_class($model).'Member'); // En faisant ça, on optimise notre requête SQL en évitant de trier la liste des membres
		}
		catch (Exception $e) {
			$memberModel = null;
		}

		return $memberModel !== null && $memberModel::where('user_id', $user_id)->exists() > 0;
	}

	protected static function isOwner($model, $user_id) {
		return $model !== null && $model->user_id === $user_id;
	}
}
