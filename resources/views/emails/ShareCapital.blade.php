<!DOCTYPE html>
<html>

<head>

    <style>
        ul {
            margin: 0 0 0 0;
            padding: 0;
        }
    </style>
</head>

<body>
    {{-- <h2>Congratulations, {{ $user->first_name }}!</h2>
    <p>Your membership in the cooperative has been <strong>approved</strong>.</p>
    <p>You can now log in to your account.</p> --}}

    <h2>Hello, {{ $user->fullname }}!</h2>
    <p>Your membership has been approved. Please complete your share capital contribution to fully activate your
        account.</p>

    <a href="{{ route('share_capital.show', ['id' => $user->id]) }}">
        Click here to complete your Share Capital
    </a>
</body>

</html>