<?php
\ = 'f:\Folkslogic\school\resources\views\modules\reports';
\ = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(\));
foreach (\ as \) {
    if (\->isFile() && \->getExtension() === 'php') {
        \ = \->getRealPath();
        
        // Skip index.blade.php and reports_layout.blade.php
        if (basename(\) === 'index.blade.php' && dirname(\) === 'f:\Folkslogic\school\resources\views\modules\reports') continue;
        if (basename(\) === 'reports_layout.blade.php') continue;
        // Skip print files
        if (strpos(\, 'print.blade.php') !== false) continue;

        \ = file_get_contents(\);
        \ = \;
        
        // Replace extends
        \ = preg_replace('/@extends\([\'"]layouts\.admin[\'"]\)/', '@extends(\'modules.reports.reports_layout\')', \);
        
        // Remove Back to XYZ buttons
        \ = preg_replace('/<div class="mb-3">\s*<a href="[^"]+" class="btn btn-outline-secondary">\s*<i class="ti ti-arrow-left me-1"><\/i>\s*Back to [^<]+<\/a>\s*<\/div>/', '', \);
        \ = preg_replace('/<a href="[^"]+" class="btn btn-outline-secondary">\s*<i class="ti ti-arrow-left me-1"><\/i>\s*Back to [^<]+<\/a>/', '', \);
        
        if (\ !== \) {
            file_put_contents(\, \);
            echo 'Updated: ' . basename(\) . "\n";
        }
    }
}
