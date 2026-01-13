<?php

class AuthController extends Controller {
    public function index() {
        $data = [
            'title' => 'Login',
            'username' => '',
            'password' => '',
            'username_err' => '',
            'password_err' => ''
        ];
        $this->view('auth/login', $data);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process form
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'title' => 'Login',
                'username' => trim($_POST['username']),
                'password' => trim($_POST['password']),
                'username_err' => '',
                'password_err' => ''
            ];

            // Validate Username
            if (empty($data['username'])) {
                $data['username_err'] = 'Please enter username';
            }

            // Validate Password
            if (empty($data['password'])) {
                $data['password_err'] = 'Please enter password';
            }

            // Check for user/email
            $userModel = $this->model('User');
            if ($userModel->findUserByUsername($data['username'])) {
                // User found
            } else {
                $data['username_err'] = 'No user found';
            }

            // Make sure errors are empty
            if (empty($data['username_err']) && empty($data['password_err'])) {
                // Validated
                $loggedInUser = $userModel->login($data['username'], $data['password']);

                if ($loggedInUser) {
                    // Create Session
                    $this->createUserSession($loggedInUser);
                } else {
                    $data['password_err'] = 'Password incorrect';
                    $this->view('auth/login', $data);
                }
            } else {
                // Load view with errors
                $this->view('auth/login', $data);
            }

        } else {
            // Init data
            $data = [
                'title' => 'Login',
                'username' => '',
                'password' => '',
                'username_err' => '',
                'password_err' => ''
            ];

            // Load view
            $this->view('auth/login', $data);
        }
    }

    public function createUserSession($user) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['unit_id'] = $user['unit_id'];

        // Redirect based on role
        if ($user['role'] == 'admin') {
            header('location: ' . URLROOT . '/admin/dashboard');
        } elseif ($user['role'] == 'employee') {
            header('location: ' . URLROOT . '/employee/dashboard');
        } elseif ($user['role'] == 'head') {
            header('location: ' . URLROOT . '/head/dashboard');
        } elseif ($user['role'] == 'management') {
            header('location: ' . URLROOT . '/management/dashboard');
        } else {
             header('location: ' . URLROOT . '/index.php');
        }
    }

    public function logout() {
        session_start();
        unset($_SESSION['user_id']);
        unset($_SESSION['user_username']);
        unset($_SESSION['user_name']);
        unset($_SESSION['role']);
        session_destroy();
        header('location: ' . URLROOT . '/auth/login');
    }
}
