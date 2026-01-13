<?php

class EmployeeController extends Controller {
    public function __construct() {
        session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
            header('location: ' . URLROOT . '/auth/login');
            exit;
        }
    }

    public function index() {
        $this->dashboard();
    }

    public function dashboard() {
        $logbookModel = $this->model('Logbook');
        $user_id = $_SESSION['user_id'];
        
        // Get stats
        $total_logbooks = $logbookModel->countLogbooksByUser($user_id);
        
        $data = [
            'title' => 'Employee Dashboard',
            'total_logbooks' => $total_logbooks
        ];
        $this->view('employee/dashboard', $data);
    }

    public function logbook() {
        $logbookModel = $this->model('Logbook');
        $activityTypeModel = $this->model('ActivityType');

        $user_id = $_SESSION['user_id'];
        $date = $_GET['date'] ?? date('Y-m-d');
        $message = '';

        // Handle Form Submission
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $date = $_POST['date'];

            // Find or Create Logbook
            $logbook = $logbookModel->getLogbookByUserAndDate($user_id, $date);
            if (!$logbook) {
                $logbook_id = $logbookModel->createLogbook($user_id, $date);
            } else {
                $logbook_id = $logbook['id'];
            }

            // Check if logbook is finalized
            if ($logbook && ($logbook['status'] == 'approved' || $logbook['status'] == 'rejected')) {
                $message = 'Logbook sudah difinalisasi dan tidak dapat diubah.';
            } else {
                if ($_POST['action'] == 'add_activity') {
                $data = [
                    'logbook_id' => $logbook_id,
                    'activity_type_id' => $_POST['activity_type_id'],
                    'description' => $_POST['description'],
                    'start_time' => $_POST['start_time'],
                    'end_time' => $_POST['end_time'],
                    'output' => $_POST['output'],
                    'kendala' => $_POST['kendala']
                ];
                if ($logbookModel->addActivity($data)) {
                    $message = 'Kegiatan berhasil ditambahkan';
                }
            } elseif ($_POST['action'] == 'update_activity') {
                $data = [
                    'id' => $_POST['detail_id'],
                    'activity_type_id' => $_POST['activity_type_id'],
                    'description' => $_POST['description'],
                    'start_time' => $_POST['start_time'],
                    'end_time' => $_POST['end_time'],
                    'output' => $_POST['output'],
                    'kendala' => $_POST['kendala']
                ];
                if ($logbookModel->updateActivity($data)) {
                    $message = 'Kegiatan berhasil diperbarui';
                    // Clear edit mode
                    header("Location: " . URLROOT . "/employee/logbook?date=" . $date);
                    exit;
                }
            } elseif ($_POST['action'] == 'delete_activity') {
                if ($logbookModel->deleteActivity($_POST['detail_id'])) {
                    $message = 'Kegiatan dihapus';
                }
            } elseif ($_POST['action'] == 'submit_logbook') {
                if ($logbookModel->updateStatus($logbook_id, 'submitted')) {
                    $message = 'Logbook berhasil dikirim';
                }
            }
            } // End if not finalized
        }

        // Fetch Data for View
        $logbook = $logbookModel->getLogbookByUserAndDate($user_id, $date);
        $activities = [];
        if ($logbook) {
            $activities = $logbookModel->getActivitiesByLogbookId($logbook['id']);
        }
        $activity_types = $activityTypeModel->getAllActivityTypes();

        // Check for Edit Mode
        $edit_data = [];
        if (isset($_GET['edit_id'])) {
            $edit_data = $logbookModel->getActivityById($_GET['edit_id']);
        }

        // Get previous validation notes if any
        $validation = $logbookModel->getValidationByLogbookId($logbook['id'] ?? 0);

        $data = [
            'title' => 'Input Logbook',
            'date' => $date,
            'logbook' => $logbook,
            'activities' => $activities,
            'activity_types' => $activity_types,
            'message' => $message,
            'edit_data' => $edit_data,
            'validation' => $validation
        ];

        $this->view('employee/logbook_form', $data);
    }

    public function history() {
        $logbookModel = $this->model('Logbook');
        $user_id = $_SESSION['user_id'];
        
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        
        $logbooks = $logbookModel->getLogbooksByUserIdAndDateRange($user_id, $start_date, $end_date);

        $data = [
            'title' => 'Riwayat Logbook',
            'logbooks' => $logbooks,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];

        $this->view('employee/logbook_list', $data);
    }
}
