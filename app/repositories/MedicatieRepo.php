<?php
class MedicatieRepo {
    
    public static function findByPacient($idPacient) {
        if (isMockMode()) return [];
        $stmt = db()->prepare('SELECT * FROM Medicatie WHERE id_pacient = ? AND is_deleted = 0 ORDER BY status ASC, data_inceput DESC');
        $stmt->execute([$idPacient]);
        return $stmt->fetchAll();
    }
    
    public static function findActive($idPacient) {
        if (isMockMode()) return [];
        $stmt = db()->prepare("SELECT * FROM Medicatie WHERE id_pacient = ? AND status = 'activ' AND is_deleted = 0 ORDER BY data_inceput DESC");
        $stmt->execute([$idPacient]);
        return $stmt->fetchAll();
    }
    
    public static function findIstoric($idPacient) {
        if (isMockMode()) return [];
        $stmt = db()->prepare("SELECT * FROM Medicatie WHERE id_pacient = ? AND status != 'activ' AND is_deleted = 0 ORDER BY data_sfarsit DESC");
        $stmt->execute([$idPacient]);
        return $stmt->fetchAll();
    }
    
    public static function findById($id) {
        if (isMockMode()) return null;
        $stmt = db()->prepare('SELECT * FROM Medicatie WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public static function insert($data) {
        if (isMockMode()) return 1;
        $stmt = db()->prepare('INSERT INTO Medicatie 
            (id_pacient, id_medic, id_consultatie, produs, forma_prezentare, doza, posologie, data_inceput, data_sfarsit, data_ultima_prescriere, status, observatii)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['id_pacient'], $data['id_medic'], $data['id_consultatie'] ?? null,
            $data['produs'], $data['forma_prezentare'] ?? null, $data['doza'] ?? null,
            $data['posologie'] ?? null, $data['data_inceput'],
            $data['data_sfarsit'] ?? null, $data['data_ultima_prescriere'] ?? $data['data_inceput'],
            $data['status'] ?? 'activ', $data['observatii'] ?? null,
        ]);
        return (int)db()->lastInsertId();
    }
    
    public static function update($id, $data) {
        if (isMockMode()) return true;
        $old = self::findById($id);
        if ($old) VersionHistoryRepo::saveSnapshot('Medicatie', $id, 'UPDATE', $old, $data);
        
        $stmt = db()->prepare('UPDATE Medicatie SET 
            produs=?, forma_prezentare=?, doza=?, posologie=?, data_inceput=?, data_sfarsit=?, 
            data_ultima_prescriere=?, status=?, observatii=?, data_modificare=GETDATE()
            WHERE id=?');
        return $stmt->execute([
            $data['produs'], $data['forma_prezentare'] ?? null, $data['doza'] ?? null,
            $data['posologie'] ?? null, $data['data_inceput'],
            $data['data_sfarsit'] ?? null, $data['data_ultima_prescriere'] ?? null,
            $data['status'] ?? 'activ', $data['observatii'] ?? null, $id,
        ]);
    }
    
    public static function delete($id) {
        if (isMockMode()) return true;
        $old = self::findById($id);
        if ($old) VersionHistoryRepo::saveSnapshot('Medicatie', $id, 'ARCHIVE', $old);
        return db()->prepare('UPDATE Medicatie SET is_deleted = 1, data_modificare = GETDATE() WHERE id = ?')->execute([$id]);
    }
    
    public static function opreste($id) {
        if (isMockMode()) return true;
        $old = self::findById($id);
        if ($old) VersionHistoryRepo::saveSnapshot('Medicatie', $id, 'STOP', $old, ['status' => 'oprit']);
        return db()->prepare("UPDATE Medicatie SET status = 'oprit', data_sfarsit = GETDATE(), data_modificare = GETDATE() WHERE id = ?")->execute([$id]);
    }
}