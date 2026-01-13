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

    public function report() {
        $logbookModel = $this->model('Logbook');
        $user_id = $_SESSION['user_id'];
        
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        
        $logbooks = $logbookModel->getLogbooksByUserIdAndDateRange($user_id, $start_date, $end_date);
        
        // Get activities for each logbook to display details
        foreach ($logbooks as &$logbook) {
            $logbook['activities'] = $logbookModel->getActivitiesByLogbookId($logbook['id']);
        }

        $data = [
            'title' => 'Laporan Logbook',
            'logbooks' => $logbooks,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];

        $this->view('employee/report', $data);
    }

    public function export() {
        require_once '../vendor/autoload.php';
        
        $logbookModel = $this->model('Logbook');
        $user_id = $_SESSION['user_id'];
        
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        
        $logbooks = $logbookModel->getLogbooksByUserIdAndDateRange($user_id, $start_date, $end_date);
        
        // Prepare data with activities
        foreach ($logbooks as &$logbook) {
            $logbook['activities'] = $logbookModel->getActivitiesByLogbookId($logbook['id']);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $sheet->setCellValue('A1', 'Laporan Logbook Pegawai');
        $sheet->setCellValue('A2', 'Nama: ' . $_SESSION['user_name']);
        $sheet->setCellValue('A3', 'Periode: ' . $start_date . ' s/d ' . $end_date);
        
        $headers = ['No', 'Tanggal', 'Waktu', 'Kegiatan', 'Output', 'Kendala', 'Status'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '5', $header);
            $col++;
        }

        $row = 6;
        $no = 1;
        foreach ($logbooks as $logbook) {
            if (empty($logbook['activities'])) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $logbook['date']);
                $sheet->setCellValue('G' . $row, $logbook['status']);
                $row++;
            } else {
                foreach ($logbook['activities'] as $activity) {
                    $sheet->setCellValue('A' . $row, $no++);
                    $sheet->setCellValue('B' . $row, $logbook['date']);
                    $sheet->setCellValue('C' . $row, date('H:i', strtotime($activity['start_time'])) . ' - ' . date('H:i', strtotime($activity['end_time'])));
                    $sheet->setCellValue('D' . $row, $activity['description']);
                    $sheet->setCellValue('E' . $row, $activity['output']);
                    $sheet->setCellValue('F' . $row, $activity['kendala']);
                    $sheet->setCellValue('G' . $row, $logbook['status']);
                    $row++;
                }
            }
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Laporan_Logbook_' . $_SESSION['user_name'] . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
