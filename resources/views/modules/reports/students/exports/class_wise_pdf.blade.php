<!DOCTYPE html>
<html>
<head>
    <title>Class-Wise Student Report - PDF</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h2>Class-Wise Student Report</h2>
    <table>
        <thead>
            <tr>
                <th>Class</th>
                <th>Total Students</th>
                <th>Male</th>
                <th>Female</th>
                <th>Active</th>
                <th>Inactive</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td>{{ $item->class_name }}</td>
                    <td>{{ $item->total_students }}</td>
                    <td>{{ $item->male_count }}</td>
                    <td>{{ $item->female_count }}</td>
                    <td>{{ $item->active_count }}</td>
                    <td>{{ $item->inactive_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
