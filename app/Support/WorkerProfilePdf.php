<?php

namespace App\Support;

use Illuminate\Support\Facades\Response;

class WorkerProfilePdf
{
    public static function mergeLedgerTransactions($cashTransactions, $bankTransactions = [])
    {
        $cashRows = collect($cashTransactions)->map(fn ($transaction) => (object) [
            'date' => $transaction->date,
            'created_at' => $transaction->created_at,
            'reference' => $transaction->reference,
            'source' => 'Cash',
            'type' => $transaction->type,
            'direction' => $transaction->direction,
            'amount' => $transaction->amount,
            'payment_method' => $transaction->payment_method ?: '-',
            'note' => $transaction->note,
        ]);

        $bankRows = collect($bankTransactions)->map(fn ($transaction) => (object) [
            'date' => $transaction->date,
            'created_at' => $transaction->created_at,
            'reference' => $transaction->reference,
            'source' => 'Bank',
            'type' => $transaction->direction === 'out' ? 'Bank Out' : 'Bank In',
            'direction' => $transaction->direction,
            'amount' => $transaction->amount,
            'payment_method' => collect([$transaction->bank_name, $transaction->bank_details])->filter()->implode(' - ') ?: '-',
            'note' => $transaction->note,
        ]);

        return $cashRows
            ->concat($bankRows)
            ->sortByDesc(fn ($transaction) => optional($transaction->date)->format('Y-m-d').'-'.optional($transaction->created_at)->format('His').'-'.$transaction->reference)
            ->values();
    }

    public static function download(object $worker, string $title, string $type, $workLogs, $cashTransactions = null, $bankTransactions = null)
    {
        [$headers, $rows] = self::workLogTable($type, $workLogs);
        $totalWork = $workLogs->sum(fn ($log) => (float) $log->total_rate);
        $transactions = self::mergeLedgerTransactions($cashTransactions ?? [], $bankTransactions ?? []);

        $summary = [
            ['label' => 'Name', 'value' => $worker->name],
            ['label' => 'Phone', 'value' => $worker->phone ?: '-'],
            ['label' => 'Address', 'value' => $worker->address ?: '-'],
        ];

        if (! empty($worker->nid_passport_no)) {
            $summary[] = ['label' => 'NID / Passport', 'value' => $worker->nid_passport_no];
        }

        $summary = array_merge($summary, [
            ['label' => 'Work Total', 'value' => number_format($totalWork, 2)],
            ['label' => 'Paid', 'value' => number_format((float) ($worker->total_paid ?? 0), 2)],
            ['label' => 'Due', 'value' => number_format((float) ($worker->total_due ?? 0), 2)],
            ['label' => 'Advance', 'value' => number_format((float) ($worker->advance ?? 0), 2)],
        ]);

        $sections = [
            [
                'title' => 'Work Logs',
                'headers' => $headers,
                'rows' => $rows,
            ],
            [
                'title' => 'Transactions',
                'headers' => ['Date', 'Reference', 'Source', 'Type', 'Amount', 'Method / Bank', 'Note'],
                'rows' => $transactions->map(fn ($transaction) => [
                    optional($transaction->date)->format('d M Y') ?: '-',
                    $transaction->reference ?: '-',
                    $transaction->source ?: '-',
                    ucwords(str_replace('_', ' ', (string) $transaction->type)),
                    ($transaction->direction === 'in' ? '+' : '-').number_format((float) $transaction->amount, 2),
                    $transaction->payment_method ?: '-',
                    $transaction->note ?: '-',
                ]),
            ],
        ];

        return Response::make(SimplePdf::tables('Inaya Creation - '.$title.' - '.$worker->name, $sections, [
            'logo_path' => public_path('inaya_creation_logo.jpeg'),
            'summary' => $summary,
        ]), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$type.'-'.$worker->id.'-profile-work-logs-'.now()->format('Y-m-d-H-i-s').'.pdf"',
        ]);
    }

    private static function workLogTable(string $type, $logs): array
    {
        return match ($type) {
            'tailor' => [
                ['Date', 'Product', 'Design Code', 'Qty', 'Rate/Piece', 'Total'],
                $logs->map(fn ($log) => [
                    optional($log->date)->format('Y-m-d'),
                    $log->product?->product_name ?? '-',
                    $log->product?->sku ?: ($log->product?->product_code ?: '-'),
                    $log->item_qty,
                    $log->per_piece_rate,
                    $log->total_rate,
                ]),
            ],
            'computer' => [
                ['Date', 'Memo No', 'Product', 'Design Code', 'Design Qty', 'Received Qty', 'Balance', 'Rate/Piece', 'Total'],
                $logs->map(fn ($log) => [
                    optional($log->date)->format('Y-m-d'), $log->memo_no ?: '-', $log->product?->product_name ?? '-',
                    $log->product?->sku ?: ($log->product?->product_code ?: '-'), $log->computer_design_qty,
                    $log->received_qty, (float) $log->computer_design_qty - (float) $log->received_qty,
                    $log->rate_per_piece, $log->total_rate,
                ]),
            ],
            'carry' => [
                ['Date', 'Memo No', 'Marka', 'Bale Qty', 'KG', 'Received KG', 'Balance KG', 'Rate/KG', 'Total'],
                $logs->map(fn ($log) => [
                    optional($log->date)->format('Y-m-d'), $log->memo_no ?: '-', $log->marka ?: '-', $log->bale_qty,
                    $log->total_unit_kg, $log->received_qty, (float) $log->total_unit_kg - (float) $log->received_qty,
                    $log->rate_per_kg, $log->total_rate,
                ]),
            ],
            default => [
                ['Date', 'Memo No', 'Qty', 'Received Qty', 'Balance', 'Unit', 'Rate/Goj', 'Total'],
                $logs->map(fn ($log) => [
                    optional($log->date)->format('Y-m-d'), $log->memo_no ?: '-', $log->qty, $log->received_qty,
                    (float) $log->qty - (float) $log->received_qty, $log->unit, $log->rate_per_goj, $log->total_rate,
                ]),
            ],
        };
    }
}
