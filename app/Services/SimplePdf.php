<?php

namespace App\Services;

/**
 * A minimal, dependency-free PDF writer: builds the raw PDF object/xref
 * syntax by hand (text positioning via BT/Tj, header shading via "re f",
 * grid lines via "m/l/S") rather than pulling in a PDF library — the same
 * low-level technique the original prototype used client-side in JS,
 * ported straight to PHP.
 */
class SimplePdf
{
    public static function fromRows(string $title, string $period, array $rows): string
    {
        $columns = $rows[0] ?? [];
        $bodyRows = array_slice($rows, 1);
        $colCount = max(1, count($columns));

        $pageW = 1600;
        $pageH = 900;
        $left = 30;
        $top = $pageH - 90;
        $rowH = 18;
        $rowsPerPage = max(1, (int) floor(($top - 60) / $rowH));
        $colWidth = ($pageW - $left * 2) / $colCount;
        $fontSize = $colWidth < 90 ? 6.0 : 8.0;

        $safe = fn ($v) => preg_replace('/[^\x20-\x7E]/', '?', (string) ($v ?? ''));
        $pdfEsc = fn ($v) => str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $safe($v));
        $truncate = function ($v, $width, $size) use ($safe) {
            $s = $safe($v);
            $max = max(3, (int) floor($width / ($size * 0.55)));

            return mb_strlen($s) > $max ? mb_substr($s, 0, max(1, $max - 3)).'...' : $s;
        };

        $pages = [];
        $totalPages = max(1, (int) ceil(count($bodyRows) / $rowsPerPage));

        for ($start = 0; $start < count($bodyRows) || $start === 0; $start += $rowsPerPage) {
            $pageRows = array_slice($bodyRows, $start, $rowsPerPage);
            $pageNo = count($pages) + 1;
            $ops = [];
            $ops[] = "BT /F2 16 Tf {$left} ".($pageH - 40)." Td ({$pdfEsc($title)}) Tj ET";
            $ops[] = "BT /F1 9 Tf {$left} ".($pageH - 58)." Td ({$pdfEsc($period)}) Tj ET";

            $rectW = $colWidth * $colCount;
            $ops[] = "0.92 g {$left} {$top} {$rectW} {$rowH} re f 0 g";
            $x = $left;
            foreach ($columns as $c) {
                $ops[] = "BT /F2 {$fontSize} Tf ".($x + 2).' '.($top + 5)." Td ({$pdfEsc($truncate($c, $colWidth, $fontSize))}) Tj ET";
                $x += $colWidth;
            }

            $y = $top;
            $ops[] = "0.65 G 0.5 w {$left} {$y} m ".($left + $rectW)." {$y} l S";
            foreach ($pageRows as $row) {
                $y -= $rowH;
                $x = $left;
                foreach ($row as $v) {
                    $ops[] = "BT /F1 {$fontSize} Tf ".($x + 2).' '.($y + 5)." Td ({$pdfEsc($truncate($v, $colWidth, $fontSize))}) Tj ET";
                    $x += $colWidth;
                }
                $ops[] = "0.85 G 0.35 w {$left} {$y} m ".($left + $rectW)." {$y} l S";
            }

            $x = $left;
            for ($i = 0; $i <= $colCount; $i++) {
                $ops[] = "0.75 G 0.35 w {$x} ".($top + $rowH)." m {$x} {$y} l S";
                $x += $colWidth;
            }

            $ops[] = "BT /F1 8 Tf {$left} 24 Td (Page {$pageNo} of {$totalPages}) Tj ET";
            $pages[] = implode("\n", $ops);

            if (empty($bodyRows)) {
                break;
            }
        }

        return self::assemble($pageW, $pageH, $pages);
    }

    protected static function assemble(int $pageW, int $pageH, array $pages): string
    {
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
        ];

        $kids = [];
        $nextId = 5;
        foreach ($pages as $content) {
            $pageObj = $nextId++;
            $streamObj = $nextId++;
            $kids[] = "{$pageObj} 0 R";
            $objects[$pageObj] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$pageW} {$pageH}] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents {$streamObj} 0 R >>";
            $objects[$streamObj] = '<< /Length '.strlen($content)." >>\nstream\n{$content}\nendstream";
        }
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.count($kids).' >>';

        $pdf = "%PDF-1.4\n%1234\n";
        ksort($objects);
        $maxId = max(array_keys($objects));
        $offsets = [0];
        for ($i = 1; $i <= $maxId; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= "{$i} 0 obj\n{$objects[$i]}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".($maxId + 1)."\n0000000000 65535 f \n";
        for ($i = 1; $i <= $maxId; $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }
        $pdf .= "trailer\n<< /Size ".($maxId + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }
}
