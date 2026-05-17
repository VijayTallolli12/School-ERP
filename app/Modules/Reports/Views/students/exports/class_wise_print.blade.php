@extends("Reports::reports_layout")

@section("title", "Class-wise Student Report - Print")
@section("report_title", "Class-wise Student Report")

@section("content")
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Class</th>
                <th>Total Students</th>
                <th>Male</th>
                <th>Female</th>
                <th>Active</th>
                <th>Inactive</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td>{{ $item->class_name }}</td>
                    <td>{{ $item->total_students }}</td>
                    <td>{{ $item->male_count }}</td>
                    <td>{{ $item->female_count }}</td>
                    <td>{{ $item->active_count }}</td>
                    <td>{{ $item->inactive_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@push("scripts")
<script>
    window.print();
</script>
@endpush