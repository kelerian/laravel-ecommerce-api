<?php

namespace App\Services\Media;

use App\Dto\News\FilterForNewsListDto;
use App\Models\Media\News;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class NewsQueryService
{

    public function __construct(
        private NewsListQuery $newsQueryList
    )
    {}

    public function getFilteredNewsList(FilterForNewsListDto $dto): LengthAwarePaginator
    {
        return $this->newsQueryList
            ->filterByActive()
            ->filterByDateRange($dto->dateFrom, $dto->dateTo)
            ->filterByEmail($dto->userEmail)
            ->filterByTag($dto->tags, $dto->tagsFlag)
            ->paginateWithSort($dto->limit, $dto->sort, $dto->direction);
    }
    public function getCachedFilteredNewsList(FilterForNewsListDto $dto): LengthAwarePaginator
    {
        $cacheKey = $this->buildNewsCacheKey($dto);

        return Cache::tags(['news'])
            ->remember($cacheKey, 3600, function () use ($dto) {
                return $this->getFilteredNewsList($dto);
            });
    }
    private function buildNewsCacheKey(FilterForNewsListDto $dto): string
    {
        $keyData = $dto->toArray();

        return "news:list:" . md5(serialize($keyData));
    }

    public function getCachedNewsDetailBySlug(string $slug): News
    {
        $cacheKey = 'new:' . $slug;
        return Cache::tags(['news'])
            ->remember($cacheKey, 1800, function () use ($slug) {
                return $this->getNewsDetailBySlug($slug);
            });

    }

    public function getNewsDetailBySlug(string $slug): News
    {
        return News::with([
            'tags',
            'images',
            'author',
        ])->where('slug', $slug)
            ->where('news.active', true)
            ->firstOrFail();
    }
}
