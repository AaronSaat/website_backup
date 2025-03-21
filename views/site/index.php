<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\grid\ActionColumn;

$this->title = 'Dashboard - Website Laporan Backup';
?>

<div class="row">
    <div class="col-md-6">
        <div class="box box-solid box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Terima kasih telah melakukan backup bulan ini</h3>
            </div>
            <div class="box-body">
                <?= "Today's Date: " . date("d-m-Y") ?>
                <?= "| Last Backup: " . date("d-m-Y") ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="box box-solid box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Mohon melakukan backup bulan ini</h3>
            </div>
            <div class="box-body">
                <?= "Today's Date: " . date("d-m-Y") ?>
                <?= "| Last Backup: " . date("d-m-Y") ?>
            </div>
        </div>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <h3 class="box-title">Laporan Backup</h3>
        <div class="pull-right">
            <label for="filter-biro">Filter Biro Pekerjaan:</label>
            <select id="filter-biro" class="form-control" style="width: 200px; display: inline-block;">
                <option value="">Semua</option>
                <?php foreach ($biroList as $biro): ?>
                    <option value="<?= $biro['id'] ?>" <?= ($biro['id'] == $selectedBiro) ? 'selected' : '' ?>>
                        <?= Html::encode($biro['nama']) ?>
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
                    'value' => function($model) {
                        $buttons = '';
                
                        if ($model->status === 'Waiting for Approval') {
                            $buttons .= Html::a('Approve', ['approve', 'id' => $model->id], ['class' => 'btn btn-success btn-sm']) . ' ';
                            $buttons .= Html::a('Disapprove', ['disapprove', 'id' => $model->id], ['class' => 'btn btn-danger btn-sm']) . ' ';
                        }
                
                        // $buttons .= Html::a('Lihat Detail', ['lihatdetail', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']);
                
                        return $buttons;
                    }
                ],
                [
                    'class' => ActionColumn::class,
                    'template' => '{update} {delete} {view}',
                    'buttons' => [
                        'update' => function ($url, $model) {
                            return in_array($model->status, ['Waiting for Approval', 'Disapproved']) ? 
                                Html::a('<i class="fa fa-pencil"></i>', $url, [
                                    'class' => 'btn btn-warning btn-sm',
                                    'title' => 'Update',
                                ]) : '';
                        },
                        'delete' => function ($url, $model) {
                            return Html::a('<i class="fa fa-trash"></i>', $url, [
                                'class' => 'btn btn-danger btn-sm',
                                'title' => 'Delete',
                                'data' => [
                                    'confirm' => 'Apakah Anda yakin ingin menghapus laporan ini?',
                                    'method' => 'post',
                                ],
                            ]);
                        },
                        'view' => function ($url, $model) {
                            return Html::a('<i class="fa fa-eye"></i>', $url, [
                                'class' => 'btn btn-info btn-sm',
                                'title' => 'Lihat Detail',
                            ]);
                        },
                    ],
                ],                
            ],
        ]); ?>
    </div>
</div>

<?php
    $js = <<<JS
    $('#filter-biro').change(function() {
        let biroId = $(this).val();
        window.location.href = '?biro_id=' + biroId;
    });
    JS;
    $this->registerJs($js);
?>