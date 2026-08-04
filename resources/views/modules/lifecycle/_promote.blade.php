@can('student_lifecycle.promote')
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <select id="targetYear" class="form-select form-select-sm" style="width: 200px;">
            <option value="">Select target academic year</option>
            @foreach ($academicYears as $academicYear)
                <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
            @endforeach
        </select>
        <select id="targetClass" class="form-select form-select-sm" style="width: 220px;">
            <option value="">Select target class</option>
            @foreach ($classSections as $classSection)
                <option value="{{ $classSection->id }}">{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</option>
            @endforeach
        </select>
        <input id="rollNoPrefix" class="form-control form-control-sm" placeholder="Roll no / prefix" style="width: 160px;">
        <button id="promoteBtn" class="btn btn-primary btn-sm" disabled>
            <i class="ti ti-arrow-up-circle me-1"></i> Promote Selected
        </button>
    </div>
    <div class="alert alert-info py-2">
        Select students below, choose a target academic year and class, then click <strong>Promote Selected</strong>.
        Optional: enter a roll number for each student (or a prefix applied to their current roll number).
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-bordered mb-0" id="promoteTable">
            <thead>
            <tr>
                <th width="40"><input type="checkbox" id="selectAll"></th>
                <th>Admission No</th>
                <th>Student</th>
                <th>Current Class</th>
                <th>Current Roll No</th>
                <th>New Roll No</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($students as $student)
                <tr>
                    <td><input type="checkbox" class="student-check" value="{{ $student['id'] }}"></td>
                    <td>{{ $student['admission_no'] }}</td>
                    <td>{{ $student['full_name'] }}</td>
                    <td>{{ $student['class'] }}</td>
                    <td class="current-roll">{{ $student['roll_no'] ?? '' }}</td>
                    <td><input class="form-control form-control-sm new-roll" data-student="{{ $student['id'] }}" placeholder="Auto"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No active students found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endcan

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const updatePromoteState = () => {
                const hasSelection = $('.student-check:checked').length > 0;
                const hasTarget = $('#targetYear').val() && $('#targetClass').val();
                $('#promoteBtn').prop('disabled', !(hasSelection && hasTarget));
            };

            $('#selectAll').on('change', function () {
                $('.student-check').prop('checked', $(this).prop('checked'));
                updatePromoteState();
            });

            $('.student-check').on('change', updatePromoteState);
            $('#targetYear, #targetClass').on('change', updatePromoteState);

            $('#rollNoPrefix').on('input', function () {
                const prefix = $(this).val().trim();
                $('.new-roll').each(function () {
                    const current = $(this).closest('tr').find('.current-roll').text().trim();
                    $(this).val(prefix ? prefix + current : '');
                });
            });

            $('#promoteBtn').on('click', function () {
                const btn = $(this).prop('disabled', true);
                const studentIds = $('.student-check:checked').map(function () { return $(this).val(); }).get();
                const rollNumbers = {};
                $('.new-roll').each(function () {
                    const val = $(this).val().trim();
                    if (val) rollNumbers[$(this).data('student')] = val;
                });

                if (!confirm(`Promote ${studentIds.length} student(s)?`)) {
                    btn.prop('disabled', false);
                    return;
                }

                $.ajax({
                    url: '{{ route('admin.lifecycle.promotions.store') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        student_ids: studentIds,
                        to_academic_year_id: $('#targetYear').val(),
                        to_class_section_id: $('#targetClass').val(),
                        roll_numbers: rollNumbers,
                    },
                    success: (res) => {
                        if (res.success) {
                            App.toast('success', res.message);
                            window.location.reload();
                        } else {
                            App.toast('error', res.message || 'Promotion failed.');
                        }
                    },
                    error: (xhr) => {
                        const res = xhr.responseJSON || {};
                        App.toast('error', res.message || (res.errors ? Object.values(res.errors).flat().join('\n') : 'Something went wrong.'));
                    },
                    complete: () => btn.prop('disabled', false)
                });
            });
        });
    </script>
@endpush
