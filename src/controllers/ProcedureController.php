<?php
require_once __DIR__ . '/../models/Procedure.php';

class ProcedureController
{
    private $procedureModel;

    public function __construct($pdo)
    {
        $this->procedureModel = new Procedure($pdo);
    }

    public function add($data)
    {
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data format']);
            return;
        }

        foreach ($data as $entry) {
            if (empty($entry['patient_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Patient ID not found']);
                return;
            }

            $success = $this->procedureModel->addProcedure(
                $entry['patient_id'],
                $entry['procedure_name'] ?? null,
                $entry['procedure_date'] ?? null,
                $entry['contraption_start_date'] ?? null,
                $entry['laboratory_diagnostic'] ?? null,
                $entry['status'] ?? null
            );

            if (!$success) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to insert procedure']);
                return;
            }
        }

        echo json_encode(['success' => true]);
    }

    // Get procedures for a patient
    public function getByPatient($patient_id)
    {
        $procedures = $this->procedureModel->getProceduresByPatient($patient_id);
        echo json_encode($procedures);
    }

    // Delete a procedure
    public function deleteProcedure()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['procedure_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Procedure ID is required']);
            return;
        }

        $procedureId = $data['procedure_id'];
        $result = $this->procedureModel->deleteProcedure($procedureId);

        echo json_encode(['success' => $result]);
    }

    // Update a procedure
    public function updateProcedure()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['procedure_id']) || !isset($data['procedure_name']) || !isset($data['procedure_date']) || !isset($data['contraption_start_date']) || !isset($data['laboratory_diagnostic']) || !isset($data['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'All fields are required']);
            return;
        }

        $result = $this->procedureModel->updateProcedure($data);

        echo json_encode(['success' => $result]);
    }
}
