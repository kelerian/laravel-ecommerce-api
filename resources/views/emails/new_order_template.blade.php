<!DOCTYPE html>
<html>
<head>
    <style>
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .welcome { color: #2c5aa0; font-size: 24px; }
    </style>
</head>
<body>
<div class="container">
    <h1 class="welcome">Здравствуйте, {{ $order->email }}!</h1>

    <p>Вы оформили заказ на сайте {{$siteUrl}}.</p>
    <p>Заказ в статусе: {{$order->orderStatus->title}}.</p>
    <p>Тип оплаты: {{$order->payType->title}}.</p>
    <p>Стоимость заказа: {{$order->final_price}}.</p>
    @foreach($order->orderItem as $product)
        <div class="order_item">
            <p>Название товара: {{ $product->product_name }}</p>
            <p>Стомиость: {{ $product->price }}</p>
            <p>Количество: {{ $product->quantity }}</p>
        </div>
    @endforeach
</div>
</body>
</html>
