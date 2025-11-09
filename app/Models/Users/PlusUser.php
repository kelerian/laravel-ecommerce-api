<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlusUser extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'gender_id',
        'address',
        'birthday',
        'lastname',
        'gender_slug'
    ];
    //отключили автоматическое добавление полей при создании.

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class, 'gender_id','id');
    }


    public function genderSlug(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->gender->slug,
            set: function ($value) {
                $gender = Gender::where('slug', $value)->firstOrFail();
                return ['gender_id' => $gender->id];
            });
    }

}
