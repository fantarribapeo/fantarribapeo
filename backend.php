<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FantaEuropeo";

// Crea connessione
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica connessione
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Funzione per ottenere la classifica dei teams
if ($_GET['data'] == 'teams') {
    $sql = "SELECT GIOCATORE, PUNTEGGIO_TEAM, 
            (SELECT GROUP_CONCAT(NOME SEPARATOR ',') FROM NAZIONALI WHERE ID IN (NAZIONALE_1, NAZIONALE_2, NAZIONALE_3, NAZIONALE_4)) AS nazionali_nomi,
            (SELECT GROUP_CONCAT(PUNTEGGIO_NAZIONALE SEPARATOR ',') FROM NAZIONALI WHERE ID IN (NAZIONALE_1, NAZIONALE_2, NAZIONALE_3, NAZIONALE_4)) AS nazionali_punteggi, 
			(SELECT GROUP_CONCAT(BANDIERA SEPARATOR ',') FROM NAZIONALI WHERE ID IN (NAZIONALE_1, NAZIONALE_2, NAZIONALE_3, NAZIONALE_4)) AS nazionali_bandiere
            FROM TEAMS ORDER BY PUNTEGGIO_TEAM DESC";
    $result = $conn->query($sql);

    $teams = [];
    while($row = $result->fetch_assoc()) {
        $nazionaliNomi = explode(",", $row['nazionali_nomi']);
        $nazionaliPunteggi = explode(",", $row['nazionali_punteggi']);
		$nazionaliBandiere = explode(",", $row['nazionali_bandiere']);
        $nazionali = [];
        for ($i = 0; $i < count($nazionaliNomi); $i++) {
            $nazionali[] = ['nome' => $nazionaliNomi[$i], 'punteggio' => $nazionaliPunteggi[$i], 'bandiera' => $nazionaliBandiere[$i]];
        }
        $teams[] = [
            'giocatore' => $row['GIOCATORE'],
            'punteggio' => $row['PUNTEGGIO_TEAM'],
            'nazionali' => $nazionali
        ];
    }
    echo json_encode($teams);
}

