@extends("Reports::reports_layout")

@section("title", "Gender-wise Student Report - Print")
@section("report_title", "Gender-wise Student Report")

@section("content")
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Class & Section</th>
                <th>Male</th>
                <th>Female</th>
                <th>Other</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->class_section }}</td>
                    <td>{{ $item->male }}</td>
                    <td>{{ $item->female }}</td>
                    <td>{{ $item->other }}</td>
                    <td>{{ $item->total }}</td>
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
