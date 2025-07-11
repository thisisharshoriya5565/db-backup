@extends('layouts.app')

@section('content')
    <div class="bg-green-100 p-6 rounded shadow text-green-800">
        ✅ Backup <strong>{{ $folder }}</strong> restored successfully.
    </div>
    <a href="{{ url('/backup/list') }}" class="mt-6 inline-block text-blue-600 hover:underline">← Back to Backup List</a>
@endsection
