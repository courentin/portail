<?php
/**
 * Ajoute une association clé/valeur.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2018, SiMDE-UTC
 * @license GNU GPL-3.0
 */

namespace App\Traits\Model;

use Illuminate\Database\Eloquent\Builder;
use App\Exceptions\PortailException;

trait HasKeyValue
{
    /**
     * Sélecteur de clé.
     *
     * @param  Builder $query
     * @param  string  $key
     * @return mixed
     */
    public function scopeWhereKey(Builder $query, string $key)
    {
        if (method_exists($this, $key)) {
            throw new PortailException('Impossible de récupérer ou de modifier la donnée');
        }

        $query = $query->where('key', strtoupper($key));

        if ($query->count() > 0) {
            return $query;
        } else {
            throw new PortailException('Non trouvé', 404);
        }
    }

    /**
     * Sélecteur de clé.
     *
     * @param  Builder $query
     * @param  string  $key
     * @return mixed
     */
    public function scopeKey(Builder $query, string $key)
    {
        $model = $this->scopeWhereKey($query, $key)->first();

        if ($model) {
            return $model;
        } else {
            throw new PortailException('Non trouvé', 404);
        }
    }

    /**
     * Sélecteur de valeur via la clé.
     *
     * @param  Builder $query
     * @param  string  $key
     * @return mixed
     */
    public function scopeValueOf(Builder $query, string $key)
    {
        $key = strtoupper($key);

        if (method_exists($this, $key)) {
            return $this->$key($query);
        }

        return $this->scopeKey($query, $key)->value;
    }

    /**
     * Sélecteur indiquant si la clé est une méthode.
     *
     * @param  Builder $query
     * @param  string  $key
     * @return mixed
     */
    public function scopeKeyIsFunction(Builder $query, string $key)
    {
        return method_exists($this, $key);
    }

    /**
     * Sélecteur indiquant si la clé est dans la base de donnée.
     *
     * @param  Builder $query
     * @param  string  $key
     * @return mixed
     */
    public function scopeKeyExistsInDB(Builder $query, string $key)
    {
        return $query->where('key', strtoupper($key))->exists();
    }

    /**
     * Sélecteur indiquant si la clé existe.
     *
     * @param  Builder $query
     * @param  string  $key
     * @return mixed
     */
    public function scopeKeyExists(Builder $query, string $key)
    {
        return $this->scopeKeyIsFunction($query, $key) || $this->scopeKeyExistsInDB($query, $key);
    }

    /**
     * Sélecteur indiquant si la clé existe.
     *
     * @param  Builder $query
     * @param  string  $key
     * @return mixed
     */
    public function scopeToArray(Builder $query, string $key=null)
    {
        if ($key) {
            return [
                strtolower($key) => $this->scopeValueOf($query, $key),
            ];
        } else {
            $collection = [];

            foreach ($query->get(['key', 'value', 'type']) as $keyValue) {
                $collection[strtolower($keyValue->key)] = $keyValue->value;
            }

            return $collection;
        }
    }

    /**
     * Sélecteur permettant de retourner tout en tableau.
     *
     * @param  Builder $query
     * @return array
     */
    public function scopeAllToArray(Builder $query)
    {
        $data = $this->scopeToArray($query);

        if (property_exists($this, 'functionalKeys')) {
            foreach ($this->functionalKeys as $key) {
                if (method_exists($this, $key)) {
                    try {
                        $data[$key] = $this->scopeValueOf($query, $key);
                    } catch (PortailException $e) {
                        $data[$key] = null;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Sélecteur permettant de retourner à partir des groupes tout en tableau.
     *
     * @param  Builder $query
     * @return array
     */
    public function scopeGroupToArray(Builder $query)
    {
        $data = $query->get();
        $groups = [];

        foreach ($data as $model) {
            if (!isset($groups[strtolower($model->key)])) {
                $groups[strtolower($model->key)] = [];
            }

            $groups[strtolower($model->key)][] = $model->value;
        }

        return $groups;
    }

    /**
     * Casting automatique des données.
     *
     * @param  mixed $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($key === 'value') {
            switch ($this->type) {
                case 'STRING':
                    return $value;

                case 'INTEGER':
                    return (int) $value;

                case 'DOUBLE':
                    return (double) $value;

                case 'BOOLEAN':
                    return (boolean) $value;

                case 'ARRAY':
                    return json_decode($value, true);

                case 'DATETIME':
                    return \Carbon\Carbon::parse($value);

                default:
                    return null;
            }
        }

        return $value;
    }

    /**
     * Casting automatique des données.
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if ($key === 'value') {
            switch (gettype($value)) {
                case 'string':
                    if (\DateTime::createFromFormat('Y-m-d H:i:s', $value)
                        || \DateTime::createFromFormat('Y-m-d', $value)
                        || \DateTime::createFromFormat('H:i:s', $value)) {
                        $value = \Carbon\Carbon::parse($value);
                    } else {
                        $type = 'STRING';
                    }
                    break;

                case 'integer':
                    $type = ($type ?? 'INTEGER');
                case 'double':
                    $type = ($type ?? 'DOUBLE');
                case 'boolean':
                    $type = ($type ?? 'BOOLEAN');
                    $value = (string) $value;
                    break;

                case 'array':
                    $type = ($type ?? 'ARRAY');
                    $value = json_encode($value);
                    break;

                default:
                       $type = 'NULL';
            }

            if ($value instanceof \Carbon\Carbon) {
                $type = 'DATETIME';
            } else if (!isset($type)) {
                $type = 'STRING';
            }

            parent::setAttribute('type', $type);
        } else if ($key === 'key') {
            $value = strtoupper($value);

            if (method_exists($this, $value)) {
                throw new PortailException("Cette information ne peut pas être créée");
            }
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Conversion en array.
     *
     * @param  mixed $one
     * @return array
     */
    public function toArray($one=0)
    {
        if ($one) {
            return [
                strtolower($this->key) => $this->value,
            ];
        } else {
            $array = parent::toArray();
            $array['value'] = $this->getAttribute('value');

            return $array;
        }
    }

    /**
     * Conversion en json.
     *
     * @param  mixed $options
     * @return string
     */
    public function toJson($options=0)
    {
        return json_encode($this->toArray($options));
    }

    /**
     * Relation avec l'utilisateur.
     *
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
