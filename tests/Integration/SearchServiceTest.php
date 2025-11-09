<?php

namespace Tests\Integration;

use App\Dto\Search\SearchDto;
use App\Models\Products\Product;
use App\Services\Search\SearchService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SearchServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_searches_products_by_title(): void
    {

        $product = Product::factory()->create(['title' => 'Unique Product Name']);

        $searchDto = new SearchDto(
            q: 'Unique Product',
            model: Product::class,
            searchType: 'fulltext',
            page: 1,
            perPage: 10
        );

        $service = new SearchService();

        $results = $service->search($searchDto);

        $this->assertNotEmpty($results);
        $this->assertStringContainsString('Unique Product', $results->first()['content']['title'] ?? '');
    }

    public function test_returns_empty_for_nonexistent_product(): void
    {
        $searchDto = new SearchDto(
            q: 'Nonexistent Product 12345',
            model: Product::class,
            searchType: 'fulltext',
            page: 1,
            perPage: 10
        );

        $service = new SearchService();

        $results = $service->search($searchDto);

        $this->assertEmpty($results->items());
    }
}
