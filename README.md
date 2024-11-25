**📊 System Zarządzania Klientami i Fakturami**

System do zarządzania danymi klientów, fakturami oraz płatnościami. Projekt umożliwia generowanie raportów takich jak:

- Nadpłaty na kontach klientów.
- Niedopłaty za faktury.
- Nierozliczone faktury po terminie płatności.



**🔧 Funkcjonalności**

- Wyświetlanie danych klientów, faktur oraz płatności.
- Filtrowanie i sortowanie raportów.
- Generowanie raportów nadpłat, niedopłat oraz faktur przeterminowanych.
- Przyjazny interfejs oparty na HTML i CSS.
- Zgodność z PSR-7 i nowoczesnymi standardami PHP 8.



**🛠️ Wymagania**

- PHP w wersji 8.1 lub wyższej.
- Serwer MySQL (lub inny kompatybilny z PDO).
- Composer do zarządzania zależnościami.



**📂 Instalacja**

**1. Sklonuj repozytorium:**

- git clone https://github.com/Bartlomiejste/recruitment-task-main.git
- cd recruitment-task-main


**2. Zainstaluj zależności przez Composer:**

- install composer
  
**3. Skonfiguruj bazę danych:**

- Edytuj plik config/config.php, aby dostosować dane połączenia z bazą danych.
- Uruchom skrypt inicjalizacyjny, aby stworzyć tabelę i wypełnić ją danymi:
  - /recruitment-task-main/setup/setup_database.php

**4. Uruchom lokalny serwer PHP.**

**5. Otwórz aplikację w przeglądarce.**
