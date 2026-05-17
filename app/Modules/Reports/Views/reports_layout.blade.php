<!DOCTYPE html>
<html>
<head>
    <title>@yield("title")</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { text-align: center; margin-top: 20px; font-size: 0.8em; }
        .page-break { page-break-after: always; }
    </style>
    @stack("styles")
</head>
<body>
    <div class="container-fluid py-4">
        <div class="header mb-4">
            <h1>@yield("report_title")</h1>
        </div>
        @yield("content")
        <div class="footer mt-4">
            <p>Generated on {{ date("Y-m-d H:i:s") }}</p>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    @stack("scripts")
</body>
</html>
