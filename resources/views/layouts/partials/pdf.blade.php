<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>
    <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>

    <table>
        <thead>
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
</body>
</html>