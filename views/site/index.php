<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\grid\ActionColumn;

$this->title = 'Dashboard - Website Laporan Backup';

setlocale(LC_TIME, 'id_ID.UTF-8', 'Indonesian_indonesia.1252');
date_default_timezone_set('Asia/Jakarta');
?>

<div class="row">
    <div class="col-md-6">
        <div class="box box-solid box-<?= $cardType ?>">
            <div class="box-header with-border">
                <h3 class="box-title"><?= $message ?></h3>
            </div>
            <div class="box-body">
                <p><strong>Tanggal hari ini: </strong><?= strftime('%A, %d %B %Y', time()) ?></p>

                <?php if ($cardType !== 'warning'): // Tidak tampilkan jika menunggu approval ?>
                    <p><strong>Backup terakhir: </strong><?= $lastBackupDate ? strftime('%A, %d %B %Y', strtotime($lastBackupDate)) : 'Belum ada backup' ?></p>

                    <?php if ($daysSinceLastBackup !== 'N/A'): ?>
                        <p><strong><?= "$daysSinceLastBackup hari semenjak backup terakhir" ?></strong></p>
                    <?php else: ?>
                        <p><strong>Belum ada backup sebelumnya</strong></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <h3 class="box-title">
            Laporan Backup 
            <small class="text-muted">(<?= Html::encode(Yii::$app->user->identity->biroPekerjaan->nama) ?>)</small>
        </h3>
        <div class="pull-right d-flex align-items-center gap-2" style="display: flex; align-items: center; gap: 10px;">
            <!-- Filter Biro Pekerjaan -->
            <?php if (Yii::$app->user->identity->username === 'admin'): ?>
                <label for="filter-biro" style="margin-right: 5px;">Filter Biro Pekerjaan:</label>
                <select id="filter-biro" class="form-control" style="width: 200px; display: inline-block; margin-right: 15px;">
                    <option value="">Semua</option>
                    <?php foreach ($biroList as $biro): ?>
                        <option value="<?= $biro['id'] ?>" <?= ($biro['id'] == $selectedBiro) ? 'selected' : '' ?>>
                            <?= Html::encode($biro['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Search Nama -->
                <form method="GET" style="display: inline-flex;">
                    <input type="text" name="search_nama" value="<?= Html::encode($searchNama) ?>" 
                        class="form-control" placeholder="Cari Nama..." style="width: 200px; margin-right: 10px;">
                    <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Cari</button>
                </form>
            <?php endif; ?>

            <?= Html::a('<i class="fa fa-plus"></i> Tambah Laporan', ['site/tambahlaporan'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <div class="box-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'attribute' => 'user_id',
                    'label' => 'Nama',
                    'value' => function($model) {
                        return $model->user->nama;
                    }
                ],
                [
                    'attribute' => 'biro_pekerjaan',
                    'label' => 'Biro Pekerjaan',
                    'value' => function($model) {
                        return $model->user->biroPekerjaan->nama;
                    }
                ],
                [
                    'attribute' => 'tanggal_backup',
                    'label' => 'Tanggal Backup',
                    'value' => function ($model) {
                        setlocale(LC_TIME, 'id_ID.UTF-8', 'Indonesian_indonesia.1252');
                        return strftime('%A, %d %B %Y', strtotime($model->tanggal_backup));
                    }
                ], 
                [
                    'attribute' => 'status',
                    'label' => 'Status',
                    'format' => 'raw',
                    'value' => function($model) {
                        $updatedAt = $model->updated_at ? date('d-m-Y', strtotime($model->updated_at)) : '-';
                
                        switch ($model->status) {
                            case 'Approved':
                                $statusLabel = '<span class="label label-success">
                                                    <i class="fa fa-check-circle"></i> Approved
                                                </span>';
                                $statusText = "<small><i class='fa fa-calendar-check'></i> Approved at: $updatedAt</small>";
                                break;
                            case 'Waiting for Approval':
                                $statusLabel = '<span class="label label-warning">
                                                    <i class="fa fa-clock-o"></i> Waiting for Approval
                                                </span>';
                                $statusText = "<small><i class='fa fa-calendar'></i> Updated at: $updatedAt</small>";
                                break;
                            case 'Disapproved':
                                $statusLabel = '<span class="label label-danger">
                                                    <i class="fa fa-times-circle"></i> Disapproved
                                                </span>';
                                $statusText = "<small><i class='fa fa-calendar'></i> Disapproved at: $updatedAt</small>
                                               <br><small><i class='fa fa-exclamation-circle'></i> Check notes</small>";
                                break;
                            default:
                                $statusLabel = '<span class="label label-default">
                                                    <i class="fa fa-question-circle"></i> Unknown
                                                </span>';
                                $statusText = "<small><i class='fa fa-calendar'></i> Updated at: $updatedAt</small>";
                                break;
                        }
                
                        return "$statusLabel<br>$statusText";
                    }
                ],                                                                          
                [
                    'label' => 'Action',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $buttons = '';
                
                        $currentUser = Yii::$app->user->identity;
                        $isAdmin = ($currentUser->username === 'admin');
                        $isOwner = ($model->user_id === $currentUser->id);
                
                        // Jika Admin dan status "Waiting for Approval", tampilkan tombol Approve/Disapprove
                        if ($isAdmin && $model->status === 'Waiting for Approval') {
                            $buttons .= Html::a(
                                '<i class="fa fa-check"></i> Approve',
                                ['approve', 'user_id' => $model->user_id],
                                ['class' => 'btn btn-success btn-sm']
                            ) . ' ';
                        
                            $buttons .= Html::a(
                                '<i class="fa fa-times"></i> Disapprove',
                                ['disapprove', 'user_id' => $model->user_id],
                                ['class' => 'btn btn-danger btn-sm']
                            ) . ' ';
                        }                        
                
                        // Jika user adalah admin atau pemilik laporan, tampilkan tombol edit, delete, dan view
                        // if (($isAdmin || $isOwner) && in_array($model->status, ['Waiting for Approval', 'Disapproved'])) {
                        //     $buttons .= Html::a('<i class="fa fa-pencil"></i>', ['update', 'user_id' => $model->user_id], [
                        //         'class' => 'btn btn-warning btn-sm',
                        //         'title' => 'Update',
                        //     ]) . ' ';
                        // }
                
                        if ($isAdmin) {
                            $buttons .= Html::a('<i class="fa fa-trash"></i> Delete', ['delete', 'user_id' => $model->user_id], [
                                'class' => 'btn btn-danger btn-sm',
                                'title' => 'Delete',
                                'data' => [
                                    'confirm' => 'Apakah Anda yakin ingin menghapus laporan ini?',
                                    'method' => 'post',
                                ],
                            ]) . ' ';
                        }
                        
                        $buttons .= Html::a('<i class="fa fa-eye"></i> View Detail', ['view', 'user_id' => $model->user_id], [
                            'class' => 'btn btn-info btn-sm',
                            'title' => 'Lihat Detail',
                        ]);                        
                
                        return $buttons;
                    }
                ],                                            
            ],
        ]); ?>
    </div>
</div>

<script>
    document.getElementById('filter-biro').addEventListener('change', function() {
        const selectedBiro = this.value;
        const searchNama = document.querySelector('input[name="search_nama"]').value;
        const url = new URL(window.location.href);
        url.searchParams.set('biro', selectedBiro);
        url.searchParams.set('search_nama', searchNama);
        window.location.href = url.toString();
    });
</script>