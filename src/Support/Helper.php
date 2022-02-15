<?php

namespace EscolaLms\Cart\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Helper
{
    public static function hasRelation(Model $model, string $key)
    {
        // If the key already exists in the relationships array, it just means the
        // relationship has already been loaded, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($model->relationLoaded($key)) {
            return true;
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (method_exists($model, $key)) {
            //Uses PHP built in function to determine whether the returned object is a laravel relation
            return is_a($model->$key(), Relation::class);
        }

        return false;
    }
}
