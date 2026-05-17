@extends("modules.reports.reports_layout")

@section("title", "Admission Report - Print")
@section("report_title", "Admission Report")

@section("content")
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Admission Date</th>
                <th>Student Name</th>
                <th>Admission No</th>
                <th>Class & Section</th>
                <th>Guardian</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td>{{ $item->admission_date }}</td>
                    <td>{{ $item->student_name }}</td>
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
