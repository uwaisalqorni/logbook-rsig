<?php

class AdminController extends Controller {
    public function __construct() {
        // Check if logged in and is admin
        session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
            header('location: ' . URLROOT . '/auth/login');
            exit;
        }
    }

    public function index() {
        $userModel = $this->model('User');
        $unitModel = $this->model('Unit');
        $logbookModel = $this->model('Logbook');

        $data = [
            'title' => 'Admin Dashboard',
            'total_users' => $userModel->countUsers(),
            'total_units' => $unitModel->countUnits(),
            'today_logbooks' => $logbookModel->countTodayLogbooks()
        ];
        $this->view('admin/dashboard', $data);
    }

    public function units() {
        $unitModel = $this->model('Unit');
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            if ($_POST['action'] == 'add') {
                $data = ['name' => trim($_POST['name'])];
                if ($unitModel->addUnit($data)) {
                    $message = 'Unit berhasil ditambahkan';
                }
            } elseif ($_POST['action'] == 'edit') {
                $data = [
                    'id' => $_POST['id'],
                    'name' => trim($_POST['name'])
                ];
                if ($unitModel->updateUnit($data)) {
                    $message = 'Unit berhasil diupdate';
                }
            } elseif ($_POST['action'] == 'delete') {
                if ($unitModel->deleteUnit($_POST['id'])) {
                    $message = 'Unit berhasil dihapus';
                }
            }
        }

        $units = $unitModel->getAllUnits();
        $data = [
            'title' => 'Master Unit',
            'units' => $units,
            'message' => $message
        ];
        $this->view('admin/units', $data);
    }

    public function activity_types() {
        $activityModel = $this->model('ActivityType');
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            if ($_POST['action'] == 'add') {
                $data = ['name' => trim($_POST['name'])];
                if ($activityModel->addActivityType($data)) {
                    $message = 'Jenis Kegiatan berhasil ditambahkan';
                }
            } elseif ($_POST['action'] == 'edit') {
                $data = [
                    'id' => $_POST['id'],
                    'name' => trim($_POST['name'])
                ];
                if ($activityModel->updateActivityType($data)) {
                    $message = 'Jenis Kegiatan berhasil diupdate';
                }
            } elseif ($_POST['action'] == 'delete') {
                if ($activityModel->deleteActivityType($_POST['id'])) {
                    $message = 'Jenis Kegiatan berhasil dihapus';
                }
            }
        }

        $activities = $activityModel->getAllActivityTypes();
        $data = [
            'title' => 'Master Jenis Kegiatan',
            'activities' => $activities,
            'message' => $message
        ];
        $this->view('admin/activity_types', $data);
    }

    public function users() {
        $userModel = $this->model('User');
        $unitModel = $this->model('Unit');
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            if ($_POST['action'] == 'add') {
                $data = [
                    'nik' => trim($_POST['nik']),
                    'name' => trim($_POST['name']),
                    'username' => trim($_POST['username']),
                    'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                    'role' => $_POST['role'],
                    'unit_id' => $_POST['unit_id'] ?: NULL,
                    'position' => $_POST['position'],
                    'golongan' => $_POST['golongan']
                ];
                if ($userModel->addUser($data)) {
                    $message = 'Pegawai berhasil ditambahkan';
                }
            } elseif ($_POST['action'] == 'edit') {
                $data = [
                    'id' => $_POST['id'],
                    'nik' => trim($_POST['nik']),
                    'name' => trim($_POST['name']),
                    'username' => trim($_POST['username']),
                    'role' => $_POST['role'],
                    'unit_id' => $_POST['unit_id'] ?: NULL,
                    'position' => $_POST['position'],
                    'golongan' => $_POST['golongan'],
                    'status' => $_POST['status'],
                    'password' => !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : ''
                ];
                if ($userModel->updateUser($data)) {
                    $message = 'Pegawai berhasil diupdate';
                }
            } elseif ($_POST['action'] == 'delete') {
                if ($userModel->deleteUser($_POST['id'])) {
                    $message = 'Pegawai berhasil dihapus';
                }
            }
        }

        $users = $userModel->getAllUsers();
        $units = $unitModel->getAllUnits();
        $data = [
            'title' => 'Master Pegawai',
            'users' => $users,
            'units' => $units,
            'message' => $message
        ];
        $this->view('admin/users', $data);
    }
}