// Funzione per ottenere le nazionali
if ($_GET['data'] == 'nazionali') {

	// Step 1: Recupera i moltiplicatori
    $sqlMultipliers = "SELECT * FROM MOLTIPLICATORI LIMIT 1";
    $resultMultipliers = $conn->query($sqlMultipliers);
    $multipliers = $resultMultipliers->fetch_assoc();
	
	// Step 2: Recupera nazionali
	$sql = "SELECT * FROM NAZIONALI ORDER BY PUNTEGGIO_NAZIONALE DESC";
    $result = $conn->query($sql);
    $nazionali = [];
    while($row = $result->fetch_assoc()) {
        $details = [];
        
        // Mappa dei dettagli
        $detailsMap = [
            'Vince Partita' => $row['VINCE_PARTITA'] * $multipliers['VINCE_PARTITA'],
            'Pareggia Partita' => $row['PAREGGIA_PARTITA'] * $multipliers['PAREGGIA_PARTITA'],
            'Perde Partita' => $row['PERDE_PARTITA'] * $multipliers['PERDE_PARTITA'],
            'Passaggio Girone' => $row['PASSAGGIO_GIRONE'] * $multipliers['PASSAGGIO_GIRONE'],
            'Passaggio Girone Come Prima' => $row['PASSAGGIO_GIRONE_COME_PRIMA'] * $multipliers['PASSAGGIO_GIRONE_COME_PRIMA'],
            'Non Passa il Girone' => $row['NON_PASSA_IL_GIRONE'] * $multipliers['NON_PASSA_IL_GIRONE'],
            'Passa Turno' => $row['PASSA_TURNO'] * $multipliers['PASSA_TURNO'],
            'Non Passa Turno' => $row['NON_PASSA_TURNO'] * $multipliers['NON_PASSA_TURNO'],
            'Raggiunge la Semifinale' => $row['RAGGIUNGE_LA_SEMIFINALE'] * $multipliers['RAGGIUNGE_LA_SEMIFINALE'],
            'Raggiunge la Finale' => $row['RAGGIUNGE_LA_FINALE'] * $multipliers['RAGGIUNGE_LA_FINALE'],
            'Vince Europeo' => $row['VINCE_EUROPEO'] * $multipliers['VINCE_EUROPEO'],
            'Squadra Squalificata' => $row['SQUADRA_SQUALIFICATA'] * $multipliers['SQUADRA_SQUALIFICATA'],
            'Miglior Giocatore' => $row['MIGLIOR_GIOCATORE'] * $multipliers['MIGLIOR_GIOCATORE'],
            'Miglior Giovane' => $row['MIGLIOR_GIOVANE'] * $multipliers['MIGLIOR_GIOVANE'],
            'Capocannoniere' => $row['CAPOCANNONIERE'] * $multipliers['CAPOCANNONIERE'],
            'Rigore Sbagliato' => $row['RIGORE_SBAGLIATO'] * $multipliers['RIGORE_SBAGLIATO'],
            'Rigore Parato' => $row['RIGORE_PARATO'] * $multipliers['RIGORE_PARATO'],
            'Gol Fatto' => $row['GOL_FATTO'] * $multipliers['GOL_FATTO'],
            'Gol Subito' => $row['GOL_SUBITO'] * $multipliers['GOL_SUBITO'],
            'Espulsione Giocatore' => $row['ESPULSIONE_GIOCATORE'] * $multipliers['ESPULSIONE_GIOCATORE'],
            'Espulsione Allenatore' => $row['ESPULSIONE_ALLENATORE'] * $multipliers['ESPULSIONE_ALLENATORE'],
            'Inno Sbagliato' => $row['INNO_SBAGLIATO'] * $multipliers['INNO_SBAGLIATO'],
            'Invasione di Campo' => $row['INVASIONE_DI_CAMPO'] * $multipliers['INVASIONE_DI_CAMPO'],
            'Invasione di Campo Nudo' => $row['INVASIONE_DI_CAMPO_NUDO'] * $multipliers['INVASIONE_DI_CAMPO_NUDO'],
            'Rimonta Fatta' => $row['RIMONTA_FATTA'] * $multipliers['RIMONTA_FATTA'],
            'Rimonta Subita' => $row['RIMONTA_SUBITA'] * $multipliers['RIMONTA_SUBITA'],
			'Vince con almeno 5 gol di scarto' => $row['VINCE_5_GOL'] * $multipliers['VINCE_5_GOL'],
			'Perde con almeno 5 gol di scarto' => $row['PERDE_5_GOL'] * $multipliers['PERDE_5_GOL']
        ];
        
        // Filtra i dettagli con valore diverso da zero
        foreach ($detailsMap as $key => $value) {
            if ($value != 0) {
                $details[$key] = $value;
            }
        }

        $nazionali[] = [
            'name' => $row['NOME'],
            'score' => $row['PUNTEGGIO_NAZIONALE'],
            'flag' => $row['BANDIERA'],
            'details' => $details
        ];
    }
    echo json_encode($nazionali);
}

