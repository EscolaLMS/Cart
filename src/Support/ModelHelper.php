<?php

namespace EscolaLms\Cart\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class ModelHelper
{
    public static function hasRelation(Model $model, string $key)
    {
        if ($model->relationLoaded($key)) {
            return true;
        }

        if (method_exists($model, $key)) {
            return is_a($model->$key(), Relation::class);
        }

        return false;
    }
}
