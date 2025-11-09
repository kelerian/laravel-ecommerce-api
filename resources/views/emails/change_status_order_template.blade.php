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

    <p>Оповещение с сайта {{$siteUrl}}.</p>
    <p>Изменен статус заказа: {{$order->id}}.</p>
    <p>Прежний статус: {{$oldStatus->title}}.</p>
    <p>Новый статус: {{$newStatus->title}}.</p>
</div>
</body>
</html>
