<?php

namespace App\Models\Media;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    protected $table = 'media';
    protected $guarded = [];
    protected $casts = [
        'custom_properties' => 'array',
        'generated_conversions' => 'array',
        'responsive_images' => 'array',
        'manipulations' => 'array',
        'size' => 'integer',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function getUrlAttribute(): string
    {
        return storage_path('app/'.$this->disk.'/'.$this->file_name);
    }

    public function getConversionUrlAttribute($conversionName = 'thumb'): ?string
    {
        $conversions = $this->generated_conversion ?? [];

        if (isset($conversions[$conversionName])){
            return storage_path('app/'.$this->disk.'/'.$conversions[$conversionName]);
        }
        return null;
    }

    public function getAltTextAttribute(): ?string
    {
        return $this->custom_properties['alt'] ?? null;
    }

    public function setAltTextAttribute($value): void
    {
        $properties = $this->custom_properties ?? [];
        $properties['alt'] = $value;
        $this->custom_properties = $properties;
    }
}
