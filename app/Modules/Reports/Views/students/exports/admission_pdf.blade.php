<!DOCTYPE html>
<html>
<head>
    <title>Admission Report</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Admission Report</h1>
    <h4>Total Admissions: {{ $totalAdmissions }}</h4>
    <table>
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
</body>
</html>