// Funzione per ottenere i bonus/malus
if ($_GET['data'] == 'moltiplicatori') {
    $sql = "SELECT * FROM MOLTIPLICATORI LIMIT 1";
    $result = $conn->query($sql);

    $moltiplicatori = [];
    while($row = $result->fetch_assoc()) {
        $moltiplicatori[] = [
			'Vince Partita' => $row['VINCE_PARTITA'],
			'Pareggia Partita' => $row['PAREGGIA_PARTITA'],
			'Perde Partita' => $row['PERDE_PARTITA'],
			'Passaggio Girone' => $row['PASSAGGIO_GIRONE'],
			'Passaggio Girone Come Prima' => $row['PASSAGGIO_GIRONE_COME_PRIMA'],
			'Non Passa il Girone' => $row['NON_PASSA_IL_GIRONE'],
			'Passa Turno' => $row['PASSA_TURNO'],
			'Non Passa Turno' => $row['NON_PASSA_TURNO'],
			'Raggiunge la Semifinale' => $row['RAGGIUNGE_LA_SEMIFINALE'],
			'Raggiunge la Finale' => $row['RAGGIUNGE_LA_FINALE'],
			'Vince Europeo' => $row['VINCE_EUROPEO'],
			'Squadra Squalificata' => $row['SQUADRA_SQUALIFICATA'],
			'Miglior Giocatore' => $row['MIGLIOR_GIOCATORE'],
			'Miglior Giovane' => $row['MIGLIOR_GIOVANE'],
			'Capocannoniere' => $row['CAPOCANNONIERE'],
			'Rigore Sbagliato' => $row['RIGORE_SBAGLIATO'],
			'Rigore Parato' => $row['RIGORE_PARATO'],
			'Gol Fatto' => $row['GOL_FATTO'],
			'Gol Subito' => $row['GOL_SUBITO'],
			'Espulsione Giocatore' => $row['ESPULSIONE_GIOCATORE'],
			'Espulsione Allenatore' => $row['ESPULSIONE_ALLENATORE'],
			'Inno Sbagliato' => $row['INNO_SBAGLIATO'],
			'Invasione di Campo' => $row['INVASIONE_DI_CAMPO'],
			'Invasione di Campo Nudo' => $row['INVASIONE_DI_CAMPO_NUDO'],
			'Rimonta Fatta' => $row['RIMONTA_FATTA'],
			'Rimonta Subita' => $row['RIMONTA_SUBITA'],
			'Vince con almeno 5 gol di scarto' => $row['VINCE_5_GOL'],
			'Perde con almeno 5 gol di scarto' => $row['PERDE_5_GOL']
        ];
    }
	
    echo json_encode($moltiplicatori);
}


// Funzione per ottenere il listone
if ($_GET['data'] == 'listone') {
    $sql = "SELECT * FROM NAZIONALI ORDER BY VALORE DESC";
    $result = $conn->query($sql);

    $nazionali = [];
    while($row = $result->fetch_assoc()) {

        $nazionali[] = [
            'name' => $row['NOME'],
            'valore' => $row['VALORE'],
            'flag' => $row['BANDIERA']
        ];
    }
    echo json_encode($nazionali);
}


