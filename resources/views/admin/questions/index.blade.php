@extends('layouts.app')

@section('title', 'Manage Questions')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h2>Questions</h2>
        <a href="{{ route('questions.create') }}" class="btn btn-primary mb-3">Add New Question</a>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Question</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($questions as $question)
                    <tr>
                        <td>{{ $question->id }}</td>
                        <td>{{ $question->question }}</td>
                        <td>
                            <a href="{{ route('questions.edit', $question->id) }}" class="btn btn-warning">Edit</a>
                            <form action="{{ route('questions.destroy', $question->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
