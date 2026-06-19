<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; }
        h1 { text-align: center; font-size: 18px; margin-bottom: 5px; }
        .meta { text-align: center; font-size: 11px; color: #666; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2563eb; color: #fff; padding: 6px 4px; text-align: left; font-size: 9px; }
        td { padding: 4px; border-bottom: 1px solid #ddd; font-size: 9px; }
        tr:nth-child(even) { background: #f9f9f9; }
        .footer { text-align: center; font-size: 9px; color: #999; margin-top: 15px; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">Generated on {{ now()->format('d M Y H:i') }}</div>
    <table>
        <thead>
            <tr>
                @if (!empty($data[0]))
                    @foreach (array_keys($data[0]) as $header)
                        <th>{{ ucwords(str_replace('_', ' ', $header)) }}</th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="10" style="text-align:center;">No data found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">{{ config('app.name') }} &mdash; Library Module Report</div>
</body>
</html>