// Funzione per calcolare i punteggi
function calcolaPunteggi($conn) {
    // Step 1: Recupera i moltiplicatori
    $sql = "SELECT * FROM MOLTIPLICATORI LIMIT 1";
    $result = $conn->query($sql);
    $multipliers = $result->fetch_assoc();

    // Step 2: Calcola il punteggio di ogni nazionale
    $sql = "SELECT * FROM NAZIONALI";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $punteggio_nazionale = 
            $row['VINCE_PARTITA'] * $multipliers['VINCE_PARTITA'] +
            $row['PAREGGIA_PARTITA'] * $multipliers['PAREGGIA_PARTITA'] +
            $row['PERDE_PARTITA'] * $multipliers['PERDE_PARTITA'] +
            $row['PASSAGGIO_GIRONE'] * $multipliers['PASSAGGIO_GIRONE'] +
            $row['PASSAGGIO_GIRONE_COME_PRIMA'] * $multipliers['PASSAGGIO_GIRONE_COME_PRIMA'] +
            $row['NON_PASSA_IL_GIRONE'] * $multipliers['NON_PASSA_IL_GIRONE'] +
            $row['PASSA_TURNO'] * $multipliers['PASSA_TURNO'] +
            $row['NON_PASSA_TURNO'] * $multipliers['NON_PASSA_TURNO'] +
            $row['RAGGIUNGE_LA_SEMIFINALE'] * $multipliers['RAGGIUNGE_LA_SEMIFINALE'] +
            $row['RAGGIUNGE_LA_FINALE'] * $multipliers['RAGGIUNGE_LA_FINALE'] +
            $row['VINCE_EUROPEO'] * $multipliers['VINCE_EUROPEO'] +
            $row['SQUADRA_SQUALIFICATA'] * $multipliers['SQUADRA_SQUALIFICATA'] +
            $row['MIGLIOR_GIOCATORE'] * $multipliers['MIGLIOR_GIOCATORE'] +
            $row['MIGLIOR_GIOVANE'] * $multipliers['MIGLIOR_GIOVANE'] +
            $row['CAPOCANNONIERE'] * $multipliers['CAPOCANNONIERE'] +
            $row['RIGORE_SBAGLIATO'] * $multipliers['RIGORE_SBAGLIATO'] +
            $row['RIGORE_PARATO'] * $multipliers['RIGORE_PARATO'] +
            $row['GOL_FATTO'] * $multipliers['GOL_FATTO'] +
            $row['GOL_SUBITO'] * $multipliers['GOL_SUBITO'] +
            $row['ESPULSIONE_GIOCATORE'] * $multipliers['ESPULSIONE_GIOCATORE'] +
            $row['ESPULSIONE_ALLENATORE'] * $multipliers['ESPULSIONE_ALLENATORE'] +
            $row['INNO_SBAGLIATO'] * $multipliers['INNO_SBAGLIATO'] +
            $row['INVASIONE_DI_CAMPO'] * $multipliers['INVASIONE_DI_CAMPO'] +
            $row['INVASIONE_DI_CAMPO_NUDO'] * $multipliers['INVASIONE_DI_CAMPO_NUDO'] +
            $row['RIMONTA_FATTA'] * $multipliers['RIMONTA_FATTA'] +
            $row['RIMONTA_SUBITA'] * $multipliers['RIMONTA_SUBITA'] +
            $row['VINCE_5_GOL'] * $multipliers['VINCE_5_GOL'] +
            $row['PERDE_5_GOL'] * $multipliers['PERDE_5_GOL'];

        // Aggiorna il punteggio della nazionale nel database
        $sqlUpdate = "UPDATE NAZIONALI SET PUNTEGGIO_NAZIONALE = $punteggio_nazionale WHERE ID = " . $row['ID'];
        $conn->query($sqlUpdate);
    }

    // Step 4: Calcola il punteggio totale di ogni team
    $sql = "SELECT * FROM TEAMS";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $nazionale1 = $row['NAZIONALE_1'];
        $nazionale2 = $row['NAZIONALE_2'];
        $nazionale3 = $row['NAZIONALE_3'];
        $nazionale4 = $row['NAZIONALE_4'];

        $sqlNazionali = "SELECT PUNTEGGIO_NAZIONALE FROM NAZIONALI WHERE ID IN ($nazionale1, $nazionale2, $nazionale3, $nazionale4)";
        $resultNazionali = $conn->query($sqlNazionali);

        $punteggio_team = 0;
        while ($rowNazionale = $resultNazionali->fetch_assoc()) {
            $punteggio_team += $rowNazionale['PUNTEGGIO_NAZIONALE'];
        }

        // Aggiorna il punteggio del team nel database
        $sqlUpdate = "UPDATE TEAMS SET PUNTEGGIO_TEAM = $punteggio_team WHERE ID = " . $row['ID'];
        $conn->query($sqlUpdate);
    }

    return ['status' => 'success'];
}

// Controlla quale azione è richiesta
if (isset($_GET['action']) && $_GET['action'] == 'calcolaPunteggi') {
    $response = calcolaPunteggi($conn);
    echo json_encode($response);
    exit;
}

$conn->close();
?>
