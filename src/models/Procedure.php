<?php

class Procedure
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Add a new procedure record
    public function addProcedure($patient_id, $procedure_name, $procedure_date, $contraption_start_date, $laboratory_diagnostic, $status)
    {
        $stmt = $this->pdo->prepare("INSERT INTO procedures (patient_id, procedure_name, procedure_date, contraption_start_date, laboratory_diagnostic, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        return $stmt->execute([$patient_id, $procedure_name, $procedure_date, $contraption_start_date, $laboratory_diagnostic, $status]);
    }

    // Get all procedures for a specific patient
    public function getProceduresByPatient($patient_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM procedures WHERE patient_id = ? ORDER BY created_at DESC");
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // delete procedure
    public function deleteProcedure($procedureId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM procedures WHERE procedure_id = ?");
        return $stmt->execute([$procedureId]);
    }

    // update procedure
    public function updateProcedure($data)
    {
        $stmt = $this->pdo->prepare("
            UPDATE procedures SET 
                procedure_name = ?, 
                procedure_date = ?, 
                contraption_start_date = ?, 
                laboratory_diagnostic = ?, 
                status = ? 
            WHERE procedure_id = ?
        ");

        return $stmt->execute([
            $data['procedure_name'],
            $data['procedure_date'],
            $data['contraption_start_date'],
            $data['laboratory_diagnostic'],
            $data['status'],
            $data['procedure_id']
        ]);
    }
}
