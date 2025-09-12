<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Usuario</th>
            <th>Tipo</th>
            <th>Acción</th>
            <th>ID Modelo</th>
            <th>Cambios</th>
        </tr>
    </thead>
    <tbody>
        @foreach($changeLogs as $log)
            <tr>
                <td>{{ $log->id }}</td>
                <td>{{ $log->created_at }}</td>
                <td>{{ $log->user ? $log->user->name : 'Usuario eliminado' }}</td>
                <td>{{ $log->model_type }}</td>
                <td>{{ $log->action }}</td>
                <td>{{ $log->model_id }}</td>
                <td>{{ json_encode($log->changes, JSON_UNESCAPED_UNICODE) }}</td>
            </tr>
        @endforeach
    </tbody>
</table> 