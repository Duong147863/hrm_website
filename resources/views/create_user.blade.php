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
    <form action="/create_user" method="post">
        @csrf
        <label for="Name">
            Name:
            <input type="text" name="username">
        </label><br><br>
        <label for="Password">
            Password:
            <input type="text" name="password">
        </label><br><br>
        <label for="Password">
            Permission:
            <input type="text" name="permission">
        </label><br><br>
        <label for="Password">
            Status:
            <input type="text" name="account_status">
        </label><br><br>
        <button type="submit">Create user</button>
    </form>
</body>

</html>
