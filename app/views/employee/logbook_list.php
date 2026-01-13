<?php require_once '../app/views/layouts/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Riwayat Logbook</h3>
        <div class="card-tools">
            <form action="" method="GET" class="form-inline">
                <div class="form-group mr-2">
                    <label for="start_date" class="mr-2">Dari:</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?php echo $data['start_date']; ?>">
                </div>
                <div class="form-group mr-2">
                    <label for="end_date" class="mr-2">Sampai:</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="<?php echo $data['end_date']; ?>">
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['logbooks'] as $logbook): ?>
                <tr>
                    <td><?php echo date('d F Y', strtotime($logbook['date'])); ?></td>
                    <td>
                        <?php 
                        $status = $logbook['status'];
                        $badgeClass = 'secondary';
                        if ($status == 'submitted') $badgeClass = 'info';
                        if ($status == 'approved') $badgeClass = 'success';
                        if ($status == 'rejected') $badgeClass = 'danger';
                        if ($status == 'revision') $badgeClass = 'warning';
                        ?>
                        <span class="badge badge-<?php echo $badgeClass; ?>"><?php echo ucfirst($status); ?></span>
                    </td>
                    <td>
                        <a href="<?php echo URLROOT; ?>/employee/logbook?date=<?php echo $logbook['date']; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> <?php echo ($logbook['status'] == 'approved' || $logbook['status'] == 'rejected') ? 'Lihat' : 'Lihat / Edit'; ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
