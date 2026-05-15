<?php

namespace App\Support;

/**
 * SimplePdf — zero-dependency, beautifully styled PDF table generator.
 *
 * Drop-in replacement: SimplePdf::table($title, $headers, $rows)
 *
 * Features
 * ────────
 *  • A4 landscape with branded header + accent bar
 *  • Alternating row shading for easy scanning
 *  • Column headers repeated on every page
 *  • Right-aligned numeric columns, negative values in red
 *  • Taka symbol (৳) safely converted to "BDT "
 *  • Footer with page numbers on every page
 *  • FlateDecode (gzip) stream compression
 *  • Pure PHP — no Composer packages required
 */
class SimplePdf
{
    // ── Page geometry (A4 landscape, user-space points) ─────────────────────
    private const PW = 841.89;
    private const PH = 595.28;
    private const ML = 36.0;          // margin left
    private const MR = 36.0;          // margin right
    private const MT = 36.0;          // margin top
    private const MB = 28.0;          // margin bottom

    // ── Heights ──────────────────────────────────────────────────────────────
    private const HEADER_H = 52.0;
    private const FOOTER_H = 22.0;
    private const THEAD_H  = 20.0;
    private const ROW_H    = 16.5;

    // ── Font sizes ───────────────────────────────────────────────────────────
    private const FS_TITLE    = 15;
    private const FS_SUBTITLE =  8;
    private const FS_TH       =  7;
    private const FS_TD       =  7.5;
    private const FS_FOOTER   =  7;

    // ── Brand palette (PDF rg/RG values, 0-1) ───────────────────────────────
    private const ACCENT     = '0.133 0.376 0.851';   // #2260D9
    private const HDR_BG     = '0.243 0.318 0.431';   // #3E5160
    private const HDR_FG     = '1.000 1.000 1.000';   // white
    private const SUB_FG     = '0.749 0.812 0.933';   // light blue-grey
    private const ROW_ODD    = '1.000 1.000 1.000';   // white
    private const ROW_EVEN   = '0.953 0.965 0.984';   // #F3F6FB
    private const BORDER     = '0.851 0.867 0.898';   // #D9DDE5
    private const BODY_FG    = '0.200 0.224 0.271';   // #333949
    private const MUTED_FG   = '0.502 0.533 0.588';   // #808897
    private const FOOTER_BG  = '0.953 0.965 0.984';   // #F3F6FB
    private const RED_FG     = '0.753 0.129 0.129';   // negative numbers

    // ── Internal state ────────────────────────────────────────────────────────
    private string $pdfHeader = "%PDF-1.4\n%\xe2\xe3\xcf\xd3\n";

    private array  $pages    = [];    // page-number => content-stream string
    private int    $curPage  = 0;
    private float  $curY     = 0.0;

    private array  $headers   = [];
    private array  $colWidths = [];
    private array  $colAlign  = [];   // 'L' | 'R' per column index
    private float  $tableW    = 0.0;
    private string $docTitle  = '';

    // ════════════════════════════════════════════════════════════════════════
    // PUBLIC API
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Build and return a PDF byte-string for a table report.
     *
     * @param string   $title   Report heading shown in the header strip
     * @param array    $headers Column labels
     * @param iterable $rows    Iterable of arrays (values in header order)
     * @param array|null $widths Optional explicit column widths in points
     */
    public static function table(
        string   $title,
        array    $headers,
        iterable $rows,
        ?array   $widths = null
    ): string {
        return (new self())->run($title, $headers, $rows, $widths);
    }

    // ════════════════════════════════════════════════════════════════════════
    // CORE
    // ════════════════════════════════════════════════════════════════════════

    private function run(string $title, array $headers, iterable $rows, ?array $widths): string
    {
        $this->docTitle  = $title;
        $this->headers   = $headers;
        $rowArr          = is_array($rows) ? $rows : iterator_to_array($rows);

        $this->colAlign  = $this->detectAlignment($headers, $rowArr);

        $usable = self::PW - self::ML - self::MR;
        $this->colWidths = $widths ?? $this->autoWidths($headers, $rowArr, $usable);
        $this->tableW    = (float) array_sum($this->colWidths);

        $this->newPage();
        $this->drawPageHeader();
        $this->drawTableHeader();

        foreach ($rowArr as $idx => $row) {
            $this->drawRow((array) $row, $idx);
        }

        return $this->compile();
    }

