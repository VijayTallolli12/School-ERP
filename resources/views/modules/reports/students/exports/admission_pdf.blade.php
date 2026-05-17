<!DOCTYPE html>
<html>
<head>
    <title>Admission Report - PDF</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h2>Admission Report</h2>
    <table>
        <thead>
            <tr>
                <th>Admission Date</th>
                <th>Student Name</th>
                <th>Admission No</th>
                <th>Class & Section</th>
                <th>Guardian</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td>{{ $item->admission_date }}</td>
                    <td>{{ $item->student_name }}</td>
                    <td>{{ $item->admission_no }}</td>
                    <td>{{ $item->class_section }}</td>
                    <td>{{ $item->guardian }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
