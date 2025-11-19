<?php

namespace App\Services\Media;

use App\Models\Media\News;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NewsListQuery
{

    private $query;

    public function __construct()
    {
        $this->query = News::query()->select(
            'news.id',
            'news.created_at',
            'news.active',
            'news.preview_picture',
            'news.slug',
            'news.title',
            'news.author_id',
        )
        ->with([
            'tags' => function ($query){
                $query->select('tags.id', 'tags.slug','tags.title');
            },
            'author' => function ($query){
                $query->select('id','email','name');
            }
        ]);
    }

    public function filterByActive(): self
    {
        $this->query->getActive();
        return $this;
    }

    public function filterByDateRange(string|bool $dateFrom, string|bool $dateTo): self
    {
        if ($dateFrom) {
            $this->query->where('news.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $this->query->where('news.created_at', '<=', $dateTo);
        }
        return $this;
    }

    public function filterByEmail(string|bool $email): self
    {
        if ($email) {
            $this->query->whereHas('author',function ($q) use($email){
                $q->where('email',$email);
            });
        }
        return $this;
    }

    public function filterByTag(array|bool $tags, bool $tagsFlag): self
    {
        if ($tags) {
            $this->query->join('news_tags as nt', 'news.id', '=', 'nt.news_id')
                ->join('tags as t', 't.id', '=', 'nt.tag_id');
            if ($tagsFlag) {
                $this->query
                    ->whereIn('t.slug',$tags)
                    ->groupBy('news.id')
                    ->havingRaw('COUNT(DISTINCT t.slug) = ?',[count($tags)])
                    ->havingRaw('COUNT(DISTINCT t.slug) = (
                            SELECT COUNT(DISTINCT t2.slug)
                            FROM news_tags nt2
                            JOIN tags t2 ON t2.id = nt2.tag_id
                            WHERE nt2.news_id = news.id
                            )');
            } else {
                $this->query
                    ->whereIn('t.slug',$tags);
            }
        }
        return $this;
    }

    public function paginateWithSort(int $limit, string $sort, string $direction): LengthAwarePaginator
    {
        return $this->query
            ->orderBy($sort, $direction)
            ->distinct()
            ->paginate($limit);
    }
}
