<?php

namespace Tests\Feature;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CartTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser(): User
    {
        return User::factory()
            ->withFuser()
            ->withPlusUser()
            ->withTwoProfiles()
            ->create([
                'email' => 'test'.time().'@mail.ru',
                'password' => 'Password123!'
            ]);
    }

    private function getCartProducts(): array
    {
        $response = $this->get('/api/v1/catalog?limit=5');
        $response->assertStatus(200);

        $catalog = $response->json('data.data') ?? $response->json('data');
        $products = [];

        foreach ($catalog as $product) {
            if (count($products) >= 2) break;

            $hasStock = !empty($product['stocks']) && collect($product['stocks'])->sum('quantity') > 0;
            if ($hasStock) {
                $products[] = [
                    'id' => $product['id'],
                    'quantity' => 1
                ];
            }
        }

        if (empty($products)) {
            $this->markTestSkipped('No available products');
        }

        return $products;
    }

    private function getAuthHeaders(User $user): array
    {
        return [
            'fuser' => $user->cart->fuser_id,
            'Authorization' => 'Bearer ' . $user->createToken('test')->plainTextToken,
            'accept' => 'application/json'
        ];
    }

    private function getFuserHeaders(): array
    {
        return [
            'fuser' => strval(time()),
            'accept' => 'application/json'
        ];
    }

    public function test_get_empty_cart(): void
    {
        $response = $this->withHeaders($this->getFuserHeaders())->getJson('/api/v1/cart');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success', 'message', 'data' => [
                    'id', 'updated_at', 'product_count', 'calculate_prices', 'products'
                ]
            ])
            ->assertJsonPath('data.products', []);
    }

    public function test_add_products_to_cart(): void
    {
        $headers = $this->getFuserHeaders();
        $products = $this->getCartProducts();

        $response = $this->withHeaders($headers)
            ->postJson('/api/v1/cart', ['products' => $products]);

        $response->assertStatus(200);

        $cart = $this->withHeaders($headers)->getJson('/api/v1/cart')->json('data');
        $this->assertCount(count($products), $cart['products']);
    }

    public function test_update_product_quantity(): void
    {
        $headers = $this->getFuserHeaders();
        $products = $this->getCartProducts();

        $installedQuantity = 5;

        $this->withHeaders($headers)
            ->postJson('/api/v1/cart', ['products' => $products]);

        $updateData = ['products' => [
            ['id' => $products[0]['id'], 'quantity' => $installedQuantity]
        ]];

        $response = $this->withHeaders($headers)->postJson('/api/v1/cart', $updateData);
        $response->assertStatus(200);

        $cart = $this->withHeaders($headers)->getJson('/api/v1/cart')->json('data');
        $updatedProduct = collect($cart['products'])->firstWhere('id', $products[0]['id']);

        $this->assertEquals($installedQuantity, $updatedProduct['quantity']);
    }

    public function test_remove_product_from_cart(): void
    {
        $headers = $this->getFuserHeaders();
        $products = $this->getCartProducts();

        $this->withHeaders($headers)
            ->postJson('/api/v1/cart', ['products' => $products]);

        $response = $this->withHeaders($headers)
            ->deleteJson('/api/v1/cart', [
                'products' => [['id' => $products[0]['id']]]
            ]);

        $response->assertStatus(200);

        $cart = $this->withHeaders($headers)->getJson('/api/v1/cart')->json('data');
        $remainingIds = collect($cart['products'])->pluck('id')->toArray();

        $this->assertNotContains($products[0]['id'], $remainingIds);
    }

    public function test_clear_cart(): void
    {
        $headers = $this->getFuserHeaders();
        $products = $this->getCartProducts();

        $this->withHeaders($headers)
            ->postJson('/api/v1/cart', ['products' => $products]);

        $response = $this->withHeaders($headers)
            ->deleteJson('/api/v1/cart', [
                'products' => [['id' => $products[0]['id']]],
                'all' => 'true'
            ]);

        $response->assertStatus(200);

        $cart = $this->withHeaders($headers)->getJson('/api/v1/cart')->json('data');
        $this->assertEmpty($cart['products']);
    }

    public function test_cart_price_calculation(): void
    {
        $headers = $this->getFuserHeaders();
        $products = $this->getCartProducts();

        $this->withHeaders($headers)
            ->postJson('/api/v1/cart', ['products' => $products]);

        $cart = $this->withHeaders($headers)->getJson('/api/v1/cart')->json('data');

        $this->assertArrayHasKey('calculate_prices', $cart);
        $this->assertNotEmpty($cart['calculate_prices']);

        $selected = collect($cart['calculate_prices'])->where('selected', true);
        $this->assertCount(1, $selected);
    }

    public function test_cart_validation(): void
    {
        $headers = $this->getFuserHeaders();

        $response = $this->withHeaders($headers)
            ->postJson('/api/v1/cart', [
                'products' => [
                    ['id' => 'invalid', 'quantity' => 1],
                    ['id' => 1, 'quantity' => -123]
                ]
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products.0.id', 'products.1.quantity']);
    }

    public function test_authenticated_user_cart(): void
    {
        $user = $this->createUser();
        $headers = $this->getAuthHeaders($user);

        $response = $this->withHeaders($headers)->getJson('/api/v1/cart');
        $response->assertStatus(200);
    }
}
