<?php

namespace App\Models\Traits;


use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::creating(function (Model $model) {
            $model->generateSlug();
        });

        static::updating(function (Model $model) {
            if ($model->isDirty('name') || $model->isDirty('title')) {
                $model->generateSlug();
            }
        });
    }
    public function generateSlug(): void
    {
        $sourceField = $this->getSlugSourceField();

        if (!empty($this->{$sourceField})) {
            $this->slug = Str::slug($this->{$sourceField});
        }
    }

    protected function getSlugSourceField(): string
    {
        return $this->getAttribute('title') ? 'title' : 'name';
    }
}
