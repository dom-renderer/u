@if(!empty($data))
    <table>
        <thead>
            <tr>
                <th> Task Code </th>
                <th> Task Date </th>
                <th> Location </th>
                <th> DoM </th>
                <th> Title </th>
                <th> Remarks </th>
                <th> Status </th>
            </tr>
        </thead>
    </table>
    @forelse ($data as $row)
        <tr>
            <td> {{ $row['task_code'] }} </td>
            <td> {{ date('d-m-Y', strtotime($row['task_date'])) }} </td>
            <td> {{ $row['store_name'] }} - {{ $row['store_code'] }} </td>
            <td> {{ $row['dom'] }} </td>
            <td> {{ $row['title'] }} </td>
            <td> {{ $row['remarks'] }} </td>
            <td> {{ $row['status'] }} </td>
        </tr>
    @empty
        <td colspan="7"> Looks like there is no re-dos </td>
    @endforelse
@endif