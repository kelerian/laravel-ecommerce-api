<?php

namespace App\Services\Media;

use App\Dto\News\FilterForNewsListDto;
use App\Models\Media\News;
use Illuminate\Support\Facades\Cache;

class NewsQueryService
{
    private function buildNewsListQuery(FilterForNewsListDto $dto)
    {
        return News::query()->select(
            'news.id',
            'news.created_at',
            'news.active',
            'news.preview_picture',
            'news.slug',
            'news.title',
            'news.author_id',
        )
            ->where('news.active','=',true)
            ->with([
                'tags' => function ($query){
                    $query->select('tags.id', 'tags.slug','tags.title');
                },
                'author' => function ($query){
                    $query->select('id','email','name');
                }
            ])
            ->when($dto->dateFrom, function ($query) use ($dto) {
                $query->where('news.created_at', '>=', $dto->dateFrom);
            })
            ->when($dto->dateTo, function ($query) use ($dto) {
                $query->where('news.created_at', '<=', $dto->dateTo);
            })
            ->when($dto->userEmail, function ($query) use ($dto){
                $query->whereHas('author',function ($q) use($dto){
                    $q->where('email',$dto->userEmail);
                });
            })
            ->when($dto->tags, function($query) use ($dto) {
                $query->join('news_tags as nt', 'news.id', '=', 'nt.news_id')
                    ->join('tags as t', 't.id', '=', 'nt.tag_id');
                if ($dto->tagsFlag) {
                    $query
                        ->whereIn('t.slug',$dto->tags)
                        ->groupBy('news.id')
                        ->havingRaw('COUNT(DISTINCT t.slug) = ?',[count($dto->tags)])
                        ->havingRaw('COUNT(DISTINCT t.slug) = (
                            SELECT COUNT(DISTINCT t2.slug)
                            FROM news_tags nt2
                            JOIN tags t2 ON t2.id = nt2.tag_id
                            WHERE nt2.news_id = news.id
                            )');
                } else {
                    $query
                        ->whereIn('t.slug',$dto->tags);
                }
            })
            ->orderBy($dto->sort,$dto->direction)
            ->distinct()
            ->paginate($dto->limit);
    }

    public function newsListWithFilter(FilterForNewsListDto $dto)
    {
        $cacheKey = $this->buildNewsCacheKey($dto);

        return Cache::tags(['news'])
            ->remember($cacheKey, 3600, function () use ($dto) {
                return $this->buildNewsListQuery($dto);
            });
    }
    private function buildNewsCacheKey(FilterForNewsListDto $dto): string
    {
        $keyData = $dto->toArray();

        return "news:list:" . md5(serialize($keyData));
    }

    public function getNewsDetailBySlug(string $slug)
    {
        $cacheKey = 'new:' . $slug;
        return Cache::tags(['news'])
            ->remember($cacheKey, 1800, function () use ($slug) {
                return News::with([
                    'tags',
                    'images',
                    'author',
                ])->where('slug', $slug)
                    ->where('news.active', true)
                    ->firstOrFail();
            });

    }
}
