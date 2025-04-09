<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title = 'Daftar Kategori';
?>

<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Html::encode($this->title) ?></h3>
        <div class="pull-right">
            <?php if (Yii::$app->user->can('superadmin')): ?>
                <?= Html::a('<i class="fa fa-plus"></i> Tambah Kategori', ['kategori/tambahkategori'], ['class' => 'btn btn-success']) ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="box-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'id',
                'nama_kategori',
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{delete}',
                    'buttons' => [
                    'delete' => function ($url, $model) {
                            if (Yii::$app->user->can('superadmin')) {
                                return Html::a('<i class="fa fa-trash"></i> Delete', $url, [
                                    'class' => 'btn btn-danger btn-sm',
                                    'title' => 'Hapus Pengguna',
                                    'data' => [
                                        'confirm' => 'Apakah Anda yakin ingin menghapus pengguna ini?',
                                        'method' => 'post',
                                    ],
                                ]);
                            }
                            return ''; // Tidak menampilkan tombol jika bukan superadmin
                        },
                    ],
                ],  
            ],
        ]); ?>
    </div>
</div>
