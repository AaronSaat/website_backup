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
            <label for="filter-biro" style="margin-right: 5px;">Filter Biro Pekerjaan:</label>
            <select id="filter-biro" class="form-control" style="width: 200px; display: inline-block; margin-right: 15px;">
                <option value="">Semua</option>
                <?php foreach ($biroList as $biro): ?>
                    <option value="<?= $biro['id'] ?>" <?= ($biro['id'] == $selectedBiro) ? 'selected' : '' ?>>
                        <?= Html::encode($biro['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
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
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $label = $model->status == 10 ? 'Aktif' : 'Nonaktif';
                        $class = $model->status == 10 ? 'btn btn-success btn-sm' : 'btn btn-default btn-sm';
                        $icon = $model->status == 10 ? 'fa fa-toggle-on' : 'fa fa-toggle-off';
                        $url = ['pengguna/togglestatus', 'id' => $model->id];
                        return Html::a("<i class='{$icon}'></i> {$label}", $url, [
                            'class' => $class,
                            'data' => [
                                'method' => 'post',
                                'confirm' => 'Yakin ingin mengubah status pengguna ini?',
                            ],
                        ]);
                    },
                ],                
            ],
        ]); ?>
    </div>
</div>

<script>
    document.getElementById('filter-biro').addEventListener('change', function() {
        const selectedBiro = this.value;
        const url = new URL(window.location.href);
        url.searchParams.set('biro', selectedBiro);
        window.location.href = url.toString();
    });
</script>