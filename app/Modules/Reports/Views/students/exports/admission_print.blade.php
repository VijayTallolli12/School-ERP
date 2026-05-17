@extends("Reports::reports_layout")

@section("title", "Admission Report - Print")
@section("report_title", "Admission Report")

@section("content")
    <h4>Total Admissions: {{ $totalAdmissions }}</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Class</th>
                <th>Total Admissions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td>{{ $item->class_name }}</td>
                    <td>{{ $item->total_admissions }}</td>
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