@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Task Details</h2>
        <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $task->title }}</h5>
            <p class="card-text">{{ $task->description ?: 'No description provided' }}</p>
            <p>
                <strong>Status:</strong>
                @if($task->completed)
                    <span class="badge bg-success">Completed</span>
                @else
                    <span class="badge bg-warning">Pending</span>
                @endif
            </p>
            <p><strong>Created at:</strong> {{ $task->created_at->format('Y-m-d H:i') }}</p>
        </div>
    </div>
@endsection
