<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #333; padding: 20px; }
        h2 { text-align: center; margin-bottom: 5px; }
        .meta { text-align: center; color: #666; font-size: 13px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2563eb; color: #fff; padding: 8px 6px; text-align: left; font-size: 11px; }
        td { padding: 6px; border-bottom: 1px solid #ddd; font-size: 11px; }
        tr:nth-child(even) { background: #f9f9f9; }
        .footer { text-align: center; font-size: 10px; color: #999; margin-top: 20px; }
        .no-print { display: block; text-align: center; margin-bottom: 15px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary" style="padding:8px 20px;cursor:pointer;background:#2563eb;color:#fff;border:none;border-radius:4px;">Print Document</button>
    </div>
    <h2>{{ $title }}</h2>
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
    <div class="footer">{{ config('app.name') }} &mdash; Transport Module Report</div>
    <script>setTimeout(() => window.print(), 500);</script>
</body>
</html>
