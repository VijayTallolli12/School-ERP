@extends("modules.reports.reports_layout")

@section("title", "Student List Report - Print")
@section("report_title", "Student List Report")

@section("content")
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Admission No</th>
                <th>Class & Section</th>
                <th>Guardian</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->full_name }}</td>
                    <td>{{ $item->admission_no }}</td>
                    <td>{{ $item->class_section }}</td>
                    <td>{{ $item->guardian }}</td>
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
