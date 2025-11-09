<?php

namespace App\Services\Search;

use App\Dto\Search\SearchDto;
use App\Models\Media\News;
use App\Models\Products\Product;
use Elastic\ScoutDriverPlus\Support\Query;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService
{
    private const FRAGMENT_SIZE = 30;
    private const FIELDS = [
        News::class => [
            'autocomplete' => 'title.autocomplete',
            'fulltext' => [
                'title^3',
                'content^2',
                'tags_title^1.5',
                'author^1',
                'slug^1'
            ],
            'trigram' => [
                'title.trigram^2',
                'content.trigram^1'
            ],
            'body' => 'content',
            'title' => 'title'
        ],
        Product::class => [
            'autocomplete' => 'title.autocomplete',
            'fulltext' => [
                'title^3',
                'description^2',
                'slug^1'
            ],
            'trigram' => [
                'title.trigram^2',
                'description.trigram^1'
            ],
            'body' => 'description',
            'title' => 'title'
        ]
    ];

    public function search(SearchDto $searchDto)
    {
        return match($searchDto->searchType) {
            'autocomplete' => $this->autocompleteSearch($searchDto),
            'fulltext' => $this->fulltextSearch($searchDto),
            default => $this->fulltextSearch($searchDto),
        };
    }

    private function autocompleteSearch(SearchDto $searchDto)
    {
        $field = self::FIELDS[$searchDto->model]['autocomplete'];

        $query = Query::match()
            ->field($field)
            ->query($searchDto->q);

        $results = $searchDto->model::searchQuery($query)
            ->size(15)
            ->source(['title', 'id', 'slug'])
            ->execute();

        return $results->hits()->map(function ($hit) {
            $document = $hit->document()->content();
            return [
                'id' => $document['id'],
                'title' => $document['title'],
                'slug' => $document['slug'],
            ];
        });
    }

    private function fulltextSearch(SearchDto $searchDto)
    {
        $fields = self::FIELDS[$searchDto->model]['fulltext'];
        $title = self::FIELDS[$searchDto->model]['title'];
        $body = self::FIELDS[$searchDto->model]['body'];

        $fulltextQuery = Query::multiMatch()
            ->fields($fields)
            ->query($searchDto->q)
            ->type('best_fields')
            ->fuzziness('AUTO');

        $fulltextResult =  $searchDto->model::searchQuery($fulltextQuery)
            ->from(($searchDto->page - 1) * $searchDto->perPage)
            ->size($searchDto->perPage)
            ->highlight($title)
            ->highlight($body, ['fragment_size' => self::FRAGMENT_SIZE])
            ->sort('_score', 'desc')
            ->sort('created_at_timestamp', 'desc')
            ->execute();

        if ($searchDto->page === 1 && $fulltextResult->hits()->count() < 3) {
            return $this->mergeWithTrigram($fulltextResult, $searchDto);
        }

        return $this->createPaginator($fulltextResult, $searchDto);
    }


    private function mergeWithTrigram($fulltextResult, SearchDto $searchDto)
    {

        $fields = self::FIELDS[$searchDto->model]['trigram'];
        $title = self::FIELDS[$searchDto->model]['title'];
        $body = self::FIELDS[$searchDto->model]['body'];

        $existingIds = $fulltextResult->hits()->map(function ($hit) {
            return $hit->document()->content()['id'];
        })->toArray();

        $trigramQuery = Query::multiMatch()
            ->fields($fields)
            ->query($searchDto->q)
            ->type('best_fields')
            ->fuzziness('AUTO');

        $trigramResult =  $searchDto->model::searchQuery($trigramQuery)
            ->size($searchDto->perPage)
            ->highlight($title)
            ->highlight($body, ['fragment_size' => self::FRAGMENT_SIZE])
            ->sort('_score', 'desc')
            ->execute();

        $allHits = $fulltextResult->hits();

        foreach ($trigramResult->hits() as $hit) {
            $document = $hit->document()->content();

            if (!in_array($document['id'], $existingIds)) {
                $allHits->push($hit);
                $existingIds[] = $document['id'];

                if ($allHits->count() >= $searchDto->perPage) {
                    break;
                }
            }
        }

        $items = $allHits->map(function ($hit) {
            return [
                'content' => $hit->document()->content(),
                'highlight' => $hit->highlight()?->raw()
            ];
        });

        return new LengthAwarePaginator(
            $items,                 // данные для текущей страницы
            $allHits->count(),      // всего результатов
            $searchDto->perPage,
            $searchDto->page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    private function createPaginator($searchResult, SearchDto $searchDto)
    {
        $items = $searchResult->hits()->map(function ($hit) {
            return [
                'content' => $hit->document()->content(),
                'highlight' => $hit->highlight()?->raw()
            ];
        });

        return new LengthAwarePaginator(
            $items,                     // данные для текущей страницы
            $searchResult->total(),     // всего результатов
            $searchDto->perPage,
            $searchDto->page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
