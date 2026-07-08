<!DOCTYPE html>
<html>
<head>
    <title>Student Directory Report</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Student Directory Report</h1>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Admission No</th>
                <th>Class & Section</th>
                <th>Gender</th>
                <th>Guardian</th>
                <th>Contact</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student->full_name }}</td>
                    <td>{{ $student->admission_no }}</td>
                    <td>{{ $student->class_section }}</td>
                    <td>{{ ucfirst($student->gender) }}</td>
                    <td>{{ $student->guardian_name }}</td>
                    <td>{{ $student->guardian_phone }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
