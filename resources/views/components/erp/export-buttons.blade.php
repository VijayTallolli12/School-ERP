{{--
    ERP Report Export Buttons
    Standardizes Print / PDF / Excel export controls across all report pages.

    Props:
        excelUrl (string)  URL for Excel export
        pdfUrl   (string)  URL for PDF export
        printUrl (string)  URL for Print view
        excelId  (string)  Optional ID for Excel link
        pdfId    (string)  Optional ID for PDF link
        printId  (string)  Optional ID for Print link
        target   (string)  target attr for links (default: _self)
        size     (string)  Optional btn size class e.g. btn-sm
--}}
<div class="dropdown">
    <button class="btn btn-outline-secondary {{ $size ?? '' }}" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        Export <i class="ti ti-dots-vertical ms-1"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
        @if(isset($excelUrl) || isset($excelId))
            <li>
                <a class="dropdown-item d-flex align-items-center" href="{{ $excelUrl ?? '#' }}" @if($excelId ?? null) id="{{ $excelId }}" @endif target="{{ $target ?? '_self' }}">
                    <i class="ti ti-file-spreadsheet text-success me-2"></i> Excel
                </a>
            </li>
        @endif
        @if(isset($pdfUrl) || isset($pdfId))
            <li>
                <a class="dropdown-item d-flex align-items-center" href="{{ $pdfUrl ?? '#' }}" @if($pdfId ?? null) id="{{ $pdfId }}" @endif target="{{ $target ?? '_self' }}">
                    <i class="ti ti-file-type-pdf text-danger me-2"></i> PDF
                </a>
            </li>
        @endif
        @if(isset($printUrl) || isset($printId))
            <li>
                <a class="dropdown-item d-flex align-items-center" href="{{ $printUrl ?? '#' }}" @if($printId ?? null) id="{{ $printId }}" @endif target="{{ $target ?? '_blank' }}">
                    <i class="ti ti-printer text-secondary me-2"></i> Print
                </a>
            </li>
        @endif
    </ul>
</div>
