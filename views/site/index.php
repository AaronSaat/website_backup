<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\grid\ActionColumn;

$this->title = 'Dashboard - Website Laporan Backup';
?>

<div class="row">
    <div class="col-md-6">
        <div class="box box-solid box-<?= $cardType ?>">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?= $cardType === 'success' ? 'Terima kasih telah melakukan backup bulan ini' : 'Mohon melakukan backup bulan ini' ?>
                </h3>
            </div>
            <div class="box-body">
                <?= "Today's Date: " . $today ?>
                <?= " | Last Backup: " . $lastBackupDate ?>
                <br>
                <strong><?= $daysSinceLastBackup !== 'N/A' ? "$daysSinceLastBackup hari semenjak backup terakhir" : "Belum ada backup sebelumnya" ?></strong>
            </div>
        </div>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <h3 class="box-title">Laporan Backup</h3>
        <div class="pull-right">
            <!-- Filter Biro Pekerjaan -->
            <label for="filter-biro" style="margin-right: 5px;">Filter Biro Pekerjaan:</label>
            <select id="filter-biro" class="form-control" style="width: 200px; display: inline-block; margin-right: 15px;">
                <option value="">Semua</option>
                <?php foreach ($biroList as $biro): ?>
                    <option value="<?= $biro['id'] ?>" <?= ($biro['id'] == $selectedBiro) ? 'selected' : '' ?>>
                        <?= Html::encode($biro['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Filter Nama User -->
            <label for="filter-user" style="margin-right: 5px;">Filter Nama User:</label>
            <select id="filter-user" class="form-control" style="width: 200px; display: inline-block;">
                <option value="">Semua</option>
                <?php foreach ($userList as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= ($user['id'] == $selectedUser) ? 'selected' : '' ?>>
                        <?= Html::encode($user['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
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
                    'attribute' => 'kategori_id',
                    'label' => 'Kategori',
                    'value' => function($model) {
                        return $model->kategori->nama_kategori;
                    }
                ],
                [
                    'attribute' => 'status',
                    'label' => 'Status',
                    'format' => 'raw',
                    'value' => function($model) {
                        switch ($model->status) {
                            case 'Approved':
                                return '<span class="label label-success">Approved</span>';
                            case 'Waiting for Approval':
                                return '<span class="label label-warning">Waiting for Approval</span>';
                            case 'Disapproved':
                                return '<span class="label label-danger">Disapproved</span>';
                            default:
                                return '<span class="label label-default">Unknown</span>';
                        }
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
                            $buttons .= Html::a('Approve', ['approve', 'id' => $model->id], ['class' => 'btn btn-success btn-sm']) . ' ';
                            $buttons .= Html::a('Disapprove', ['disapprove', 'id' => $model->id], ['class' => 'btn btn-danger btn-sm']) . ' ';
                        }
                
                        // Jika user adalah admin atau pemilik laporan, tampilkan tombol edit, delete, dan view
                        if (($isAdmin || $isOwner) && in_array($model->status, ['Waiting for Approval', 'Disapproved'])) {
                            $buttons .= Html::a('<i class="fa fa-pencil"></i>', ['update', 'id' => $model->id], [
                                'class' => 'btn btn-warning btn-sm',
                                'title' => 'Update',
                            ]) . ' ';
                        }
                
                        if ($isAdmin || $isOwner) {
                            $buttons .= Html::a('<i class="fa fa-trash"></i>', ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-danger btn-sm',
                                'title' => 'Delete',
                                'data' => [
                                    'confirm' => 'Apakah Anda yakin ingin menghapus laporan ini?',
                                    'method' => 'post',
                                ],
                            ]) . ' ';
                        }
                
                        $buttons .= Html::a('<i class="fa fa-eye"></i>', ['view', 'id' => $model->id], [
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
    // Event listener untuk filter biro dan user
    document.getElementById('filter-biro').addEventListener('change', function () {
        let biroId = this.value;
        let userId = document.getElementById('filter-user').value;
        window.location.href = '<?= Yii::$app->urlManager->createUrl(["site/index"]) ?>' + 
            '?biro=' + biroId + '&user=' + userId;
    });

    document.getElementById('filter-user').addEventListener('change', function () {
        let userId = this.value;
        let biroId = document.getElementById('filter-biro').value;
        window.location.href = '<?= Yii::$app->urlManager->createUrl(["site/index"]) ?>' + 
            '?biro=' + biroId + '&user=' + userId;
    });
</script>