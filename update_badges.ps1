$files = Get-ChildItem -Path "f:\Folkslogic\school\app\Modules" -Recurse -Filter *.php
foreach ($f in $files) {
    $c = Get-Content $f.FullName -Raw
    $nc = $c -replace "'success' : 'secondary'", "'success-subtle text-success' : 'secondary-subtle text-secondary'"
    $nc = $nc -replace "'success' : 'danger'", "'success-subtle text-success' : 'danger-subtle text-danger'"
    $nc = $nc -replace "'warning' : 'success'", "'warning-subtle text-warning' : 'success-subtle text-success'"
    $nc = $nc -replace "'secondary' : 'success' : 'danger'", "'secondary-subtle text-secondary' : 'success-subtle text-success' : 'danger-subtle text-danger'"
    $nc = $nc -replace "'pass' \? 'success' : 'danger'", "'pass' ? 'success-subtle text-success' : 'danger-subtle text-danger'"
    $nc = $nc -replace "'earning' \? 'success' : 'danger'", "'earning' ? 'success-subtle text-success' : 'danger-subtle text-danger'"
    if ($c -ne $nc) {
        Set-Content $f.FullName -Value $nc -NoNewline
    }
}

$views = Get-ChildItem -Path "f:\Folkslogic\school\resources\views\modules" -Recurse -Filter *.blade.php
foreach ($f in $views) {
    $c = Get-Content $f.FullName -Raw
    
    # Replace in blade JS strings for datatables:
    $nc = $c -replace "\? 'success' : 'secondary'", "? 'success-subtle text-success' : 'secondary-subtle text-secondary'"
    $nc = $nc -replace "\? 'warning' : 'success'", "? 'warning-subtle text-warning' : 'success-subtle text-success'"
    $nc = $nc -replace "\? 'success' : 'danger'", "? 'success-subtle text-success' : 'danger-subtle text-danger'"
    
    # Replace standard badge classes in blade HTML:
    $nc = $nc -replace "badge bg-success(?!-)", "badge bg-success-subtle text-success"
    $nc = $nc -replace "badge bg-secondary(?!-)", "badge bg-secondary-subtle text-secondary"
    $nc = $nc -replace "badge bg-danger(?!-)", "badge bg-danger-subtle text-danger"
    $nc = $nc -replace "badge bg-warning text-dark", "badge bg-warning-subtle text-warning"
    $nc = $nc -replace "badge bg-warning(?!-)", "badge bg-warning-subtle text-warning"
    $nc = $nc -replace "badge bg-info(?!-)", "badge bg-info-subtle text-info"
    
    if ($c -ne $nc) {
        Set-Content $f.FullName -Value $nc -NoNewline
    }
}
