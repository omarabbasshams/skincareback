@extends('layouts.app')

@section('title', 'Add Question')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h2>Add New Question</h2>
        <form method="POST" action="{{ route('questions.store') }}">
            @csrf
            <div class="form-group">
                <label for="question">Question:</label>
                <input type="text" id="question" name="question" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection
