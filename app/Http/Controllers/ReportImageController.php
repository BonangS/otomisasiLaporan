<?php

namespace App\Http\Controllers;

use App\Services\ReportImageExtractor;
use App\Services\TraceabilityExcelExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ReportImageController extends Controller
{
    public function create(): View
    {
        return view('welcome');
    }

    public function store(
        Request $request,
        ReportImageExtractor $extractor,
        TraceabilityExcelExporter $exporter,
    ): BinaryFileResponse|RedirectResponse {
        $validated = $request->validate([
            'report_image' => ['required', 'image', 'max:10240'],
        ]);

        try {
            $report = $extractor->extract($validated['report_image']);
            $path = $exporter->export($report);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['report_image' => $exception->getMessage()]);
        }

        return response()->download($path, basename($path));
    }
}
