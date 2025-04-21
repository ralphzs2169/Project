<?php

class Attachment
{

    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Adds a new file attachment for a specific patient
    public function addAttachment($patient_id, $file, $display_table)
    {
        $uploadDir = '/kardex_system/src/public/uploads/';
        $uniqueFileName = uniqid() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $uniqueFileName;

        // Move the file to the upload directory
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $targetPath;
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return false; // Upload failed
        }

        // Store only relative path (e.g. /kardex_system/src/public/uploads/abc.jpg)
        $stmt = $this->pdo->prepare("INSERT INTO attachments (patient_id, file_name, file_path, uploaded_at, display_table) VALUES (?, ?, ?, NOW(), ?)");
        return $stmt->execute([
            $patient_id,
            $file['name'],        // original filename
            $targetPath,           // relative path
            $display_table
        ]);
    }

    // Retrieves all attachments for a given patient
    public function getAttachmentsByPatient($patient_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM attachments WHERE patient_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll();
    }
}
