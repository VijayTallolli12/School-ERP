@extends('modules.reports.reports_layout')

@section('title', $title . ' - Print')
@section('report_title', $title)

@section('content')
    <table class="table table-bordered table-striped mt-4">
        <thead class="table-light">
            <tr>
                @if(!empty($data))
                    @foreach(array_keys((array)$data[0]) as $col)
                        <th>{{ ucwords(str_replace('_', ' ', $col)) }}</th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    @foreach((array)$row as $val)
                        <td>{{ $val ?? '-' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@push('scripts')
<script>
    window.onload = function() { window.print(); }
</script>
@endpush