<?php

namespace App\Models\Users;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Carts\Cart;
use App\Models\Media\Media;
use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens as Sanctum;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\Users\UserFactory> */
    use HasFactory, Notifiable, Sanctum;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function scopeWithAllRelations($query)
    {
        return $query->with([
            'gender',
            'plusUser',
            'groups',
            'cart' => function ($query) {
                $query->select( 'user_id', 'fuser_id');
        }]);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            UserGroup::class,
            'user_group_user',
            'user_id',
            'group_id'
        );
    }

    public function addToGroupBySlug(string $slug)
    {
        $group = UserGroup::where('slug', $slug)->firstOrFail();
        $this->groups()->syncWithoutDetaching($group->id);
    }

    public function hasGroup(string $slug): bool
    {
        return $this->groups()->where('slug',$slug )->exists();
    }

    public function isAdmin(): bool
    {
        return $this->groups()->where('slug','admin' )->exists();
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function plusUser(): HasOne
    {
        return $this->hasOne(PlusUser::class, 'user_id', 'id');
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class, 'user_id', 'id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function gender()
    {
        return $this->hasOneThrough(
            Gender::class,
            PlusUser::class,
            'user_id',
            'id',
            'id',
            'gender_id'
        );
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }

    public function avatars(): MorphMany
    {
        return $this->media()
            ->where('collection_name','avatars');
    }

    public function getAvatarAttribute(): Media
    {
        return $this->avatar()
            ->latest()
            ->first();
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar()->url;
    }


}
