@extends('layouts.admin')

@section('title', 'Academic Calendar')
@section('page-title', 'Academic Calendar')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Academic Calendar</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center gap-3">
                    <ul class="nav nav-tabs card-header-tabs flex-grow-1 mb-0" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#listView" type="button" role="tab">
                                <i class="ti ti-list me-1"></i> List View
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendarView" type="button" role="tab">
                                <i class="ti ti-calendar me-1"></i> Calendar View
                            </button>
                        </li>
                    </ul>
                    @can('academic_calendar.create')
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#eventModal" id="createEvent">
                            <i class="ti ti-plus me-1"></i> Add Event
                        </button>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        {{-- List View --}}
                        <div class="tab-pane fade show active" id="listView" role="tabpanel">
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm filter-select" id="filterEventType">
                                        <option value="">All Event Types</option>
                                        @foreach ($eventTypes as $type)
                                            <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm filter-select" id="filterAudience">
                                        <option value="">All Audiences</option>
                                        @foreach ($audiences as $audience)
                                            <option value="{{ $audience }}">{{ ucfirst($audience) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm filter-select" id="filterPublished">
                                        <option value="">All Status</option>
                                        <option value="1">Published</option>
                                        <option value="0">Draft</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm filter-select" id="filterAcademicYear">
                                        <option value="">All Academic Years</option>
                                        @foreach ($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <table class="table table-striped table-bordered w-100" id="eventsTable">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Event Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Audience</th>
                                        <th>Status</th>
                                        <th width="140">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        {{-- Calendar View --}}
                        <div class="tab-pane fade" id="calendarView" role="tabpanel">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <button class="btn btn-outline-secondary btn-sm" id="prevMonth">
                                    <i class="ti ti-chevron-left"></i>
                                </button>
                                <h5 class="mb-0 fw-semibold" id="calendarMonthYear"></h5>
                                <button class="btn btn-outline-secondary btn-sm" id="nextMonth">
                                    <i class="ti ti-chevron-right"></i>
                                </button>
                            </div>
                            <div id="calendarGrid"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content ajax-form" id="eventForm" method="POST" action="{{ route('admin.calendar.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="eventMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalTitle">Add Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Title</label>
                            <input class="form-control" name="title" required maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Event Type</label>
                            <select class="form-select" name="event_type" required>
                                <option value="">Select</option>
                                @foreach ($eventTypes as $type)
                                    <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Academic Year</label>
                            <select class="form-select" name="academic_year_id" required>
                                <option value="">Select</option>
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Audience</label>
                            <select class="form-select" name="audience" required>
                                <option value="">Select</option>
                                @foreach ($audiences as $audience)
                                    <option value="{{ $audience }}">{{ ucfirst($audience) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Start Date</label>
                            <input class="form-control" type="date" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input class="form-control" type="date" name="end_date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input class="form-control" name="location" maxlength="255">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4" maxlength="5000"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary py-2"><i class="ti ti-device-floppy me-1"></i> Save Event</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="eventDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventDetailTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="eventDetailBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async () => { (async () => { const DataTable = await window.lazyDT();
            const eventModal = new bootstrap.Modal('#eventModal');
            const eventDetailModal = new bootstrap.Modal('#eventDetailModal');
            const eventForm = $('#eventForm');

            // --- DataTable ---
            const eventsTable = $('#eventsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route('admin.calendar.data') }}',
                    data: function (d) {
                        d.event_type = $('#filterEventType').val();
                        d.audience = $('#filterAudience').val();
                        d.is_published = $('#filterPublished').val();
                        d.academic_year_id = $('#filterAcademicYear').val();
                    },
                },
                columns: [
                    { data: 'title', name: 'title' },
                    { data: 'event_type', name: 'event_type', orderable: false, searchable: false },
                    { data: 'start_date', name: 'start_date' },
                    { data: 'end_date', name: 'end_date' },
                    { data: 'audience', name: 'audience', orderable: false, searchable: false },
                    { data: 'is_published', name: 'is_published', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ],
            });

            $('.filter-select').on('change', () => eventsTable.ajax.reload());

            // --- CRUD modals ---
            $('#createEvent').on('click', () => {
                eventForm[0].reset();
                $('#eventMethod').val('POST');
                eventForm.attr('action', '{{ route('admin.calendar.store') }}');
                $('#eventModalTitle').text('Add Event');
                eventForm.find('.is-invalid').removeClass('is-invalid');
                eventForm.find('.invalid-feedback.dynamic').remove();
            });

            $('#eventsTable').on('click', '.edit-event', function () {
                const id = $(this).data('id');
                $.get('{{ url('admin/calendar') }}/' + id, (response) => {
                    eventForm[0].reset();
                    eventForm.find('.is-invalid').removeClass('is-invalid');
                    eventForm.find('.invalid-feedback.dynamic').remove();
                    eventForm.attr('action', '{{ url('admin/calendar') }}/' + id);
                    $('#eventMethod').val('PUT');
                    $('#eventModalTitle').text('Edit Event');

                    Object.entries(response.event).forEach(([key, value]) => {
                        const input = eventForm.find(`[name="${key}"]`);
                        if (input.length) {
                            input.val(value ?? '');
                        }
                    });

                    eventModal.show();
                });
            });

            $('#eventsTable').on('click', '.toggle-publish', async function () {
                const id = $(this).data('id');
                const Swal = await window.lazySwal();
                Swal.fire({
                    title: 'Toggle publish status?',
                    text: 'Publishing will send notifications to the target audience.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, toggle it!',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: '{{ url('admin/calendar') }}/' + id + '/toggle-publish',
                        method: 'PATCH',
                        data: { _token: '{{ csrf_token() }}' },
                        success: (response) => {
                            App.toast('success', response.message);
                            eventsTable.ajax.reload(null, false);
                            if ($('#calendar-tab').hasClass('active')) {
                                loadCalendarEvents(currentYear, currentMonth);
                            }
                        },
                        error: (xhr) => {
                            App.toast('error', xhr.responseJSON?.message || 'Toggle failed.');
                        },
                    });
                });
            });

            $('#eventsTable').on('click', '.delete-event', function () {
                const id = $(this).data('id');
                App.confirmDelete({
                    url: '{{ url('admin/calendar') }}/' + id,
                    onSuccess: () => eventsTable.ajax.reload(null, false),
                });
            });

            eventForm.on('erp:success', () => {
                eventModal.hide();
                eventsTable.ajax.reload(null, false);
                if ($('#calendar-tab').hasClass('active')) {
                    loadCalendarEvents(currentYear, currentMonth);
                }
            });

            // --- Calendar View ---
            let currentYear = {{ now()->year }};
            let currentMonth = {{ now()->month }};
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

            const today = '{{ now()->format('Y-m-d') }}';

            function loadCalendarEvents(year, month) {
                $.get('{{ route('admin.calendar.events') }}', { year, month }, (response) => {
                    if (response.success) {
                        renderCalendar(year, month, response.events);
                    }
                });
            }

            function renderCalendar(year, month, events) {
                const firstDay = new Date(year, month - 1, 1);
                const lastDay = new Date(year, month, 0);
                const daysInMonth = lastDay.getDate();
                const startDayOfWeek = firstDay.getDay();
                const totalCells = Math.ceil((startDayOfWeek + daysInMonth) / 7) * 7;

                // Group events by date
                const eventsByDate = {};
                events.forEach(ev => {
                    const start = new Date(ev.start_date + 'T00:00:00');
                    const end = ev.end_date ? new Date(ev.end_date + 'T00:00:00') : start;
                    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                        const key = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                        if (!eventsByDate[key]) eventsByDate[key] = [];
                        eventsByDate[key].push(ev);
                    }
                });

                $('#calendarMonthYear').text(monthNames[month - 1] + ' ' + year);

                let html = '<table class="table table-bordered mb-0 calendar-grid"><thead><tr>';
                dayNames.forEach(d => { html += '<th class="text-center small text-secondary py-2">' + d + '</th>'; });
                html += '</tr></thead><tbody>';

                let cellIndex = 0;
                for (let row = 0; row < totalCells / 7; row++) {
                    html += '<tr>';
                    for (let col = 0; col < 7; col++) {
                        const dayNum = cellIndex - startDayOfWeek + 1;
                        const isCurrentMonth = dayNum >= 1 && dayNum <= daysInMonth;
                        const dateStr = year + '-' + String(month).padStart(2, '0') + '-' + String(dayNum).padStart(2, '0');
                        const isToday = dateStr === today;
                        const dayEvents = eventsByDate[dateStr] || [];

                        let classes = 'text-center align-top calendar-day';
                        if (!isCurrentMonth) classes += ' text-muted bg-light';
                        if (isToday) classes += ' calendar-today';

                        html += '<td class="' + classes + '" style="height:100px;width:14.28%;cursor:pointer" data-date="' + dateStr + '">';
                        html += '<div class="fw-semibold small mb-1' + (isToday ? ' text-primary' : '') + '">' + (isCurrentMonth ? dayNum : '') + '</div>';
                        dayEvents.slice(0, 3).forEach(ev => {
                            html += '<div class="badge ' + (ev.badge_class || 'bg-light text-dark') + ' d-block mb-1" style="font-size:10px;cursor:pointer" data-event=\'' + JSON.stringify(ev).replace(/'/g, '&#39;') + '\'>' + ev.title.substring(0, 18) + '</div>';
                        });
                        if (dayEvents.length > 3) {
                            html += '<div class="small text-secondary">+' + (dayEvents.length - 3) + ' more</div>';
                        }
                        html += '</td>';
                        cellIndex++;
                    }
                    html += '</tr>';
                }
                html += '</tbody></table>';

                $('#calendarGrid').html(html);

                // Event click handler
                $('#calendarGrid .badge[data-event]').on('click', function (e) {
                    e.stopPropagation();
                    const ev = $(this).data('event');
                    showEventDetail(ev);
                });

                // Day click handler
                $('#calendarGrid .calendar-day').on('click', function () {
                    const dateStr = $(this).data('date');
                    // Highlight all events on this date in the list view and switch to list tab
                    new bootstrap.Tab('#list-tab').show();
                    eventsTable.search(dateStr).draw();
                });
            }

            function showEventDetail(ev) {
                const desc = ev.description ? '<p class="mt-2">' + ev.description + '</p>' : '';
                const location = ev.location ? '<p><strong>Location:</strong> ' + ev.location + '</p>' : '';
                const endDate = ev.end_date ? '<p><strong>End Date:</strong> ' + ev.end_date + '</p>' : '';

                $('#eventDetailTitle').text(ev.title);
                $('#eventDetailBody').html(
                    '<p><span class="badge ' + (ev.badge_class || 'bg-light text-dark') + '">' + ev.event_type_label + '</span></p>' +
                    '<p><strong>Start Date:</strong> ' + ev.start_date + '</p>' +
                    endDate +
                    location +
                    '<p><strong>Audience:</strong> ' + ev.audience + '</p>' +
                    desc
                );
                eventDetailModal.show();
            }

            $('#prevMonth').on('click', () => {
                currentMonth--;
                if (currentMonth < 1) { currentMonth = 12; currentYear--; }
                loadCalendarEvents(currentYear, currentMonth);
            });

            $('#nextMonth').on('click', () => {
                currentMonth++;
                if (currentMonth > 12) { currentMonth = 1; currentYear++; }
                loadCalendarEvents(currentYear, currentMonth);
            });

            // Load calendar on tab switch
            $('#calendar-tab').on('shown.bs.tab', () => {
                loadCalendarEvents(currentYear, currentMonth);
            });

            // Initial calendar load if calendar tab is active
            if ($('#calendar-tab').hasClass('active')) {
                loadCalendarEvents(currentYear, currentMonth);
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        .calendar-grid td,
        .calendar-grid th {
            vertical-align: top;
            font-size: 0.8rem;
        }
        .calendar-grid .calendar-today {
            background-color: #eef2ff;
            box-shadow: inset 0 0 0 2px #4f46e5;
        }
        .calendar-grid .calendar-day:hover {
            background-color: #f8f9fa;
        }
        .bg-calendar-field-trip {
            background-color: #0d9488;
            color: #fff;
        }
        .bg-calendar-workshop {
            background-color: #6366f1;
            color: #fff;
        }
    </style>
@endpush
