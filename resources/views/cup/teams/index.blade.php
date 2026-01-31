@extends('cup.layouts.app')

@section('content')
<div class="container">
    <h2>Danh sách các đội bóng</h2>
    <form method="POST" action="{{ route('cup.teams.resetForm') }}">
        @csrf
        <button type="submit" class="btn btn-warning">Reset Form</button>
    </form>
    
    <table class="table table-active">
        <thead>
            <tr>
                <th>STT</th>
                <th>
                    Tên đội
                    <a href="{{ route('teams.index', ['sort' => 'name', 'direction' => 'asc']) }}">⬆️</a>
                    <a href="{{ route('teams.index', ['sort' => 'name', 'direction' => 'desc']) }}">⬇️</a>
                </th>
                <th>
                    Tấn công
                    <a href="{{ route('teams.index', ['sort' => 'attack', 'direction' => 'asc']) }}">⬆️</a>
                    <a href="{{ route('teams.index', ['sort' => 'attack', 'direction' => 'desc']) }}">⬇️</a>
                </th>
                <th>
                    Sáng tạo
                    <a href="{{ route('teams.index', ['sort' => 'creative', 'direction' => 'asc']) }}">⬆️</a>
                    <a href="{{ route('teams.index', ['sort' => 'creative', 'direction' => 'desc']) }}">⬇️</a>
                </th>
                <th>
                    Kiểm soát
                    <a href="{{ route('teams.index', ['sort' => 'control', 'direction' => 'asc']) }}">⬆️</a>
                    <a href="{{ route('teams.index', ['sort' => 'control', 'direction' => 'desc']) }}">⬇️</a>
                </th>
                <th>
                    Tốc độ
                    <a href="{{ route('teams.index', ['sort' => 'pace', 'direction' => 'asc']) }}">⬆️</a>
                    <a href="{{ route('teams.index', ['sort' => 'pace', 'direction' => 'desc']) }}">⬇️</a>
                </th>
                <th>
                    Phòng ngự
                    <a href="{{ route('teams.index', ['sort' => 'defense', 'direction' => 'asc']) }}">⬆️</a>
                    <a href="{{ route('teams.index', ['sort' => 'defense', 'direction' => 'desc']) }}">⬇️</a>
                </th>
                <th>
                    Thể lực
                    <a href="{{ route('teams.index', ['sort' => 'stamina', 'direction' => 'asc']) }}">⬆️</a>
                    <a href="{{ route('teams.index', ['sort' => 'stamina', 'direction' => 'desc']) }}">⬇️</a>
                </th>
                <th>
                    Tinh thần
                    <a href="{{ route('teams.index', ['sort' => 'mental', 'direction' => 'asc']) }}">⬆️</a>
                    <a href="{{ route('teams.index', ['sort' => 'mental', 'direction' => 'desc']) }}">⬇️</a>
                </th>
                <th>
                    Kỷ luật
                    <a href="{{ route('teams.index', ['sort' => 'discipline', 'direction' => 'asc']) }}">⬆️</a>
                    <a href="{{ route('teams.index', ['sort' => 'discipline', 'direction' => 'desc']) }}">⬇️</a>
                </th>
                <th>
                    Phong độ
                    <a href="{{ route('teams.index', ['sort' => 'form', 'direction' => 'asc']) }}">⬆️</a>
                    <a href="{{ route('teams.index', ['sort' => 'form', 'direction' => 'desc']) }}">⬇️</a>
                </th>
                <th>
                    Tổng cộng
                    <a href="{{ route('teams.index', ['sort' => 'total', 'direction' => 'asc']) }}">⬆️</a>
                    <a href="{{ route('teams.index', ['sort' => 'total', 'direction' => 'desc']) }}">⬇️</a>
                </th>
                <th>Khu vực</th>
                <th>Hành động</th>
            </tr>
        </thead>
        
        <tbody>
            @foreach ($teams as $key=>$team)
                <tr>
                    <td>{{ $key+1 }}</td>
                    <td>
                        <div style="background: linear-gradient(to right, {{ $team->color_1 }} 60%, {{ $team->color_2 }} 40%);
                                    color: {{ $team->color_3 }};
                                    text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
                                    padding: 5px; border-radius: 5px;">
                            {{ $team->name }}
                        </div>
                    </td>
                    <td>{{ $team->attack }}</td>
                    <td>{{ $team->creative }}</td>
                    <td>{{ $team->control }}</td>
                    <td>{{ $team->pace }}</td>
                    <td>{{ $team->defense }}</td>
                    <td>{{ $team->stamina }}</td>
                    <td>{{ $team->mental }}</td>
                    <td>{{ $team->discipline }}</td>
                    <td>{{ $team->form }}</td>
                    <td>{{ $team->attack + $team->creative + $team->control + $team->pace + $team->defense + $team->mental + $team->discipline + $team->stamina }}</td>
                    <td>{{ $team->region_name }}</td>
                    <td>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $team->id }}">Edit</button>
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#historyModal{{ $team->id }}">Lịch sử</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @foreach ($teams as $team)
        <!-- Modal Xem Lịch Sử -->
        <div class="modal fade" id="historyModal{{ $team->id }}" tabindex="-1" aria-labelledby="historyModalLabel{{ $team->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="historyModalLabel{{ $team->id }}">Lịch sử đội bóng {{ $team->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mùa giải</th>
                                    <th>Bàn thắng</th>
                                    <th>Bàn thua</th>
                                    <th>Hiệu số</th>
                                    <th>Vị trí</th>
                                    <th>Danh hiệu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($teamHistories as $history)
                                    @if ($history->team_name == $team->name)
                                        <tr>
                                            <td>{{ $history->season }}</td>
                                            <td>{{ $history->goal_scored }}</td>
                                            <td>{{ $history->goal_conceded }}</td>
                                            <td>{{ $history->goal_difference }}</td>
                                            <td>{{ $history->position }}-{{ $history->group }}</td>
                                            <td>{{ $history->title }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit -->
        <div class="modal fade" id="editModal{{ $team->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $team->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('cup.teams.update', $team->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel{{ $team->id }}">Chỉnh sửa đội bóng {{ $team->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="name" class="form-label">Tên đội</label>
                                    <input type="text" class="form-control" name="name" value="{{ $team->name }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="attack" class="form-label">Tấn công</label>
                                    <input type="number" class="form-control" name="attack" value="{{ $team->attack }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="creative" class="form-label">Sáng tạo</label>
                                    <input type="number" class="form-control" name="creative" value="{{ $team->creative }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="control" class="form-label">Kiểm soát</label>
                                    <input type="number" class="form-control" name="control" value="{{ $team->control }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="pace" class="form-label">Tốc độ</label>
                                    <input type="number" class="form-control" name="pace" value="{{ $team->pace }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="defense" class="form-label">Phòng ngự</label>
                                    <input type="number" class="form-control" name="defense" value="{{ $team->defense }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="stamina" class="form-label">Sức bền</label>
                                    <input type="number" class="form-control" name="stamina" value="{{ $team->stamina }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="mental" class="form-label">Tinh thần</label>
                                    <input type="number" class="form-control" name="mental" value="{{ $team->mental }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="discipline" class="form-label">Kỷ luật</label>
                                    <input type="number" class="form-control" name="discipline" value="{{ $team->discipline }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="form" class="form-label">Phong độ</label>
                                    <input type="number" class="form-control" name="form" value="{{ $team->form }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="region_id" class="form-label">Khu vực</label>
                                    <select class="form-select" name="region" required>
                                        <option value="">Chọn khu vực</option>
                                        @foreach ($regions as $region)
                                            <option value="{{ $region->id }}" {{ $team->region == $region->id ? 'selected' : '' }}>
                                                {{ $region->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="color_1" class="form-label">Màu áo</label>
                                    <input type="color" class="form-control" name="color_1" value="{{ $team->color_1 }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="color_2" class="form-label">Màu quần</label>
                                    <input type="color" class="form-control" name="color_2" value="{{ $team->color_2 }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="color_3" class="form-label">Màu số</label>
                                    <input type="color" class="form-control" name="color_3" value="{{ $team->color_3 }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection