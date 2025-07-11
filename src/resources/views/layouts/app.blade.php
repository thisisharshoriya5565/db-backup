<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Backup Manager' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-900 font-sans p-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">{{ $title ?? 'Backup Manager' }}</h1>
        @yield('content')
    </div>
</body>

</html>
