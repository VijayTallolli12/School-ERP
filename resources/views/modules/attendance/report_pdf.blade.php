<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        h1 { font-size: 13px; margin: 0 0 6px; }
        .meta { font-size: 8px; color: #555; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #666; padding: 3px 4px; text-align: left; }
        th { background: #e8e8e8; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">
        {{ now()->format('d-M-Y H:i') }}
        @if(!empty($filters['from_date']) || !empty($filters['to_date']))
            &mdash; {{ $filters['from_date'] ?? '…' }} to {{ $filters['to_date'] ?? '…' }}
        @endif
    </div>
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Roll</th>
                <th>Class</th>
                <th>Date</th>
                <th>Status</th>
                <th>Marked By</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                @php
                    $session = $row->student?->sessions->firstWhere('class_section_id', $row->class_section_id);
                @endphp
                <tr>
                    <td>{{ $row->student?->full_name }}</td>
                    <td>{{ $session?->roll_no ?? '-' }}</td>
                    <td>{{ $row->classSection?->schoolClass?->name }} - {{ $row->classSection?->section?->name }}</td>
                    <td>{{ $row->attendance_date?->format('d-M-Y') }}</td>
                    <td>{{ $row->status_label }}</td>
                    <td>{{ $row->markedBy?->name ?? '-' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit((string) ($row->remarks ?? ''), 60) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
