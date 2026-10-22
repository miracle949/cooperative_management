<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="css_folder/loading.css">

    {{-- bootstrap and tailwind link --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- font awesome cdn link --}}
    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">
</head>

<body>

    <div class="container-fluid">

        @include("components.navbar")

        <main class="p-5">
            <div class="row p-3" style="border: 1px solid rgba(0,0,0,0.3)">

                @if ($getTheUsers)

                    @foreach ($getTheUsers as $getTheUser)

                        <div class="col-lg-4">
                            <h2>{{ $getTheUser->fullname }}</h2>
                        </div>

                        <div class="col-lg-4">
                            <form method="POST" action="{{ route('approve.user', $getTheUser->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" style="background:none;border:none;padding:0;color:inherit;text-decoration:underline;cursor:pointer;">Approved</button>
                            </form>
                        </div>

                        <div class="col-lg-4">
                            <form method="POST" action="{{ route('message.user', $getTheUser->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" style="background:none;border:none;padding:0;color:inherit;text-decoration:underline;cursor:pointer;">Open Share Capital</button>
                            </form>
                        </div>

                    @endforeach

                @endif
        </main>

    </div>

</body>

</html>