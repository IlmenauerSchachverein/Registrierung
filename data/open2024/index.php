<?php
// Pfad zur CSV-Datei
$csvDateipfad = '/var/www/ilmenauerschachverein/register/data/open2024/open2024.csv';

// Array zum Speichern der FIDE-IDs von der Website
$websiteFideIDs = [];

// URL der Webseite
$url = "https://chess-results.com/tnr796000.aspx?lan=0";

// Verwende cURL, um den Inhalt der Seite abzurufen
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($ch);

if (curl_errno($ch)) {
    echo "cURL-Fehler: " . curl_error($ch);
    curl_close($ch);
    exit();
}
curl_close($ch);

// Lade den HTML-Inhalt in DOMDocument
$dom = new DOMDocument();
@$dom->loadHTML($html);

// XPath verwenden, um die FIDE-IDs von der Webseite zu extrahieren
$xpath = new DOMXPath($dom);
$rows = $xpath->query("//table[@class='CRs1']//tr");

foreach ($rows as $row) {
    $columns = $row->getElementsByTagName('td');
    if ($columns->length > 0) {
        $fideID = trim($columns->item(4)->textContent); // FIDE-ID ist in der 5. Spalte (Index 4)
        $websiteFideIDs[] = $fideID;
    }
}

// Öffnen und Lesen der CSV-Datei
if (($handle = fopen($csvDateipfad, 'r')) !== FALSE) {
    echo '<style>
            table {
                width: 100%;
                border-collapse: collapse;
                font-family: Arial, sans-serif;
                margin: 20px 0;
                box-shadow: 0 2px 3px rgba(0,0,0,0.1);
                table-layout: auto;
            }
            th, td {
                padding: 8px;
                border: 1px solid #ddd;
                text-align: left;
                word-wrap: break-word;
            }
            th {
                background-color: #808080;
                color: white;
            }
            .highlight {
                background-color: red;
                color: white;
            }
            @media screen and (max-width: 600px) {
                table {
                    display: block;
                    overflow-x: auto;
                    white-space: nowrap;
                }
                th, td {
                    font-size: 14px;
                }
            }
        </style>';
    
    echo '<table>';
    
    // Tabellenkopf (Anpassen an die Struktur deiner CSV-Datei)
    echo '<thead>';
    echo '<tr>';
    echo '<th>Anmeldedatum</th>';
    echo '<th>Uhrzeit</th>';
    echo '<th>Vorname</th>';
    echo '<th>Nachname</th>';
    echo '<th>Verein</th>';
    echo '<th>FideID</th>';
    echo '<th>Schachföderation</th>';
    echo '<th>Geburtsjahr</th>';
    echo '<th>Handynummer</th>';
    echo '<th>E-Mail-Adresse</th>';
    echo '<th>Rabattberechtigung</th>';
    echo '<th>Bestätigung gewünscht</th>';
    echo '<th>AGB Zustimmung</th>';
    echo '</tr>';
    echo '</thead>';
    
    echo '<tbody>';
    while (($datenzeile = fgetcsv($handle, 1000, ',')) !== FALSE) {
        $fideID = $datenzeile[5]; // Annahme: FIDE-ID ist in der 6. Spalte (Index 5) der CSV-Datei
        
        // Überprüfen, ob die FIDE-ID auf der Website vorkommt
        $highlight = in_array($fideID, $websiteFideIDs) ? 'highlight' : '';
        
        echo '<tr class="' . $highlight . '">';
        foreach ($datenzeile as $wert) {
            echo '<td>' . htmlspecialchars($wert) . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    
    fclose($handle);
} else {
    echo "Fehler beim Öffnen der CSV-Datei.";
}
?>
