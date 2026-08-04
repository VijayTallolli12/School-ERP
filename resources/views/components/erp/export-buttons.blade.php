{{--
    ERP Report Export Buttons
    Standardizes Print / PDF / Excel export controls across all report pages.

    Props:
        excelUrl (string)  URL for Excel export
        pdfUrl   (string)  URL for PDF export
        printUrl (string)  URL for Print view
        target   (string)  target attr for links (default: _self)
--}}
<div class="d-flex flex-wrap gap-2">
    @if($excelUrl ?? null)
        <a href="{{ $excelUrl }}" class="btn btn-sm btn-outline-success" target="{{ $target ?? '_self' }}">
            <i class="ti ti-file-spreadsheet me-1"></i>Excel
        </a>
    @endif
    @if($pdfUrl ?? null)
        <a href="{{ $pdfUrl }}" class="btn btn-sm btn-outline-danger" target="{{ $target ?? '_self' }}">
            <i class="ti ti-file-type-pdf me-1"></i>PDF
        </a>
    @endif
    @if($printUrl ?? null)
        <a href="{{ $printUrl }}" class="btn btn-sm btn-outline-secondary" target="{{ $target ?? '_blank' }}">
            <i class="ti ti-printer me-1"></i>Print
        </a>
    @endif
</div>
