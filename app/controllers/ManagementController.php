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
            'today_logbooks' => $logbookModel->countTodayLogbooks(),
            'submitted_today' => $logbookModel->countSubmittedLogbooksToday(),
            'total_employees' => $userModel->countActiveEmployees()
        ];

        $this->view('management/dashboard', $data);
    }
    public function report() {
        $logbookModel = $this->model('Logbook');
        $unitModel = $this->model('Unit');

        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        $unit_id = $_GET['unit_id'] ?? '';

        $logbooks = $logbookModel->getAllLogbooks($start_date, $end_date, $unit_id);
        $units = $unitModel->getAllUnits();

        // Get activities for each logbook to display details
        foreach ($logbooks as &$logbook) {
            $logbook['activities'] = $logbookModel->getActivitiesByLogbookId($logbook['id']);
        }

        $data = [
            'title' => 'Laporan Logbook',
            'logbooks' => $logbooks,
            'units' => $units,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'unit_id' => $unit_id
        ];

        $this->view('management/report', $data);
    }

    public function export() {
        require_once '../vendor/autoload.php';
        
        $logbookModel = $this->model('Logbook');
        
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        $unit_id = $_GET['unit_id'] ?? '';
        $type = $_GET['type'] ?? 'excel';

        $logbooks = $logbookModel->getAllLogbooks($start_date, $end_date, $unit_id);
        
        // Prepare data with activities
        foreach ($logbooks as &$logbook) {
            $logbook['activities'] = $logbookModel->getActivitiesByLogbookId($logbook['id']);
        }

        if ($type == 'excel') {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Header
            $sheet->setCellValue('A1', 'Laporan Logbook Pegawai');
            $sheet->setCellValue('A2', 'Periode: ' . $start_date . ' s/d ' . $end_date);
            
            $headers = ['No', 'Tanggal', 'Nama Pegawai', 'Unit', 'Waktu', 'Kegiatan', 'Output', 'Kendala', 'Status'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '4', $header);
                $col++;
            }

            $row = 5;
            $no = 1;
            foreach ($logbooks as $logbook) {
                if (empty($logbook['activities'])) {
                    $sheet->setCellValue('A' . $row, $no++);
                    $sheet->setCellValue('B' . $row, $logbook['date']);
                    $sheet->setCellValue('C' . $row, $logbook['user_name']);
                    $sheet->setCellValue('D' . $row, $logbook['unit_name']);
                    $sheet->setCellValue('I' . $row, $logbook['status']);
                    $row++;
                } else {
                    foreach ($logbook['activities'] as $activity) {
                        $sheet->setCellValue('A' . $row, $no++);
                        $sheet->setCellValue('B' . $row, $logbook['date']);
                        $sheet->setCellValue('C' . $row, $logbook['user_name']);
                        $sheet->setCellValue('D' . $row, $logbook['unit_name']);
                        $sheet->setCellValue('E' . $row, date('H:i', strtotime($activity['start_time'])) . ' - ' . date('H:i', strtotime($activity['end_time'])));
                        $sheet->setCellValue('F' . $row, $activity['description']);
                        $sheet->setCellValue('G' . $row, $activity['output']);
                        $sheet->setCellValue('H' . $row, $activity['kendala']);
                        $sheet->setCellValue('I' . $row, $logbook['status']);
                        $row++;
                    }
                }
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Laporan_Logbook.xlsx"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;

        } elseif ($type == 'pdf') {
            $mpdf = new \Mpdf\Mpdf(['orientation' => 'L']);
            
            $html = '<h2>Laporan Logbook Pegawai</h2>';
            $html .= '<p>Periode: ' . $start_date . ' s/d ' . $end_date . '</p>';
            $html .= '<table border="1" style="width:100%; border-collapse: collapse; font-size: 12px;">';
            $html .= '<thead><tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Pegawai</th>
                        <th>Unit</th>
                        <th>Waktu</th>
                        <th>Kegiatan</th>
                        <th>Output</th>
                        <th>Kendala</th>
                        <th>Status</th>
                      </tr></thead><tbody>';
            
            $no = 1;
            foreach ($logbooks as $logbook) {
                if (empty($logbook['activities'])) {
                    $html .= '<tr>
                                <td>' . $no++ . '</td>
                                <td>' . $logbook['date'] . '</td>
                                <td>' . $logbook['user_name'] . '</td>
                                <td>' . $logbook['unit_name'] . '</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>' . $logbook['status'] . '</td>
                              </tr>';
                } else {
                    foreach ($logbook['activities'] as $activity) {
                        $html .= '<tr>
                                    <td>' . $no++ . '</td>
                                    <td>' . $logbook['date'] . '</td>
                                    <td>' . $logbook['user_name'] . '</td>
                                    <td>' . $logbook['unit_name'] . '</td>
                                    <td>' . date('H:i', strtotime($activity['start_time'])) . ' - ' . date('H:i', strtotime($activity['end_time'])) . '</td>
                                    <td>' . $activity['description'] . '</td>
                                    <td>' . $activity['output'] . '</td>
                                    <td>' . $activity['kendala'] . '</td>
                                    <td>' . $logbook['status'] . '</td>
                                  </tr>';

                    }
                }
            }
            $html .= '</tbody></table>';
            
            $mpdf->WriteHTML($html);
            $mpdf->Output('Laporan_Logbook.pdf', 'D');
            exit;
        }
    }

    public function charts() {
        $logbookModel = $this->model('Logbook');
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');

        $stats = $logbookModel->getLogbookCountsByUnit($start_date, $end_date);

        // Prepare data for Chart.js
        $labels = [];
        $data_counts = [];
        $background_colors = [];
        
        // Generate random colors
        foreach ($stats as $stat) {
            $labels[] = $stat['unit_name'];
            $data_counts[] = $stat['total'];
            $background_colors[] = '#' . substr(md5($stat['unit_name']), 0, 6);
        }

        $data = [
            'title' => 'Laporan Grafik',
            'start_date' => $start_date,
            'end_date' => $end_date,
            'labels' => json_encode($labels),
            'data_counts' => json_encode($data_counts),
            'background_colors' => json_encode($background_colors)
        ];

        $this->view('management/charts', $data);
    }
}
