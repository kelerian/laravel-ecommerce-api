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
    <h1 class="welcome">Добро пожаловать, {{ $user->name }}!</h1>

    <p>Вы зарегистрировались в системе</p>
    <p>Дата регистрации: <strong>{{ $registrationDate }}</strong></p>
    <p>Ваш email: <strong>{{ $user->email }}</strong></p>

    <a href="{{ $siteUrl }}">Перейти на сайт</a>

    @if($user->plusUser)
        <p>Ваша фамилия: {{ $user->plusUser->lastname }}</p>
    @endif

    @if($user->profiles->count() > 0)
        <p>Ваша компания: {{ $user->profiles->first()->title }}</p>
    @endif
</div>
</body>
</html>
