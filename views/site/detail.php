<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var $model app\models\Laporan */
$this->title = 'Detail Laporan';
$this->params['breadcrumbs'][] = ['label' => 'Laporan', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$user = Yii::$app->user->identity;
$isAdmin = $user->username === 'admin';
$isOwner = $model->user_id === $user->id;
?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Html::encode($this->title) ?></h3>
        <div class="box-tools pull-right">
            <?php if ($isAdmin || ($isOwner && in_array($model->status, ['Waiting for Approval', 'Disapproved']))): ?>
                <?= Html::a('<i class="fa fa-pencil"></i> Edit', ['update', 'id' => $model->id], [
                    'class' => 'btn btn-warning btn-sm',
                    'title' => 'Edit Laporan',
                ]) ?>
            <?php endif; ?>

            <?php if ($isAdmin || $isOwner): ?>
                <?= Html::a('<i class="fa fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger btn-sm',
                    'title' => 'Hapus Laporan',
                    'data' => [
                        'confirm' => 'Apakah Anda yakin ingin menghapus laporan ini?',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
            <?php if ($isAdmin && $model->status === 'Waiting for Approval'): ?>
                <?= Html::a('<i class="fa fa-check"></i> Approve', ['approve', 'id' => $model->id], [
                    'class' => 'btn btn-success btn-sm',
                    'title' => 'Setujui Laporan',
                    'data' => [
                        'confirm' => 'Apakah Anda yakin ingin menyetujui laporan ini?',
                        'method' => 'post',
                    ],
                ]) ?>

                <?= Html::a('<i class="fa fa-times"></i> Disapprove', ['disapprove', 'id' => $model->id], [
                    'class' => 'btn btn-danger btn-sm',
                    'title' => 'Tolak Laporan',
                    'data' => [
                        'confirm' => 'Apakah Anda yakin ingin menolak laporan ini?',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="box-body">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                [
                    'label' => 'Nama',
                    'value' => $model->user->nama,
                ],
                [
                    'label' => 'Biro Pekerjaan',
                    'value' => $model->user->biroPekerjaan->nama,
                ],
                [
                    'label' => 'Tanggal Backup',
                    'value' => Yii::$app->formatter->asDate($model->tanggal_backup, 'php:d-m-Y'),
                ],
                [
                    'label' => 'Kategori',
                    'value' => $model->kategori->nama_kategori,
                ],
                [
                    'label' => 'Status',
                    'format' => 'raw',
                    'value' => function($model) {
                        $statusClass = $model->status === 'Approved' ? 'success' : ($model->status === 'Disapproved' ? 'danger' : 'warning');
                        return "<span class='label label-{$statusClass}'>" . Html::encode($model->status) . "</span>";
                    },
                ],
                [
                    'label' => 'File Gambar',
                    'format' => 'raw',
                    'value' => function($model) {
                        $output = '';
                        $files = explode(',', $model->file); // Pecah string menjadi array berdasarkan koma
                
                        foreach ($files as $file) {
                            $file = trim($file);
                            if (!empty($file)) {
                                $fileUrl = Yii::getAlias('@web') . '/uploads/' . $file;
                                $output .= Html::a('<i class="fa fa-eye"></i> ' . $file, $fileUrl, [
                                    'class' => 'btn btn-info btn-sm',
                                    'target' => '_blank',
                                    'title' => 'Lihat File',
                                    'style' => 'margin-bottom: 5px; display: block;', // Agar tampil vertikal
                                ]);
                            }
                        }
                
                        return !empty($output) ? $output : '<span class="text-muted">Tidak ada file</span>';
                    },
                ],                
            ],
        ]) ?>
    </div>
    <div class="box-footer">
        <?= Html::a('<i class="fa fa-arrow-left"></i> Kembali', ['index'], ['class' => 'btn btn-default']) ?>
    </div>
</div>