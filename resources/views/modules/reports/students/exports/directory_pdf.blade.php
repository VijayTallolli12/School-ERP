<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; }
        .header { text-align: center; margin-bottom: 12px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header p { margin: 3px 0; color: #666; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 3px 4px; text-align: left; font-size: 8px; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-center { text-align: center; }
        .badge-active { background: #d1e7dd; color: #0f5132; padding: 1px 5px; }
        .badge-inactive { background: #f8d7da; color: #842029; padding: 1px 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Generated on {{ date('Y-m-d H:i:s') }} | Total Students: {{ count($rows) }}</p>
    </div>

    @if(empty($rows))
        <p class="text-center">No students found for the selected criteria.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Admission No</th>
                    <th>Student Name</th>
                    <th>Class & Section</th>
                    <th>Gender</th>
                    <th>Date of Birth</th>
                    <th>Parent Name</th>
                    <th>Parent Mobile</th>
                    <th>Email</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row['admission_no'] }}</td>
                        <td>{{ $row['student_name'] }}</td>
                        <td>{{ $row['class_section'] }}</td>
                        <td>{{ $row['gender'] }}</td>
                        <td>{{ $row['date_of_birth'] }}</td>
                        <td>{{ $row['parent_name'] }}</td>
                        <td>{{ $row['parent_mobile'] }}</td>
                        <td>{{ $row['email'] }}</td>
                        <td class="text-center">
                            <span class="badge-{{ $row['status'] == 'Active' ? 'active' : 'inactive' }}">{{ $row['status'] }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
