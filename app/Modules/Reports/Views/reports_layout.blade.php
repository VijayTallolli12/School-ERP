<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield("title")</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css">
    <style>
        body { font-family: "Inter", system-ui, -apple-system, sans-serif; background: #f8fafc; }
        .report-header { text-align: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e2e8f0; }
        .report-footer { text-align: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; font-size: 0.8rem; color: #64748b; }
        .page-break { page-break-after: always; }
    </style>
    @stack("styles")
</head>
<body>
    <div class="container-fluid py-4">
        <div class="report-header">
            <h3 class="fw-semibold">@yield("report_title")</h3>
        </div>
        @yield("content")
        <div class="report-footer">
            <p class="mb-0">Generated on {{ date("Y-m-d H:i:s") }}</p>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.min.js"></script>
    @stack("scripts")
</body>
</html>
