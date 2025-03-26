<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title = 'Daftar Biro Pekerjaan';
?>

<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Html::encode($this->title) ?></h3>
        <div class="pull-right">
            <?= Html::a('<i class="fa fa-plus"></i> Tambah Biro Pekerjaan', ['biro/tambahbiro'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>
    <div class="box-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'id',
                'nama',
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{delete}',
                    'buttons' => [
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
