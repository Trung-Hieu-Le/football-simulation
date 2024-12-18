@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Danh sách các đội bóng</h2>

    <!-- Bảng liệt kê các đội bóng -->
    <table class="table table-active">
        <thead>
            <tr>
                <th>STT</th>
                <th>Tên đội</th>
                <th>Tấn công</th>
                <th>Phòng ngự</th>
                <th>Kiểm soát</th>
                <th>Thể lực</th>
                <th>Tinh thần</th>
                <th>Penalty</th>
                <th>Phong độ</th>
                <th>Tổng cộng</th>
                {{-- <th>Màu sắc</th> --}}
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
                    <td>{{ $team->defense }}</td>
                    <td>{{ $team->control }}</td>
                    <td>{{ $team->stamina }}</td>
                    <td>{{ $team->aggressive }}</td>
                    <td>{{ $team->penalty }}</td> 
                    <td>{{ $team->form }}</td>
                    <td>{{ $team->attack+$team->defense+$team->control+$team->stamina+$team->aggressive+$team->penalty+$team->form }}</td>

                    {{-- <td>
                        <div style="display: flex;">
                            <div style="background-color: {{ $team->color_1 }}; width: 20px; height: 20px; margin-right: 5px;"></div>
                            <div style="background-color: {{ $team->color_2 }}; width: 20px; height: 20px; margin-right: 5px;"></div>
                            <div style="background-color: {{ $team->color_3 }}; width: 20px; height: 20px;"></div>
                        </div>
                    </td> --}}
                    <td>
                        <!-- Nút Edit và Nút Xem lịch sử -->
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $team->id }}">Edit</button>
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#historyModal{{ $team->id }}">Lịch sử</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Render tất cả các modal bên ngoài vòng lặp -->
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
                                    <th>Tier</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($teamHistories as $history)
                                    @if ($history->team_name == $team->name)
                                        <tr>
                                            <td>{{ $history->season }}</td>
                                            <td>{{ $history->goals_scored }}</td>
                                            <td>{{ $history->goals_conceded }}</td>
                                            <td>{{ $history->goal_difference }}</td>
                                            <td>{{ $history->position }}</td>
                                            <td>{{ $history->tier }}</td>
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
                    <form action="{{ route('teams.update', $team->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel{{ $team->id }}">Chỉnh sửa đội bóng {{ $team->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <!-- Input col-6 -->
                                <div class="col-6 mb-3">
                                    <label for="name" class="form-label">Tên đội</label>
                                    <input type="text" class="form-control" name="name" value="{{ $team->name }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="attack" class="form-label">Tấn công</label>
                                    <input type="number" class="form-control" name="attack" value="{{ $team->attack }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="defense" class="form-label">Phòng ngự</label>
                                    <input type="number" class="form-control" name="defense" value="{{ $team->defense }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="control" class="form-label">Kiểm soát</label>
                                    <input type="number" class="form-control" name="control" value="{{ $team->control }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="stamina" class="form-label">Sức bền</label>
                                    <input type="number" class="form-control" name="stamina" value="{{ $team->stamina }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="speed" class="form-label">Tinh thần</label>
                                    <input type="number" class="form-control" name="aggressive" value="{{ $team->aggressive }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="penalty" class="form-label">Penalty</label>
                                    <input type="number" class="form-control" name="penalty" value="{{ $team->penalty }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="form" class="form-label">Phong độ</label>
                                    <input type="number" class="form-control" name="form" value="{{ $team->form }}" required>
                                </div>
                                <!-- Select option khu vực -->
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
                                <!-- Màu sắc -->
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
