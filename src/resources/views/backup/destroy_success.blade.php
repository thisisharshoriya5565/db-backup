@extends('db-backup::layouts.app')

@section('content')
    <div class="bg-red-100 p-6 rounded shadow text-red-800">
        🗑️ Backup <strong>{{ $folder }}</strong> deleted successfully.
    </div>
    <a href="{{ url('/backup/list') }}" class="mt-6 inline-block text-blue-600 hover:underline">← Back to Backup List</a>
@endsection