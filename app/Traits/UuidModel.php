<?php

namespace App\Traits;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Trait UuidModel
 * @package App\Traits
 */
trait UuidModel
{
    public function getObjectType()
    {
        $cn = explode("\\", get_class());
        return strtolower($cn[count($cn) - 1]);
    }

    public function getObjectPrefix()
    {
        return $this->uuidPrefix ?? substr($this->getObjectType(), 0, 3);
    }

    /**
     * Binds creating/saving events to create UUIDs (and also prevent them from being overwritten).
     *
     * @return void
     */
    public static function bootUuidModel()
    {
        static::creating(function ($model) {
            // Don't let people provide their own UUIDs, we will generate a proper one.
            // ensure uuid is unique
            $unique = false;
            do {
                $uuid = substr($model->getObjectPrefix() . '_' . str_replace('-', '', Uuid::uuid4()->toString()), 0, 18);
                $unique = !static::findByUuid($uuid);
            } while (!$unique);
            $model->uuid = $uuid;
        });

        static::saving(function ($model) {
            // What's that, trying to change the UUID huh?  Nope, not gonna happen.
            $original_uuid = $model->getOriginal('uuid');

            if ($original_uuid !== $model->uuid) {
                $model->uuid = $original_uuid;
            }
        });
    }

    /**
     * Find a model by its uuid.
     * 
     * @param string $uuid  The UUID of the model.
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public static function findByUuid($uuid)
    {
        $instance = new static;

        return $instance->newQuery()->where('uuid', $uuid)->first();
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
