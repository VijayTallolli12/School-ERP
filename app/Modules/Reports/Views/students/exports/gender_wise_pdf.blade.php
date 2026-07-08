<!DOCTYPE html>
<html>
<head>
    <title>Gender-wise Student Report</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Gender-wise Student Report</h1>
    <table>
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
</body>
</html>
