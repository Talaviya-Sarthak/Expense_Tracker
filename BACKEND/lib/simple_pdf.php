<?php

/**
 * SimplePDF: a very small helper for generating basic text PDFs without any external dependencies.
 * The implementation is intentionally minimal—just enough to place text lines on a single A4 page.
 */
class SimplePDF
{
    private array $lines = [];
    private string $title;
    private string $creator;

    public function __construct(string $title = 'Document', string $creator = 'SimplePDF')
    {
        $this->title = $title;
        $this->creator = $creator;
    }

    /**
     * Add a single line to the PDF body. The line should already be formatted as you wish it to appear.
     */
    public function addLine(string $line): void
    {
        $this->lines[] = $line;
    }

    /**
     * Add a blank line (extra vertical space) to the PDF body.
     */
    public function addBlankLine(): void
    {
        $this->lines[] = '';
    }

    /**
     * Generate the PDF as a string (binary). Optionally force download by sending appropriate headers.
     */
    public function output(string $filename = 'document.pdf', bool $forceDownload = true): void
    {
        $pdf = $this->buildPdf();

        if ($forceDownload) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $this->sanitizeFileName($filename) . '"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
        }

        echo $pdf;
    }

    private function buildPdf(): string
    {
        $this->lines = array_values($this->lines);

        $content = $this->buildContentStream();

        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
        $objects[3] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>';
        $objects[4] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
        $objects[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[6] = $this->buildInfoObject();

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $body) {
            $offsets[$index] = strlen($pdf);
            $pdf .= $index . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        }

        $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R /Info 6 0 R >>\n";
        $pdf .= "startxref\n" . $xrefPosition . "\n%%EOF";

        return $pdf;
    }

    private function buildContentStream(): string
    {
        $lines = $this->lines;
        $content = "BT\n/F1 12 Tf\n1 0 0 1 60 780 Tm\n"; // Begin text, font, initial transform matrix

        $leading = 16; // line spacing in points
        $content .= sprintf("%.2F TL\n", $leading); // text leading

        foreach ($lines as $index => $line) {
            $escaped = $this->escapeText($line);
            if ($index === 0) {
                $content .= '(' . $escaped . ") Tj\n";
            } else {
                $content .= '(' . $escaped . ") ' \n"; // Quote operator moves down by leading automatically
            }
        }

        $content .= "ET";

        return $content;
    }

    private function buildInfoObject(): string
    {
        $date = 'D:' . date('YmdHis');
        $info = [
            '/Title (' . $this->escapeText($this->title) . ')',
            '/Creator (' . $this->escapeText($this->creator) . ')',
            '/Producer (SimplePDF)',
            '/CreationDate (' . $date . ')',
        ];

        return '<< ' . implode(' ', $info) . ' >>';
    }

    private function escapeText(string $text): string
    {
        $text = str_replace(["\\", "(", ")"], ["\\\\", "\\(", "\\)"], $text);
        // Strip non-ASCII to stay compatible with this basic implementation
        return preg_replace('/[^\x20-\x7E]/', '', $text);
    }

    private function sanitizeFileName(string $filename): string
    {
        $filename = preg_replace('/[^\w.\-]/', '_', $filename);
        return $filename !== '' ? $filename : 'document.pdf';
    }
}

