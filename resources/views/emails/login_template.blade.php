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
    <h1 class="welcome">Здравствуйте, {{ $user->name }}!</h1>

    <p>Вы авторизовались на сайте {{$siteUrl}}.</p>
</div>
</body>
</html>
