<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h2>Create user</h2>
    <form action="/update{{ $account->account_id }}" method="post">
        @csrf
        <label for="Name">
            Name:
            <input type="text" name="username" value="{{ $account->username }}">
        </label><br><br>

        <label for="Password">
            Password:
            <input type="text" name="password">
        </label><br><br>
        <button type="submit">Edit user</button>
    </form>

</body>

</html>
