<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Database;

$pdo = Database::getConnection();

// Utworzenie tabel

$pdo->exec("CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    bank_account VARCHAR(26) NOT NULL,
    nip VARCHAR(10) NOT NULL
);

CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    number VARCHAR(50) NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id)
);

CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id)
);

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date DATE NOT NULL,
    bank_account VARCHAR(26) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id)
);");

// Wyczyszczenie istniejących danych
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$pdo->exec("TRUNCATE TABLE payments");
$pdo->exec("TRUNCATE TABLE invoice_items");
$pdo->exec("TRUNCATE TABLE invoices");
$pdo->exec("TRUNCATE TABLE clients");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

// Dodanie klientów
$pdo->exec("
    INSERT INTO clients (name, bank_account, nip) VALUES
    ('Firma A', '1234567890123456', '1234567890'),
    ('Firma B', '9876543210987654', '9876543210'),
    ('Firma C', '1111222233334444', '1111222233'),
    ('Firma X', '1111222233334445', '1111222234')
");

// Dodanie faktur
$pdo->exec("
    INSERT INTO invoices (client_id, number, issue_date, due_date, total) VALUES
    (1, 'FV001', '2024-01-01', '2024-01-31', 1200.00), -- Nadpłata
    (2, 'FV002', '2024-02-01', '2024-02-28', 1500.00), -- Niedopłata
    (3, 'FV003', '2024-03-01', '2024-03-15', 1000.00)  -- Przeterminowana
");

// Dodanie pozycji faktur
$pdo->exec("
    INSERT INTO invoice_items (invoice_id, product_name, quantity, price) VALUES
    (1, 'Produkt A', 2, 300.00),
    (1, 'Produkt B', 2, 300.00),
    (2, 'Produkt C', 3, 500.00),
    (3, 'Produkt D', 2, 500.00)
");

// Dodanie płatności
$pdo->exec("
    INSERT INTO payments (invoice_id, title, amount, payment_date, bank_account) VALUES
    (1, 'Zaliczka', 1300.00, '2024-01-15', '1234567890123456'), -- Nadpłata (1300 - 1200 = 100)
    (2, 'Częściowa wpłata', 1000.00, '2024-02-15', '9876543210987654'), -- Niedopłata (1500 - 1000 = 500)
    (3, 'Zaliczka', 500.00, '2024-03-20', '1111222233334444')  -- Przeterminowana (1000 - 500 = 500)
");

echo "Dane zostały dodane!";