<?php

namespace App\Support;

class SimplePdf
{
    public static function table(string $title, array $headers, iterable $rows): string
    {
        $rows = collect($rows)->map(fn ($row) => array_map(fn ($value) => self::clean((string) $value), $row))->values();
        $headers = array_map(fn ($value) => self::clean((string) $value), $headers);
        $widths = self::widths($headers, $rows);

        $lines = [$title, str_repeat('=', min(120, strlen($title)))];
        $lines[] = self::row($headers, $widths);
        $lines[] = self::separator($widths);

        foreach ($rows as $row) {
            $lines[] = self::row($row, $widths);
        }

        return self::text($lines);
    }

    private static function widths(array $headers, $rows): array
    {
        $count = count($headers);
        $widths = [];

        for ($i = 0; $i < $count; $i++) {
            $max = strlen($headers[$i] ?? '');
            foreach ($rows as $row) {
                $max = max($max, strlen((string) ($row[$i] ?? '')));
            }

            $widths[$i] = min(max($max, 8), $i === $count - 1 ? 34 : 18);
        }

        return $widths;
    }

    private static function row(array $row, array $widths): string
    {
        $cells = [];
        foreach ($widths as $index => $width) {
            $value = substr((string) ($row[$index] ?? ''), 0, $width);
            $cells[] = str_pad($value, $width);
        }

        return '| '.implode(' | ', $cells).' |';
    }

    private static function separator(array $widths): string
    {
        return '+-'.implode('-+-', array_map(fn ($width) => str_repeat('-', $width), $widths)).'-+';
    }

    private static function text(array $lines): string
    {
        $objects = [];
        $pages = [];
        $chunks = array_chunk($lines, 30);
        $fontObject = 3;

        foreach ($chunks as $index => $chunk) {
            $pageObject = 4 + ($index * 2);
            $contentObject = $pageObject + 1;
            $pages[] = $pageObject;

            $stream = "BT\n/F1 8 Tf\n36 560 Td\n";
            foreach ($chunk as $lineIndex => $line) {
                $prefix = $lineIndex === 0 ? '' : '0 -16 Td ';
                $stream .= $prefix.'('.self::escape(substr($line, 0, 160)).") Tj\n";
            }
            $stream .= "ET";

            $objects[$pageObject] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 {$fontObject} 0 R >> >> /Contents {$contentObject} 0 R >>";
            $objects[$contentObject] = "<< /Length ".strlen($stream)." >>\nstream\n{$stream}\nendstream";
        }

        $kids = implode(' ', array_map(fn ($id) => "{$id} 0 R", $pages));
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = "<< /Type /Pages /Kids [{$kids}] /Count ".count($pages).' >>';
        $objects[$fontObject] = '<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private static function clean(string $value): string
    {
        $value = str_replace('৳', 'Tk', $value);
        $value = preg_replace('/[^\x20-\x7E]/', '', $value) ?? '';

        return trim($value);
    }

    private static function escape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], self::clean($value));
    }
}
