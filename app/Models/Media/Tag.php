<?php

namespace App\Models\Media;

use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasSlug, HasFactory;

    protected $fillable = ['title','slug'];

    public function news()
    {
        return $this->belongsToMany(News::class, 'news_tags','tag_id','news_id' )
            ->withTimestamps();
    }


}
