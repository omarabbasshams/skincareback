@extends('layouts.app')

@section('title', 'Edit Question')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h2>Edit Question</h2>
        <form method="POST" action="{{ route('questions.update', $question->id) }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="question">Question:</label>
                <input type="text" id="question" name="question" class="form-control" value="{{ $question->question }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection
