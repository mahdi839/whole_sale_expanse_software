<?php

namespace App\Http\Controllers;

use App\Services\ProductSalesReport;
use App\Support\SimplePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SalesReportController extends Controller
{
    public function __construct(private ProductSalesReport $report) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $filters = $this->report->filters($request, $user);
        $shops = $this->report->shops($user);
        $rows = $this->report->paginate($filters, $user);
        $totals = $this->report->totals($filters, $user);
        $periodLabel = $this->report->periodLabel($filters);
        $shopLabel = $this->report->shopLabel($filters, $user, $shops);

        return view('sales_reports.index', compact('filters', 'shops', 'rows', 'totals', 'periodLabel', 'shopLabel'));
    }

    public function exportCsv(Request $request)
    {
        $user = auth()->user();
        $filters = $this->report->filters($request, $user);
        $shops = $this->report->shops($user);
        $rows = $this->report->rows($filters, $user);
        $totals = $this->report->totals($filters, $user);
        $fileName = 'product-sales-report-'.now()->format('Y-m-d-H-i-s').'.csv';

        $headers = [
            'Product',
            'Design Code',
            'Product Code',
            'Sales Count',
            'Sold Qty',
            'Return Qty',
            'Net Qty',
            'Sales Amount',
            'Return Amount',
            'Net Amount',
            'Avg Price',
            'Return %',
            'Stock',
        ];

        return Response::streamDownload(function () use ($rows, $totals, $filters, $user, $shops, $headers) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['Inaya Creation - Product Sales Report']);
            fputcsv($file, ['Period', $this->report->periodLabel($filters)]);
            fputcsv($file, ['Shop', $this->report->shopLabel($filters, $user, $shops)]);
            if ($filters['search']) {
                fputcsv($file, ['Search', $filters['search']]);
            }
            fputcsv($file, ['Generated', now()->format('d M Y H:i')]);
            fputcsv($file, []);
            fputcsv($file, ['Total Sales Amount', number_format($totals->sales_amount, 2, '.', '')]);
            fputcsv($file, ['Total Sold Qty', number_format($totals->sold_qty, 2, '.', '')]);
            fputcsv($file, ['Total Return Qty', number_format($totals->return_qty, 2, '.', '')]);
            fputcsv($file, ['Net Amount', number_format($totals->net_amount, 2, '.', '')]);
            fputcsv($file, []);
            fputcsv($file, $headers);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->product_name,
                    $row->sku,
                    $row->product_code,
                    (int) $row->sales_count,
                    number_format((float) $row->sold_qty, 2, '.', ''),
                    number_format((float) $row->return_qty, 2, '.', ''),
                    number_format((float) $row->net_qty, 2, '.', ''),
                    number_format((float) $row->sales_amount, 2, '.', ''),
                    number_format((float) $row->return_amount, 2, '.', ''),
                    number_format((float) $row->net_amount, 2, '.', ''),
                    number_format((float) $row->avg_price, 2, '.', ''),
                    number_format((float) $row->return_rate, 2, '.', ''),
                    number_format((float) $row->stock_qty, 2, '.', ''),
                ]);
            }

            fputcsv($file, [
                'TOTAL',
                '',
                '',
                (int) $totals->sales_count,
                number_format($totals->sold_qty, 2, '.', ''),
                number_format($totals->return_qty, 2, '.', ''),
                number_format($totals->net_qty, 2, '.', ''),
                number_format($totals->sales_amount, 2, '.', ''),
                number_format($totals->return_amount, 2, '.', ''),
                number_format($totals->net_amount, 2, '.', ''),
                '',
                number_format($totals->return_rate, 2, '.', ''),
                '',
            ]);
            fclose($file);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        $filters = $this->report->filters($request, $user);
        $shops = $this->report->shops($user);
        $rows = $this->report->rows($filters, $user);
        $totals = $this->report->totals($filters, $user);
        $fileName = 'product-sales-report-'.now()->format('Y-m-d-H-i-s').'.pdf';

        $pdfRows = $rows->values()->map(function ($row, $index) {
            return [
                (string) ($index + 1),
                $row->product_name,
                $row->sku ?: ($row->product_code ?: '-'),
                (string) ((int) $row->sales_count),
                number_format((float) $row->sold_qty, 2),
                number_format((float) $row->return_qty, 2),
                number_format((float) $row->net_qty, 2),
                number_format((float) $row->sales_amount, 2),
                number_format((float) $row->return_amount, 2),
                number_format((float) $row->net_amount, 2),
                number_format((float) $row->return_rate, 1).'%',
            ];
        });

        $pdfRows->push([
            '',
            'TOTAL',
            '',
            (string) ((int) $totals->sales_count),
            number_format($totals->sold_qty, 2),
            number_format($totals->return_qty, 2),
            number_format($totals->net_qty, 2),
            number_format($totals->sales_amount, 2),
            number_format($totals->return_amount, 2),
            number_format($totals->net_amount, 2),
            number_format($totals->return_rate, 1).'%',
        ]);

        $subtitle = 'Period: '.$this->report->periodLabel($filters)
            .'   |   Shop: '.$this->report->shopLabel($filters, $user, $shops)
            .'   |   Products: '.$totals->products_count;

        return Response::make(SimplePdf::table(
            'Inaya Creation - Product Sales Report',
            ['#', 'Product', 'Code', 'Sales Count', 'Sold Qty', 'Return Qty', 'Net Qty', 'Sales Amount', 'Return Amount', 'Net Amount', 'Return %'],
            $pdfRows,
            [28, 168, 78, 52, 58, 62, 54, 78, 78, 78, 56],
            [
                'logo_path' => public_path('inaya_creation_logo.jpeg'),
                'subtitle' => $subtitle,
                'summary' => [
                    ['label' => 'Total Sales Amount', 'value' => 'BDT '.number_format($totals->sales_amount, 2), 'tone' => 'emerald'],
                    ['label' => 'Total Sold Qty', 'value' => number_format($totals->sold_qty, 2), 'tone' => 'indigo'],
                    ['label' => 'Total Return Qty', 'value' => number_format($totals->return_qty, 2), 'tone' => 'rose'],
                    ['label' => 'Net Sales Amount', 'value' => 'BDT '.number_format($totals->net_amount, 2), 'tone' => 'violet'],
                    ['label' => 'Return Amount', 'value' => 'BDT '.number_format($totals->return_amount, 2), 'tone' => 'amber'],
                    ['label' => 'Net Qty', 'value' => number_format($totals->net_qty, 2), 'tone' => 'sky'],
                ],
            ]
        ), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
