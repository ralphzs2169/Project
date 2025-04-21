<?php
require_once __DIR__ . '/../models/Attachment.php';

class AttachmentController
{
    private $attachmentModel;

    public function __construct($pdo)
    {
        $this->attachmentModel = new Attachment($pdo);
    }

    // Adds a new attachment for a patient
    // Assumes file upload is already handled and path + name are passed to this controller
    public function add()
    {
        if (!isset($_FILES['files'])) {
            echo json_encode(['success' => false, 'error' => 'No files uploaded.']);
            return;
        }

        $files = $_FILES['files']; // This is a multi-file array
        $displayTables = $_POST['display_tables']; // These are parallel arrays to the files
        $patient_id = $_POST['patient_id'];

        $allSuccess = true;

        // Loop through all uploaded files
        for ($i = 0; $i < count($files['name']); $i++) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];

            $display_table = $displayTables[$i] ?? null;

            $success = $this->attachmentModel->addAttachment($patient_id, $file, $display_table);

            if (!$success) {
                $allSuccess = false;
            }
        }

        echo json_encode(['success' => $allSuccess]);
    }

    // Returns all attachments of a patient
    public function getByPatient($patient_id)
    {
        $attachments = $this->attachmentModel->getAttachmentsByPatient($patient_id);
        echo json_encode($attachments);
    }
}
