<?php
/**
 * Repository: PraguriPacient
 * Azure: id_pacient(PK), max_puls(real), min_puls(real), min_spo2(real), max_temp(real)
 * NOTA: Azure încă are min_spo2 în tabelă dar noi nu-l folosim în interfață
 */
class PraguriRepo {
    
    public static function findByPacient($idPacient) {
        if (isMockMode()) {
            return $GLOBALS['MOCK_PRAGURI_PACIENT'][$idPacient] ?? self::defaultPraguri($idPacient);
        }
        $stmt = db()->prepare('SELECT * FROM PraguriPacient WHERE id_pacient = ?');
        $stmt->execute([$idPacient]);
        $result = $stmt->fetch();
        return $result ?: self::defaultPraguri($idPacient);
    }
    
    public static function defaultPraguri($idPacient) {
        return [
            'id_pacient' => $idPacient,
            'max_puls' => 93.0,
            'min_puls' => 68.0,
            'max_temp' => 38.5,
        ];
    }
    
    public static function upsert($idPacient, $data) {
        if (isMockMode()) {
            $GLOBALS['MOCK_PRAGURI_PACIENT'][$idPacient] = array_merge(
                self::defaultPraguri($idPacient), $data, ['id_pacient' => $idPacient]
            );
            return true;
        }
        $exists = db()->prepare('SELECT 1 FROM PraguriPacient WHERE id_pacient = ?');
        $exists->execute([$idPacient]);
        if ($exists->fetch()) {
            $stmt = db()->prepare('UPDATE PraguriPacient SET max_puls=?, min_puls=?, max_temp=? WHERE id_pacient=?');
            return $stmt->execute([$data['max_puls'] ?? 93.0, $data['min_puls'] ?? 68.0, $data['max_temp'] ?? 38.5, $idPacient]);
        } else {
            $stmt = db()->prepare('INSERT INTO PraguriPacient (id_pacient, max_puls, min_puls, max_temp) VALUES (?, ?, ?, ?)');
            return $stmt->execute([$idPacient, $data['max_puls'] ?? 93.0, $data['min_puls'] ?? 68.0, $data['max_temp'] ?? 38.5]);
        }
    }
}
