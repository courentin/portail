<?php
/**
 * Ajoute au controlleur de gérer les validateurs.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2018, SiMDE-UTC
 * @license GNU GPL-3.0
 */

namespace App\Traits\Controller\v1;

use Illuminate\Http\Request;

trait HasValidators
{
    use HasMorphs;

    /**
     * Récupère le validateur.
     *
     * @param  Request $request
     * @param  string  $modelName
     * @param  string  $modelText
     * @param  string  $verb
     * @return mixed
     */
    protected function getValidator(Request $request, string $modelName, string $modelText, string $verb='create')
    {
        return $this->getMorph($request, $modelName, $modelText, $verb, 'validated');
    }
}
