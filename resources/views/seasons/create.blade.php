@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Thêm Mùa giải</h2>
    <form action="{{ route('seasons.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-6">
                <label for="name">Tên Mùa giải:</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-6">
                <label for="description">Mô tả:</label>
                <input type="text" name="description" class="form-control">
            </div>
        </div>
        <button class="btn btn-success mt-3">Thêm</button>
    </form>
</div>
@endsection
