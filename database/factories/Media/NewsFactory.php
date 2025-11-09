<?php

namespace Database\Factories\Media;

use App\Models\Media\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media\News>
 */
class NewsFactory extends Factory
{

    protected $usersId;

    public function __construct($count = null,
                                ?Collection $states = null,
                                ?Collection $has = null,
                                ?Collection $for = null,
                                ?Collection $afterMaking = null,
                                ?Collection $afterCreating = null,
        $connection = null,
                                ?Collection $recycle = null,
                                bool $expandRelationships = true)
    {
        $this->usersId = DB::table('users')->pluck('id');
        parent::__construct(    $count,
            $states,
            $has,
            $for,
            $afterMaking,
            $afterCreating,
            $connection,
            $recycle,
            $expandRelationships);
    }
    public function definition(): array
    {
        return [
            'title' => 'News ' .'-'. now()->timestamp . '_' . fake()->words(2, true),
            'content' => fake()->text(),
            'active' => fake()->boolean(),
            'detail_picture' => fake()->imageUrl(640, 480, 'news', true),
            'preview_picture' => fake()->imageUrl(800, 600, 'news', true),
            'author_id' => $this->usersId->random(),
        ];
    }

    public function withTags($count = 3): static
    {
        return $this->afterCreating(function ($news) use ($count) {
            $tags = Tag::inRandomOrder()->take($count)->get();
            if ($tags->count() < $count) {
                $needed = $count - $tags->count();
                $newTags = Tag::factory()->count($needed)->create();
                $tags = $tags->merge($newTags);
            }
            $news->tags()->attach($tags);
        });
    }
}
