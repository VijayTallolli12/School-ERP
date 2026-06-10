<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header p { margin: 3px 0; color: #666; font-size: 10px; }
        .summary-box { width: 100%; margin-bottom: 12px; border-collapse: collapse; }
        .summary-box td { padding: 4px 8px; text-align: center; border: 1px solid #ddd; font-size: 10px; }
        .summary-box th { background-color: #f5f5f5; padding: 4px 8px; border: 1px solid #ddd; font-size: 10px; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.data th, table.data td { border: 1px solid #ddd; padding: 3px 5px; text-align: left; font-size: 9px; }
        table.data th { background-color: #f5f5f5; font-weight: bold; }
        .text-center { text-align: center; }
        .section-title { font-size: 12px; font-weight: bold; margin: 10px 0 5px; padding-bottom: 3px; border-bottom: 2px solid #333; }
        .tag { padding: 1px 5px; border-radius: 2px; }
        .bg-success { background: #d1e7dd; color: #0f5132; }
        .bg-danger { background: #f8d7da; color: #842029; }
        .bg-warning { background: #fff3cd; color: #664d03; }
        .bg-primary { background: #cff4fc; color: #055160; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Generated on {{ date('Y-m-d H:i:s') }}</p>
    </div>

    @php $o = $data['overall']; @endphp
    <table class="summary-box">
        <tr>
            <th>Total Appeared</th>
            <th>Passed</th>
            <th>Failed</th>
            <th>Pass %</th>
            <th>Fail %</th>
            <th>Avg Class Pass %</th>
        </tr>
        <tr>
            <td class="text-center">{{ $o['total_appeared'] }}</td>
            <td class="text-center">{{ $o['total_passed'] }}</td>
            <td class="text-center">{{ $o['total_failed'] }}</td>
            <td class="text-center"><span class="tag bg-success">{{ $o['pass_percentage'] }}%</span></td>
            <td class="text-center"><span class="tag bg-danger">{{ $o['fail_percentage'] }}%</span></td>
            <td class="text-center">{{ $data['avgPctOverall'] }}%</td>
        </tr>
    </table>

    <table class="summary-box" style="margin-bottom: 15px;">
        <tr>
            <th>Best Class</th>
            <th>Lowest Class</th>
            <th>Highest Subject</th>
            <th>Lowest Subject</th>
        </tr>
        <tr>
            <td class="text-center">{{ $data['bestClass'] ?: '--' }}</td>
            <td class="text-center">{{ $data['lowestClass'] ?: '--' }}</td>
            <td class="text-center">{{ $data['highestSubject'] ?: '--' }}</td>
            <td class="text-center">{{ $data['lowestSubject'] ?: '--' }}</td>
        </tr>
    </table>

    {{-- Class Performance --}}
    @php $cp = $data['classPerformance'] ?? []; @endphp
    @if(!empty($cp))
        <div class="section-title">Class-wise Performance</div>
        <table class="data">
            <thead>
                <tr>
                    <th>Class & Section</th>
                    <th class="text-center">Appeared</th>
                    <th class="text-center">Passed</th>
                    <th class="text-center">Failed</th>
                    <th class="text-center">Pass %</th>
                    <th class="text-center">Avg Marks</th>
                    <th class="text-center">Avg %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cp as $row)
                    <tr>
                        <td>{{ $row['class_section'] }}</td>
                        <td class="text-center">{{ $row['appeared'] }}</td>
                        <td class="text-center">{{ $row['passed'] }}</td>
                        <td class="text-center">{{ $row['failed'] }}</td>
                        <td class="text-center"><span class="tag bg-{{ $row['pass_pct'] >= 80 ? 'success' : ($row['pass_pct'] >= 50 ? 'warning' : 'danger') }}">{{ $row['pass_pct'] }}%</span></td>
                        <td class="text-center">{{ $row['avg_marks'] }}</td>
                        <td class="text-center">{{ $row['avg_percentage'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Subject Analysis --}}
    @php $sa = $data['subjectAnalysis'] ?? []; @endphp
    @if(!empty($sa))
        <div class="section-title">Subject-wise Analysis</div>
        <table class="data">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th class="text-center">Appeared</th>
                    <th class="text-center">Passed</th>
                    <th class="text-center">Failed</th>
                    <th class="text-center">Pass %</th>
                    <th class="text-center">Fail %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sa as $row)
                    <tr>
                        <td>{{ $row['subject'] }}</td>
                        <td class="text-center">{{ $row['appeared'] }}</td>
                        <td class="text-center">{{ $row['passed'] }}</td>
                        <td class="text-center">{{ $row['failed'] }}</td>
                        <td class="text-center"><span class="tag bg-{{ $row['pass_pct'] >= 80 ? 'success' : ($row['pass_pct'] >= 50 ? 'warning' : 'danger') }}">{{ $row['pass_pct'] }}%</span></td>
                        <td class="text-center">{{ $row['fail_pct'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Student Breakdown --}}
    @php $sa2 = $data['studentAnalysis'] ?? []; @endphp
    @if(!empty($sa2))
        <div class="section-title">Student-wise Breakdown</div>
        <table class="data">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Admission No</th>
                    <th>Class & Section</th>
                    <th>Exam</th>
                    <th class="text-center">%</th>
                    <th class="text-center">Result</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sa2 as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row['student_name'] }}</td>
                        <td>{{ $row['admission_no'] }}</td>
                        <td>{{ $row['class_section'] }}</td>
                        <td>{{ $row['exam_name'] }}</td>
                        <td class="text-center">{{ $row['percentage'] }}%</td>
                        <td class="text-center"><span class="tag bg-{{ $row['result'] == 'Pass' ? 'success' : 'danger' }}">{{ $row['result'] }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(empty($cp) && empty($sa) && empty($sa2))
        <p class="text-center">No data available for the selected criteria.</p>
    @endif
</body>
</html>
