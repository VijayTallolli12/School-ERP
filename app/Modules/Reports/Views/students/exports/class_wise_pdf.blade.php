<!DOCTYPE html>
<html>
<head>
    <title>Class-wise Student Report</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Class-wise Student Report</h1>
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