<?php

class Treatment
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Inserts a new treatment entry for a specific patient
    public function addTreatment($patient_id, $treatment_text)
    {
        // Delete existing treatments for this patient
        $deleteStmt = $this->pdo->prepare("DELETE FROM treatments WHERE patient_id = ?");
        $deleteStmt->execute([$patient_id]);

        // Insert the new treatment
        $insertStmt = $this->pdo->prepare("INSERT INTO treatments (patient_id, treatment_text, created_at) VALUES (?, ?, NOW())");
        return $insertStmt->execute([$patient_id, $treatment_text]);
    }

    // Retrieves all treatments linked to a specific patient, sorted by most recent
    public function getTreatmentsByPatient($patient_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM treatments WHERE patient_id = ? ORDER BY created_at DESC");
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
