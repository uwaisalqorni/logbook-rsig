<?php

class ManagementController extends Controller {
    public function __construct() {
        session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'management') {
            header('location: ' . URLROOT . '/auth/login');
            exit;
        }
    }

    public function index() {
        $this->dashboard();
    }

    public function dashboard() {
        $logbookModel = $this->model('Logbook');
        
        $stats = $logbookModel->getLogbookStatsByUnit();
        $recent_logbooks = $logbookModel->getRecentLogbooks();

        $userModel = $this->model('User');
        $unitModel = $this->model('Unit');

        $data = [
            'title' => 'Management Dashboard',
            'stats' => $stats,
            'recent_logbooks' => $recent_logbooks,
            'total_users' => $userModel->countUsers(),
            'total_units' => $unitModel->countUnits(),
            'today_logbooks' => $logbookModel->countTodayLogbooks()
        ];

        $this->view('management/dashboard', $data);
    }
}
