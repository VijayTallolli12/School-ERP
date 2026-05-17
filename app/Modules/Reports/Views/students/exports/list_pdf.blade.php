<!DOCTYPE html>
<html>
<head>
    <title>Student List Report</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Student List Report</h1>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Admission No</th>
                <th>Class & Section</th>
                <th>Guardian</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student->user->first_name . ' ' . $student->user->last_name }}</td>
                    <td>{{ $student->admission_no }}</td>
                    <td>
                        @php
                            $session = $student->sessions->first();
                            echo $session ? $session->classSection->schoolClass->name . ' - ' . $session->classSection->section->name : '';
                        @endphp
                    </td>
                    <td>{{ $student->guardians->pluck('user.first_name')->join(', ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>