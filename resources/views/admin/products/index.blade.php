@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Products</h1>
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td>
                    @if($product->image_path)
                    <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" width="100">
                    @else
                    No Image
                    @endif
                </td>
                <td>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#uploadModal-{{ $product->id }}">Upload Image</button>

                    <!-- Modal -->
                    <div class="modal fade" id="uploadModal-{{ $product->id }}" tabindex="-1" aria-labelledby="uploadModalLabel-{{ $product->id }}" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="uploadModalLabel-{{ $product->id }}">Upload Image for {{ $product->name }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <form action="{{ route('admin.products.upload', $product->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                              <div class="form-group">
                                <label for="image">Choose Image</label>
                                <input type="file" name="image" class="form-control" required>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                              <button type="submit" class="btn btn-primary">Upload</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $products->links() }}
</div>
@endsection
