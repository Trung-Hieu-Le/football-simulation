@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Danh sách Mùa giải</h2>
    <a href="{{ route('seasons.create') }}" method="GET" class="btn btn-primary mb-3">Thêm Mùa giải</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Tên Mùa giải</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($seasons as $season)
                <tr>
                    <td>{{ $season->id }}</td>
                    <td>Mùa giải {{ $season->season }}</td>
                    <td>{{ $season->created_at }}</td>
                    <td>
                        <form action="{{ route('seasons.destroy', $season->id) }}" method="POST" onsubmit="return confirmDelete()">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Xóa</button>
                        </form>
                        <form action="{{ route('seasons.groupStage', $season->id) }}" method="POST">
                            @csrf
                            <button class="btn btn-info btn-sm">Chia bảng</button>
                        </form>
                        <form action="{{ route('seasons.schedule', $season->id) }}" method="POST">
                            @csrf
                            <button class="btn btn-warning btn-sm">Tạo lịch đấu</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    function confirmDelete() {
        return confirm('Bạn có chắc chắn muốn xóa mùa giải này không?');
    }
</script>
@endsection
