<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Exports\LeadsTemplateExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

$routes = app('router')->getRoutes();
$route = $routes->match(Request::create('/leads/contacts/template', 'GET'));
if ($route->getName() !== 'leads.contacts.template'
    || !str_ends_with($route->getActionName(), '@downloadTemplate')) {
    throw new RuntimeException('Template URL was captured by the lead detail route.');
}
$detail = $routes->match(Request::create('/leads/contacts/123', 'GET'));
if ($detail->getName() !== 'leads.contacts.show') {
    throw new RuntimeException('Existing lead detail route changed.');
}

$export = new LeadsTemplateExport();
$contents = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
$file = tempnam(sys_get_temp_dir(), 'lead-template-');
try {
    file_put_contents($file, $contents);
    $workbook = IOFactory::load($file);
    $rows = $workbook->getActiveSheet()->toArray();
    if ($rows !== [$export->headings()]) {
        throw new RuntimeException('Sample workbook contained incorrect headings or lead data.');
    }
    $workbook->disconnectWorksheets();
} finally {
    unlink($file);
}
echo "Lead sample route and XLSX download checks passed.\n";
