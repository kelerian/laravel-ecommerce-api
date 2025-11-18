<?php

namespace App\Services\Products;

use App\Models\Products\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductsListQuery
{
    private $query;
    public function __construct(
        private ProductService $productServ
    )
    {
        $this->query = Product::query()
            ->with([
                'priceName' ,
                'stocks' => function($query){
                    $query->where(
                        'is_active', '=', true
                    );
                },
                'images'
            ])
            ->select('products.*');
    }

    public function sortByPrice(string $direction): self
    {
        $priceCollect = $this->productServ->getPriceNameCollect();
        $roznPriceId= $priceCollect['rozn']->id;
        $this->query
            ->join('prices', function($join) use ($roznPriceId) {
            $join->on('products.id','=','prices.product_id')
                ->where('prices.price_name_id',$roznPriceId);
            })
            ->addSelect(
                'prices.price',
            )
            ->orderBy('prices.price', $direction);
        return $this;
    }

    public function sortByDate(string $direction): self
    {
        $this->query->orderBy('created_at',$direction);

        return $this;
    }

    public function sortByStock(string $direction): self
    {

        $this->query->join('stocks', function ($join){
            $join->on('products.id','=','stocks.product_id');
        })
            ->addSelect(
                \DB::raw('SUM(stocks.quantity) as total_quantity'))
            ->groupBy('products.id')
            ->orderBy('total_quantity', $direction);

        return $this;
    }

    public function applySort(string $sort, string $direction): self
    {
        match($sort) {
            'price' => $this->sortByPrice($direction),
            'stock' => $this->sortByStock($direction),
            'date' => $this->sortByDate($direction),
        };
        return $this;
    }

    public function paginate(int $limit): LengthAwarePaginator
    {
        return $this->query->paginate($limit);
    }

}
