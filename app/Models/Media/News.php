<?php

namespace App\Models\Media;

use App\Models\Traits\HasSlug;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable as SearchableSc;
use Elastic\ScoutDriverPlus\Searchable as SearchableEl;

class News extends Model
{
    use HasSlug, HasFactory, SearchableEl;

    protected $fillable = [
        'title',
        'content',
        'active',
        'detail_picture',
        'preview_picture',
        'author_id'
    ];
    protected $hidden = [];

    public function toSearchableArray()
    {
        $tagsData = $this->tags->map(function ($tag){
            return [
                'id' => $tag->id,
                'title' => $tag->title,
                'slug' => $tag->slug,
            ];
        })->toArray();
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'created_at' => $this->created_at,
            'created_at_timestamp' => $this->created_at?->timestamp,
            'preview_picture' => $this->preview_picture ? Storage::url($this->preview_picture) : null,
            'content' => $this->content,
            'author' => $this->author?->name,
            'tags_title' => $this->tags->pluck('title')->toArray(),
            'tags' => $tagsData,
        ];
    }

    public function searchableWith()
    {
        return ['author', 'tags'];
    }
    public function shouldBeSearchable()
    {
        return $this->active == true;
    }
    public function searchableAs(): string
    {
        return 'news_v1';
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'news_tags','news_id','tag_id' )
            ->withTimestamps();
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class,'model');
    }

    public function images(): MorphMany
    {
        return $this->media()->where('collection_name','news_image');
    }

    public function addTagBySlug($tags): News
    {
        if (is_string($tags)) {
            $tags = [$tags];
        }

        $tagIds = Tag::whereIn('slug', $tags)->pluck('id');
        if ($tagIds->isNotEmpty()) {
            $this->tags()->sync($tagIds);
        }
        return $this;
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->with(['images', 'tags', 'author'])
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->firstOrFail();
    }



}
