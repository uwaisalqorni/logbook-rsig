<?php require_once '../app/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?php echo $data['total_users']; ?></h3>
                <p>Total Pegawai</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?php echo $data['total_units']; ?></h3>
                <p>Total Unit</p>
            </div>
            <div class="icon">
                <i class="fas fa-hospital"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?php echo $data['today_logbooks']; ?></h3>
                <p>Logbook Hari Ini</p>
            </div>
            <div class="icon">
                <i class="fas fa-book"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Statistik Logbook per Unit (Bulan Ini)</h3>
            </div>
            <div class="card-body">
                <canvas id="logbookChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Logbook Terbaru</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pegawai</th>
                            <th>Unit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['recent_logbooks'] as $logbook): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($logbook['date'])); ?></td>
                            <td><?php echo htmlspecialchars($logbook['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($logbook['unit_name']); ?></td>
                            <td>
                                <?php 
                                $status = $logbook['status'];
                                $badgeClass = 'secondary';
                                if ($status == 'submitted') $badgeClass = 'info';
                                if ($status == 'approved') $badgeClass = 'success';
                                if ($status == 'rejected') $badgeClass = 'danger';
                                ?>
                                <span class="badge badge-<?php echo $badgeClass; ?>"><?php echo ucfirst($status); ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>

<!-- ChartJS -->
<script src="../../adminLTE/plugins/chart.js/Chart.min.js"></script>
<script>
$(function () {
    var donutChartCanvas = $('#logbookChart').get(0).getContext('2d')
    var donutData        = {
      labels: [
          <?php foreach ($data['stats'] as $stat): ?>
            '<?php echo $stat['name']; ?>',
          <?php endforeach; ?>
      ],
      datasets: [
        {
          data: [
            <?php foreach ($data['stats'] as $stat): ?>
                <?php echo $stat['total']; ?>,
            <?php endforeach; ?>
          ],
          backgroundColor : ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'],
        }
      ]
    }
    var donutOptions     = {
      maintainAspectRatio : false,
      responsive : true,
    }
    new Chart(donutChartCanvas, {
      type: 'doughnut',
      data: donutData,
      options: donutOptions
    })
})
</script>
