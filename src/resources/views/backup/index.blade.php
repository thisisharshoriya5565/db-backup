@extends('db-backup::layouts.app')

@section('content')
    <ul class="space-y-4">
        @foreach ($folders as $folder)
            @php $folderName = basename($folder); @endphp

            @if ($folder === $lastFolder)
                <li class="p-4 bg-green-100 shadow-md rounded flex justify-between items-center">
                    <span class="text-gray-700 font-semibold">✅ Latest: {{ $folderName }}</span>
                    <a href="{{ url('backup/restore/' . $folderName) }}" class="text-blue-600 hover:underline">Restore Now</a>
                </li>
            @else
                <li class="p-4 bg-white shadow-md rounded flex justify-between items-center">
                    <span class="text-gray-700">📁 {{ $folderName }}</span>
                    <div>
                        <a href="{{ url('backup/distroy/' . $folderName) }}" class="text-red-500 hover:underline">Destroy</a>
                        |
                        <a href="{{ url('backup/restore/' . $folderName) }}" class="text-blue-600 hover:underline">Restore
                            Now</a>
                    </div>
                </li>
            @endif
        @endforeach
    </ul>
@endsection
