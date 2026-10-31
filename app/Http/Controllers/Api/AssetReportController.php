<?php

namespace App\Http\Controllers\Api;

use App\Exports\AssetReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\AssetReportRequest;
use App\Services\AssetReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Excel as ExcelFileType;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Reports\GenerateReportRequest;
use App\Http\Resources\AssetReportResource;
use Illuminate\Http\Request;

class AssetReportController extends Controller
{
    public function __construct(private readonly AssetReportService $assetReportService) {}

    public function index(AssetReportRequest $request): JsonResponse
    {
        try {
            $report = $this->assetReportService->build(
                ...$this->reportArguments($request),
            );

            return $this->sendResponse([
                'period' => $report['period'],
                'filters' => $report['filters'],
                'summary' => $report['summary'],
                'data' => $report['groups'],
            ], 'Asset report generated successfully.');
        } catch (Exception $e) {
            report($e);

            return $this->sendError('Could not generate the asset report.', [], 500);
        }
    }


    public function generate(GenerateReportRequest $request): JsonResponse
    {
        try {
            $report = $this->assetReportService->record(
                $request->validated(),
                $request->user()?->getKey(),
            );

            return $this->sendResponse([
                'item' => new AssetReportResource($report),
            ], 'Report request recorded successfully.', 201);
        } catch (Exception $e) {
            report($e);

            return $this->sendError('Could not record the report request.', [], 500);
        }
    }

    public function history(Request $request): JsonResponse
    {
        try {
            $perPage = max(1, min(100, $request->integer('per_page', 15)));
            $reports = $this->assetReportService->history($perPage);

            $result = [
                'items' => AssetReportResource::collection($reports->items()),
                'meta' => [
                    'current_page' => $reports->currentPage(),
                    'per_page' => $reports->perPage(),
                    'total' => $reports->total(),
                    'last_page' => $reports->lastPage(),
                ],
                'links' => [
                    'first_page_url' => $reports->url(1),
                    'last_page_url' => $reports->url($reports->lastPage()),
                    'next_page_url' => $reports->nextPageUrl(),
                    'prev_page_url' => $reports->previousPageUrl(),
                ],
            ];

            return $this->sendResponse($result, 'Report request history retrieved successfully.');
        } catch (Exception $e) {
            report($e);

            return $this->sendError('Could not retrieve report request history.', [], 500);
        }
    }

    public function export(AssetReportRequest $request): Response
    {
        try {
            $report = $this->assetReportService->build(
                ...$this->reportArguments($request),
            );

            $filename = 'asset-report-' . $report['period']['start_date'] . '-' . $report['period']['end_date'];

            if ($request->exportFormat() === 'pdf') {
                return Pdf::loadView('reports.asset-report-pdf', ['report' => $report])
                    ->setPaper('a4', 'landscape')
                    ->download($filename . '.pdf');
            }

            return Excel::download(
                new AssetReportExport($report),
                $filename . '.xlsx',
                ExcelFileType::XLSX,
            );
        } catch (Exception $e) {
            report($e);

            return $this->sendError('Could not export the asset report.', [], 500);
        }
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable, 2: int|null, 3: int|null}
     */
    private function reportArguments(AssetReportRequest $request): array
    {
        $data = $request->validatedReportData();

        return [
            $data['periodStart'],
            $data['periodEnd'],
            $data['categoryId'],
            $data['assetId'],
        ];
    }
}
