<?php

class HeadController extends Controller {
    public function __construct() {
        session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'head') {
            header('location: ' . URLROOT . '/auth/login');
            exit;
        }
    }

    public function index() {
        $this->dashboard();
    }

    public function dashboard() {
        $data = ['title' => 'Head Dashboard'];
        $this->view('head/dashboard', $data);
    }

    public function validation() {
        $logbookModel = $this->model('Logbook');
        $unit_id = $_SESSION['unit_id'];

        $pending_logbooks = $logbookModel->getPendingLogbooksByUnit($unit_id);
        $history_logbooks = $logbookModel->getHistoryLogbooksByUnit($unit_id);

        $data = [
            'title' => 'Validasi Logbook',
            'pending_logbooks' => $pending_logbooks,
            'history_logbooks' => $history_logbooks
        ];

        $this->view('head/validation_list', $data);
    }

    public function detail($id) {
        $logbookModel = $this->model('Logbook');
        $userModel = $this->model('User'); // Assuming we might need user details, but logbook model join handles it mostly
        
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $status = $_POST['status'];
            $notes = $_POST['notes'];
            
            if ($logbookModel->updateStatus($id, $status)) {
                // Add validation record
                $validationData = [
                    'logbook_id' => $id,
                    'validator_id' => $_SESSION['user_id'],
                    'status' => $status,
                    'notes' => $notes
                ];
                $logbookModel->addValidation($validationData);

                $message = 'Logbook berhasil divalidasi: ' . ucfirst($status);
                // Redirect back to list
                header('location: ' . URLROOT . '/head/validation');
                exit;
            }
        }

        $logbook = $logbookModel->getLogbookById($id);
        $activities = $logbookModel->getActivitiesByLogbookId($id);
        
        // Get User Name (Quick fix: query user manually or rely on join in getLogbookById if modified, 
        // but getLogbookById is simple select *. Let's fetch user separately or trust the view to handle it if passed)
        // Better: Fetch user details for display
        // $user = $userModel->getUserById($logbook['user_id']); // Need to implement getUserById in User model if needed
        
        // For now, let's just pass logbook and activities. The view might need user name.
        // Let's add a method to get Logbook with User details in Model if strictly needed, 
        // or just fetch user here.
        // $user = $this->model('User')->getUserById($logbook['user_id']); 
        
        // Get previous validation notes if any
        $validation = $logbookModel->getValidationByLogbookId($id);

        $data = [
            'title' => 'Detail Validasi',
            'logbook' => $logbook,
            'activities' => $activities,
            'validation' => $validation,
            'message' => $message
        ];

        $this->view('head/validation_detail', $data);
    }
}
