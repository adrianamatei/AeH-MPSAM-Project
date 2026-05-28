<?php
/**
 * Repository: Mesaje
 * Azure: id, tip_mesaj, sursa, destinatie, continut(text), moment_transmitere(datetime)
 * ATENȚIE: Tabela = Mesaje (nu MesajeHL7), fără id_pacient
 */
class MesajHL7Repo {
    
    public static function all() {
        if (isMockMode()) { return array_values($GLOBALS['MOCK_MESAJE_HL7']); }
        return db()->query('SELECT * FROM Mesaje ORDER BY moment_transmitere DESC')->fetchAll();
    }
    
    public static function primite() {
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_MESAJE_HL7'], 
                fn($m) => stripos($m['tip_mesaj'], 'trimitere') !== false));
        }
        return db()->query("SELECT * FROM Mesaje WHERE tip_mesaj LIKE '%trimitere%' ORDER BY moment_transmitere DESC")->fetchAll();
    }
    
    public static function trimise() {
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_MESAJE_HL7'], 
                fn($m) => stripos($m['tip_mesaj'], 'scrisoare') !== false || stripos($m['tip_mesaj'], 'FHIR') !== false));
        }
        return db()->query("SELECT * FROM Mesaje WHERE tip_mesaj LIKE '%scrisoare%' OR tip_mesaj LIKE '%FHIR%' ORDER BY moment_transmitere DESC")->fetchAll();
    }
    
    public static function insert($data) {
        if (isMockMode()) {
            $newId = empty($GLOBALS['MOCK_MESAJE_HL7']) ? 1 : max(array_keys($GLOBALS['MOCK_MESAJE_HL7'])) + 1;
            $data['id'] = $newId;
            $GLOBALS['MOCK_MESAJE_HL7'][$newId] = $data;
            return $newId;
        }
        $stmt = db()->prepare('INSERT INTO Mesaje (tip_mesaj, sursa, destinatie, continut, moment_transmitere) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$data['tip_mesaj'], $data['sursa'], $data['destinatie'], $data['continut'], $data['moment_transmitere']]);
        return (int)db()->lastInsertId();
    }
}
