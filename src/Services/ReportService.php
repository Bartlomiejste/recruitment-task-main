<?php

namespace App\Services;

use PDO;
use App\Database;

class ReportService
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Pobiera listę nadpłat.
     */
    public function getOverpayments(string $filter = '', string $sortColumn = 'overpayment', string $sortOrder = 'asc'): array
    {
        $allowedColumns = ['client_name', 'invoice_number', 'overpayment'];
        $sortColumn = in_array($sortColumn, $allowedColumns, true) ? $sortColumn : 'overpayment';
        $sortOrder = $sortOrder === 'desc' ? 'DESC' : 'ASC';

        $sql = "
            SELECT 
                c.name AS client_name, 
                i.number AS invoice_number, 
                SUM(p.amount) - i.total AS overpayment
            FROM 
                payments p
            JOIN 
                invoices i ON p.invoice_id = i.id
            JOIN 
                clients c ON i.client_id = c.id
            GROUP BY 
                i.id
            HAVING 
                overpayment > 0
        ";

        if ($filter) {
            $sql .= " AND c.name LIKE :filter";
        }

        $sql .= " ORDER BY {$sortColumn} {$sortOrder}";

        $stmt = $this->pdo->prepare($sql);

        if ($filter) {
            $stmt->bindValue(':filter', "%{$filter}%");
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Pobiera listę niedopłat.
     */
    public function getUnderpayments(string $filter = '', string $sortColumn = 'underpayment', string $sortOrder = 'asc'): array
    {
        $allowedColumns = ['client_name', 'invoice_number', 'underpayment'];
        $sortColumn = in_array($sortColumn, $allowedColumns, true) ? $sortColumn : 'underpayment';
        $sortOrder = $sortOrder === 'desc' ? 'DESC' : 'ASC';

        $sql = "
            SELECT 
                c.name AS client_name, 
                i.number AS invoice_number, 
                i.total - COALESCE(SUM(p.amount), 0) AS underpayment
            FROM 
                invoices i
            LEFT JOIN 
                payments p ON i.id = p.invoice_id
            JOIN 
                clients c ON i.client_id = c.id
            GROUP BY 
                i.id
            HAVING 
                underpayment > 0
        ";

        if ($filter) {
            $sql .= " AND c.name LIKE :filter";
        }

        $sql .= " ORDER BY {$sortColumn} {$sortOrder}";

        $stmt = $this->pdo->prepare($sql);

        if ($filter) {
            $stmt->bindValue(':filter', "%{$filter}%");
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Pobiera listę przeterminowanych faktur.
     */
    public function getOverdueInvoices(string $filter = '', string $sortColumn = 'due_date', string $sortOrder = 'asc'): array
    {
        $allowedColumns = ['client_name', 'invoice_number', 'due_date', 'unpaid_amount'];
        $sortColumn = in_array($sortColumn, $allowedColumns, true) ? $sortColumn : 'due_date';
        $sortOrder = $sortOrder === 'desc' ? 'DESC' : 'ASC';

        $sql = "
            SELECT 
                c.name AS client_name, 
                i.number AS invoice_number, 
                i.due_date, 
                i.total - COALESCE(SUM(p.amount), 0) AS unpaid_amount
            FROM 
                invoices i
            LEFT JOIN 
                payments p ON i.id = p.invoice_id
            JOIN 
                clients c ON i.client_id = c.id
            WHERE 
                i.due_date < CURDATE()
            GROUP BY 
                i.id
            HAVING 
                unpaid_amount > 0
        ";

        if ($filter) {
            $sql .= " AND c.name LIKE :filter";
        }

        $sql .= " ORDER BY {$sortColumn} {$sortOrder}";

        $stmt = $this->pdo->prepare($sql);

        if ($filter) {
            $stmt->bindValue(':filter', "%{$filter}%");
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
