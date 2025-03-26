<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;
use app\models\User;
use app\models\BiroPekerjaan;

$this->title = 'Daftar Pengguna';
?>

<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Html::encode($this->title) ?></h3>
        <div class="pull-right">
            <?= Html::a('<i class="fa fa-plus"></i> Tambah Pengguna', ['pengguna/tambahpengguna'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>
    <div class="box-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'id',
                'nama',
                [
                    'attribute' => 'biro_pekerjaan_id',
                    'label' => 'Biro Pekerjaan',
                    'value' => function ($model) {
                        return $model->biroPekerjaan ? $model->biroPekerjaan->nama : '(Tidak Ada)';
                    },
                ],
                'created_at:datetime',
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {delete}',
                    'buttons' => [
                        'update' => function ($url, $model) {
                            return Html::a('<i class="fa fa-pencil"></i> Edit', $url, [
                                'class' => 'btn btn-warning btn-sm',
                                'title' => 'Update Pengguna',
                            ]);
                        },
                        'delete' => function ($url, $model) {
                            return Html::a('<i class="fa fa-trash"></i> Delete', $url, [
                                'class' => 'btn btn-danger btn-sm',
                                'title' => 'Hapus Pengguna',
                                'data' => [
                                    'confirm' => 'Apakah Anda yakin ingin menghapus pengguna ini?',
                                    'method' => 'post',
                                ],
                            ]);
                        },
                    ],
                ],                
            ],
        ]); ?>
    </div>
</div>
