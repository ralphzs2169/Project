<?php
require_once __DIR__ . '/../models/Treatment.php';

class TreatmentController
{
    private $treatmentModel;

    public function __construct($pdo)
    {
        $this->treatmentModel = new Treatment($pdo);
    }

    // Adds a new treatment record (expects patient_id and treatment_text from frontend)
    public function add($data)
    {
        // Validates required fields
        if (empty($data['patient_id']) || empty($data['treatment_text'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Missing patient ID or treatment details.']);
            return;
        }

        $treatment_text = json_encode($data['treatment_text']);
        $success = $this->treatmentModel->addTreatment($data['patient_id'], $treatment_text);
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Treatment added successfully.']);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Failed to add treatment.']);
        }
    }

    // Fetches all treatments by patient ID
    public function getByPatient($patient_id)
    {
        if (empty($patient_id)) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Missing patient ID.']);
            return;
        }

        $treatments = $this->treatmentModel->getTreatmentsByPatient($patient_id);

        if ($treatments) {
            echo json_encode($treatments);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['error' => 'No treatments found for this patient.']);
        }
    }
}
