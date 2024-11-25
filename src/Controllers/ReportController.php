<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Services\ReportService;

class ReportController
{
    private ReportService $reportService;

    public function __construct()
    {
        $this->reportService = new ReportService();
    }

    public function renderReports(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        $html = "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Raporty</title>
            <link rel='stylesheet' href='./style.css'>
        </head>
        <body>
        <h1>Raporty</h1>";

        // Renderowanie tabel
        $html .= $this->renderTableWithFilter(
            "Nadpłaty na koncie klienta",
            'overpayments',
            $queryParams,
            function ($filter, $sortColumn, $sortOrder) {
                return $this->reportService->getOverpayments($filter, $sortColumn, $sortOrder);
            },
            ['client_name', 'invoice_number', 'overpayment'],
            'client_name',
            'asc'
        );

        $html .= $this->renderTableWithFilter(
            "Niedopłaty za faktury",
            'underpayments',
            $queryParams,
            function ($filter, $sortColumn, $sortOrder) {
                return $this->reportService->getUnderpayments($filter, $sortColumn, $sortOrder);
            },
            ['client_name', 'invoice_number', 'underpayment'],
            'underpayment',
            'desc'
        );

        $html .= $this->renderTableWithFilter(
            "Nierozliczone faktury po terminie płatności",
            'overdueInvoices',
            $queryParams,
            function ($filter, $sortColumn, $sortOrder) {
                return $this->reportService->getOverdueInvoices($filter, $sortColumn, $sortOrder);
            },
            ['client_name', 'invoice_number', 'due_date', 'unpaid_amount'],
            'due_date',
            'desc'
        );

        $html .= "</body></html>";

        $response->getBody()->write($html);
        return $response;
    }

    private function renderTableWithFilter(
        string $title,
        string $reportType,
        array $queryParams,
        callable $dataCallback,
        array $columns,
        string $defaultSortColumn = '',
        string $defaultSortOrder = 'asc'
    ): string {
        $filterValue = $queryParams["filter_{$reportType}"] ?? '';
        $currentSortColumn = $queryParams["sort_{$reportType}"] ?? $defaultSortColumn;
        $currentSortOrder = $queryParams["order_{$reportType}"] ?? $defaultSortOrder;
        $nextSortOrder = $currentSortOrder === 'asc' ? 'desc' : 'asc';

        // Pobranie danych
        $data = $dataCallback($filterValue, $currentSortColumn, $currentSortOrder);

        $html = "<h2>{$title}</h2>";
        $html .= "<form method='GET'>";
        $html .= "<input type='hidden' name='sort_{$reportType}' value='{$currentSortColumn}'>";
        $html .= "<input type='hidden' name='order_{$reportType}' value='{$currentSortOrder}'>";
        $html .= "<label for='filter_{$reportType}'>Filtr:</label>";
        $html .= "<input type='text' id='filter_{$reportType}' name='filter_{$reportType}' value='{$filterValue}'>";
        $html .= "<button type='submit'>Filtruj</button>";
        $html .= "</form>";

        if (empty($data)) {
            $html .= "<p>Brak danych do wyświetlenia.</p>";
            return $html;
        }

        $html .= "<table>";
        $html .= "<thead><tr>";
        foreach ($columns as $column) {
            $sortUrl = "?sort_{$reportType}={$column}&order_{$reportType}={$nextSortOrder}&filter_{$reportType}={$filterValue}";
            $html .= "<th><a href='{$sortUrl}'>{$column}</a></th>";
        }
        $html .= "</tr></thead>";
        $html .= "<tbody>";
        foreach ($data as $row) {
            $html .= "<tr>";
            foreach ($row as $cell) {
                $html .= "<td>{$cell}</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</tbody></table>";

        return $html;
    }
}