    // ════════════════════════════════════════════════════════════════════════
    // PAGE MANAGEMENT
    // ════════════════════════════════════════════════════════════════════════

    private function newPage(): void
    {
        $this->curPage++;
        $this->pages[$this->curPage] = '';
        $this->curY = self::MT + self::HEADER_H + 6;
    }

    private function ensureSpace(float $needed): void
    {
        $maxY = self::PH - self::MB - self::FOOTER_H - self::ROW_H;
        if ($this->curY + $needed > $maxY) {
            $this->newPage();
            $this->drawPageHeader();
            $this->drawTableHeader();
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    // PAGE CHROME
    // ════════════════════════════════════════════════════════════════════════

    private function drawPageHeader(): void
    {
        $x  = self::ML;
        $w  = self::PW - self::ML - self::MR;
        $h  = self::HEADER_H;
        $py = self::PH - self::MT - $h;   // PDF Y (bottom-up)

        // Header background
        $this->em(self::HDR_BG . " rg {$x} {$py} {$w} {$h} re f\n");

        // Accent left bar (5 pt)
        $this->em(self::ACCENT . " rg {$x} {$py} 5 {$h} re f\n");

        // Accent bottom border (2 pt)
        $lineY = $py - 1.5;
        $endX  = $x + $w;
        $this->em("q 2 w " . self::ACCENT . " RG {$x} {$lineY} m {$endX} {$lineY} l S Q\n");

        // Title
        $titleY = $py + $h - 26;
        $this->em("BT /F2 " . self::FS_TITLE . " Tf " . self::HDR_FG . " rg " . ($x + 14) . " {$titleY} Td (" . $this->esc($this->docTitle) . ") Tj ET\n");

        // Generated date
        $subY = $py + 10;
        $sub  = $this->esc('Generated: ' . date('d M Y   H:i'));
        $this->em("BT /F1 " . self::FS_SUBTITLE . " Tf " . self::SUB_FG . " rg " . ($x + 14) . " {$subY} Td ({$sub}) Tj ET\n");
    }

    private function drawFooter(int $pageNum, int $total): void
    {
        $x  = self::ML;
        $w  = self::PW - self::ML - self::MR;
        $h  = self::FOOTER_H;
        $fy = self::MB;

        // Footer background strip
        $this->em(self::FOOTER_BG . " rg {$x} {$fy} {$w} {$h} re f\n");

        // Top accent border
        $topY = $fy + $h;
        $endX = $x + $w;
        $this->em("q 1 w " . self::ACCENT . " RG {$x} {$topY} m {$endX} {$topY} l S Q\n");

        $textY = $fy + 7.5;

        // Left note
        $left = $this->esc('Confidential — For internal use only');
        $this->em("BT /F1 " . self::FS_FOOTER . " Tf " . self::MUTED_FG . " rg " . ($x + 4) . " {$textY} Td ({$left}) Tj ET\n");

        // Right page number
        $pgText = "Page {$pageNum} of {$total}";
        $pgW    = $this->textW($pgText, self::FS_FOOTER);
        $pgX    = $x + $w - 4 - $pgW;
        $this->em("BT /F1 " . self::FS_FOOTER . " Tf " . self::MUTED_FG . " rg {$pgX} {$textY} Td (" . $this->esc($pgText) . ") Tj ET\n");
    }

    // ════════════════════════════════════════════════════════════════════════
    // TABLE
    // ════════════════════════════════════════════════════════════════════════

    private function drawTableHeader(): void
    {
        $x  = self::ML;
        $y  = $this->curY;
        $h  = self::THEAD_H;
        $py = self::PH - $y - $h;

        // Background
        $this->em(self::HDR_BG . " rg {$x} {$py} {$this->tableW} {$h} re f\n");

        // Bottom accent line
        $lineY = $py - 1;
        $endX  = $x + $this->tableW;
        $this->em("q 1.5 w " . self::ACCENT . " RG {$x} {$lineY} m {$endX} {$lineY} l S Q\n");

        $cx = $x;
        foreach ($this->headers as $i => $label) {
            $cw = $this->colWidths[$i];
            $this->drawCell($cx, $y, $cw, $h, strtoupper($label), self::FS_TH, true, $this->colAlign[$i] ?? 'L', self::HDR_FG, 5);
            $cx += $cw;
        }

        $this->curY += $h;
    }

    private function drawRow(array $row, int $idx): void
    {
        $this->ensureSpace(self::ROW_H);

        $x  = self::ML;
        $y  = $this->curY;
        $h  = self::ROW_H;
        $py = self::PH - $y - $h;

        // Alternating background
        $bg = ($idx % 2 === 0) ? self::ROW_ODD : self::ROW_EVEN;
        $this->em("{$bg} rg {$x} {$py} {$this->tableW} {$h} re f\n");

        // Bottom separator
        $endX = $x + $this->tableW;
        $this->em("q 0.3 w " . self::BORDER . " RG {$x} {$py} m {$endX} {$py} l S Q\n");

        $cx = $x;
        foreach ($this->headers as $i => $hdr) {
            $cw    = $this->colWidths[$i];
            $value = (string)($row[$i] ?? '');
            $align = $this->colAlign[$i] ?? 'L';
            $color = self::BODY_FG;

            if ($align === 'R' && $value !== '') {
                $clean = str_replace([',', ' ', 'BDT', '৳'], '', $value);
                if (is_numeric($clean) && (float) $clean < 0) {
                    $color = self::RED_FG;
                }
            }

            $this->drawCell($cx, $y, $cw, $h, $value, self::FS_TD, false, $align, $color, 5);
            $cx += $cw;
        }

        $this->curY += $h;
    }

    // ════════════════════════════════════════════════════════════════════════
    // CELL DRAWING
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Draw text inside a cell area (clip + align + truncate).
     *
     * @param float  $x      Left edge of cell
     * @param float  $topY   Top of cell in top-down coordinates
     * @param float  $w      Cell width
     * @param float  $h      Cell height
     * @param string $text   Text content
     * @param float  $fs     Font size in pt
     * @param bool   $bold
     * @param string $align  'L', 'R', or 'C'
     * @param string $color  PDF rg operand (e.g. '0.2 0.2 0.2')
     * @param float  $pad    Horizontal padding
     */
    private function drawCell(
        float  $x,
        float  $topY,
        float  $w,
        float  $h,
        string $text,
        float  $fs,
        bool   $bold,
        string $align,
        string $color,
        float  $pad = 4.0
    ): void {
        if ($text === '') return;

        $maxW = $w - $pad * 2;
        if ($maxW <= 0) return;

        if ($this->textW($text, $fs) > $maxW) {
            $text = $this->truncate($text, $fs, $maxW);
        }

        $tW = $this->textW($text, $fs);

        $tx = match ($align) {
            'R'     => $x + $w - $pad - $tW,
            'C'     => $x + ($w - $tW) / 2,
            default => $x + $pad,
        };

        // Baseline: vertically centred (PDF Y is bottom-up)
        $baseline = self::PH - $topY - $h + $h * 0.32;
        $font     = $bold ? 'F2' : 'F1';

        $this->em("BT /{$font} {$fs} Tf {$color} rg {$tx} {$baseline} Td (" . $this->esc($text) . ") Tj ET\n");
    }

    // ════════════════════════════════════════════════════════════════════════
    // PDF BYTE ASSEMBLY
    // ════════════════════════════════════════════════════════════════════════

    private function compile(): string
    {
        $totalPages = count($this->pages);

        // Draw footers now we know total count
        foreach (array_keys($this->pages) as $pNum) {
            $this->curPage = $pNum;
            $this->drawFooter($pNum, $totalPages);
        }

        $out     = $this->pdfHeader;
        $xrefOff = [];
        $nextId  = 0;

        $addObj = function (string $dict) use (&$out, &$xrefOff, &$nextId): int {
            $nextId++;
            $xrefOff[$nextId] = strlen($out);
            $out .= "{$nextId} 0 obj\n{$dict}\nendobj\n";
            return $nextId;
        };

        $addStream = function (string $raw) use (&$out, &$xrefOff, &$nextId): int {
            $nextId++;
            $xrefOff[$nextId] = strlen($out);
            $comp    = @gzcompress($raw, 6);
            $useComp = ($comp !== false && strlen($comp) < strlen($raw));
            $data    = $useComp ? $comp : $raw;
            $filter  = $useComp ? '/Filter /FlateDecode ' : '';
            $len     = strlen($data);
            $out .= "{$nextId} 0 obj\n<<{$filter}/Length {$len}>>\nstream\n{$data}\nendstream\nendobj\n";
            return $nextId;
        };

        // Reserve 1 & 2 with stubs we'll overwrite via str_replace
        $catalogId = $addObj('<</Type /Catalog /Pages 2 0 R>>');  // obj 1
        $pagesId   = $addObj('<</Type /Pages /Kids [] /Count 0>>'); // obj 2

        // Font objects
        $fontRId = $addObj('<</Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding>>');
        $fontBId = $addObj('<</Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding>>');

        // Page objects
        $pageObjIds = [];
        foreach ($this->pages as $pNum => $content) {
            $resId  = $addObj("<</Font <</F1 {$fontRId} 0 R /F2 {$fontBId} 0 R>>>>");
            $cntId  = $addStream($content);
            $pgId   = $addObj(
                "<</Type /Page /Parent {$pagesId} 0 R " .
                "/MediaBox [0 0 " . self::PW . " " . self::PH . "] " .
                "/Contents {$cntId} 0 R " .
                "/Resources {$resId} 0 R>>"
            );
            $pageObjIds[] = $pgId;
        }

        // Info
        $infoId = $addObj("<</Title (" . $this->esc($this->docTitle) . ") /Creator (SimplePdf 2.0) /CreationDate (D:" . date('YmdHis') . ")>>");

        // Update pages tree (obj 2)
        $kids  = implode(' ', array_map(fn ($id) => "{$id} 0 R", $pageObjIds));
        $count = count($pageObjIds);
        $out   = str_replace(
            "{$pagesId} 0 obj\n<</Type /Pages /Kids [] /Count 0>>\nendobj\n",
            "{$pagesId} 0 obj\n<</Type /Pages /Kids [{$kids}] /Count {$count}>>\nendobj\n",
            $out
        );

        // Rebuild xref with accurate offsets (re-scan after the str_replace)
        preg_match_all('/(\d+) 0 obj\n/', $out, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        $xrefOff = [];
        foreach ($m as $match) {
            $xrefOff[(int)$match[1][0]] = $match[0][1];
        }
        ksort($xrefOff);

        $maxId      = max(array_keys($xrefOff));
        $xrefOffset = strlen($out);

        $out .= "xref\n0 " . ($maxId + 1) . "\n";
        $out .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $maxId; $i++) {
            $out .= isset($xrefOff[$i])
                ? sprintf("%010d 00000 n \n", $xrefOff[$i])
                : "0000000000 65535 f \n";
        }

        $out .= "trailer\n<</Size " . ($maxId + 1) . " /Root 1 0 R /Info {$infoId} 0 R>>\n";
        $out .= "startxref\n{$xrefOffset}\n%%EOF\n";

        return $out;
    }

    // ════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════════════════════

    private function em(string $s): void
    {
        $this->pages[$this->curPage] .= $s;
    }

    private function detectAlignment(array $headers, array $rows): array
    {
        $align = [];
        foreach ($headers as $i => $h) {
            $isNum = true;
            foreach (array_slice($rows, 0, 20) as $row) {
                $v = (string)(array_values((array)$row)[$i] ?? '');
                if ($v === '' || $v === '-') continue;
                $clean = str_replace([',', ' ', 'BDT', '৳'], '', $v);
                if (!is_numeric($clean)) { $isNum = false; break; }
            }
            $align[$i] = $isNum ? 'R' : 'L';
        }
        return $align;
    }

    private function autoWidths(array $headers, array $rows, float $usable): array
    {
        $weights = [];
        foreach ($headers as $i => $h) {
            $maxLen = mb_strlen($h);
            foreach (array_slice($rows, 0, 30) as $row) {
                $v = (string)(array_values((array)$row)[$i] ?? '');
                $maxLen = max($maxLen, min(mb_strlen($v), 30));
            }
            $weights[$i] = $maxLen + 2;
        }
        $total = array_sum($weights);
        return array_map(fn ($w) => round($usable * $w / $total, 2), $weights);
    }

    /**
     * Helvetica character-width table (AFM, units of 1/1000 pt).
     */
    private function textW(string $text, float $fs): float
    {
        static $cw = [
            ' '=>278,'!'=>278,'"'=>355,'#'=>556,'$'=>556,'%'=>889,'&'=>667,"'"=>222,
            '('=>333,')'=>333,'*'=>389,'+'=>584,','=>278,'-'=>333,'.'=>278,'/'=>278,
            '0'=>556,'1'=>556,'2'=>556,'3'=>556,'4'=>556,'5'=>556,'6'=>556,'7'=>556,
            '8'=>556,'9'=>556,':'=>278,';'=>278,'<'=>584,'='=>584,'>'=>584,'?'=>556,
            '@'=>1015,'A'=>667,'B'=>667,'C'=>722,'D'=>722,'E'=>667,'F'=>611,'G'=>778,
            'H'=>722,'I'=>278,'J'=>500,'K'=>667,'L'=>556,'M'=>833,'N'=>722,'O'=>778,
            'P'=>667,'Q'=>778,'R'=>722,'S'=>667,'T'=>611,'U'=>722,'V'=>667,'W'=>944,
            'X'=>667,'Y'=>667,'Z'=>611,'['=>278,'\\'=>278,']'=>278,'^'=>469,'_'=>556,
            '`'=>333,'a'=>556,'b'=>556,'c'=>500,'d'=>556,'e'=>556,'f'=>278,'g'=>556,
            'h'=>556,'i'=>222,'j'=>222,'k'=>500,'l'=>222,'m'=>833,'n'=>556,'o'=>556,
            'p'=>556,'q'=>556,'r'=>333,'s'=>500,'t'=>278,'u'=>556,'v'=>500,'w'=>722,
            'x'=>500,'y'=>500,'z'=>500,'{'=>334,'|'=>260,'}'=>334,'~'=>584,
        ];
        $total = 0;
        $len   = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $total += $cw[$text[$i]] ?? 556;
        }
        return $total * $fs / 1000;
    }

    private function truncate(string $text, float $fs, float $maxW): string
    {
        $ellW  = $this->textW('...', $fs);
        $avail = $maxW - $ellW;
        $out   = '';
        for ($i = 0, $len = strlen($text); $i < $len; $i++) {
            $avail -= $this->textW($text[$i], $fs);
            if ($avail < 0) break;
            $out .= $text[$i];
        }
        return $out . '...';
    }

    /**
     * Escape a value for use in a PDF literal string.
     * Multi-byte characters (e.g. ৳) are converted to ASCII equivalents.
     */
    private function esc(string $s): string
    {
        $s = preg_replace_callback('/[^\x00-\xFF]/', static function ($m) {
            return match ($m[0]) {
                '৳'  => 'BDT ',
                '€'  => 'EUR ',
                '£'  => 'GBP ',
                '¥'  => 'JPY ',
                '—'  => '--',
                '–'  => '-',
                '"'  => '"',
                '"'  => '"',
                '\u2018' => "'",
                '\u2019' => "'",
                default => '?',
            };
        }, $s);
        return str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], $s);
    }
}