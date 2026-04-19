<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;

class AnalyticsPdfService
{
    /**
     * Render Blade view to PDF. Tries Browsershot if available, falls back to dompdf.
     * Returns local path to generated file.
     */
    public function renderPdf(string $view, array $data, string $relPath): string
    {
        $html = View::make($view, $data)->render();

        // try Browsershot (spatie/browsershot) if installed
        if (class_exists(\Spatie\Browsershot\Browsershot::class)) {
            try {
                \Spatie\Browsershot\Browsershot::html($html)
                    ->save(storage_path('app/' . $relPath));
                return $relPath;
            } catch (\Throwable $e) {
                // fallthrough to dompdf
            }
        }

        // fallback to dompdf
        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->render();
            $output = $dompdf->output();
            $full = storage_path('app/' . $relPath);
            if (!is_dir(dirname($full))) {
                mkdir(dirname($full), 0755, true);
            }
            file_put_contents($full, $output);
            return $relPath;
        }

        throw new \RuntimeException('no_pdf_engine_available');
    }
}
