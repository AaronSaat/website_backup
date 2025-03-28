<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\models\Kategori; 
use yii\widgets\LinkPager;

/** @var $model app\models\Laporan */
/** @var $files app\models\File[] */
/** @var $logs app\models\Log[] */

$this->title = 'Detail Laporan';
$this->params['breadcrumbs'][] = ['label' => 'Laporan', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$user = Yii::$app->user->identity;
$isAdmin = isset($user) && $user->username === 'admin';
$isOwner = $model->user_id === $user->id;

// Ambil daftar kategori dari database
$kategoriList = ArrayHelper::map(Kategori::find()->all(), 'id', 'nama_kategori');
?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Html::encode($this->title) ?></h3>
        <div class="pull-right">
            <?php if ($isAdmin && $model->status === 'Waiting for Approval'): ?>
                <?= Html::a('<i class="fa fa-check"></i> Approve', ['approve', 'user_id' => $model->user_id], [
                    'class' => 'btn btn-success',
                    'data' => [
                        'confirm' => 'Apakah Anda yakin ingin menyetujui laporan ini?',
                        'method' => 'post',
                    ],
                ]) ?>
                <?= Html::a('<i class="fa fa-times"></i> Disapprove', ['disapprove', 'user_id' => $model->user_id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Apakah Anda yakin ingin menolak laporan ini?',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                <?= Html::a('<i class="fa fa-trash"></i> Hapus', ['delete', 'user_id' => $model->user_id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Apakah Anda yakin ingin menghapus laporan ini?',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
            <?= Html::a('<i class="fa fa-plus"></i> Tambah Laporan', ['site/tambahlaporan'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <div class="box-body">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
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
                    'value' => function ($model) {
                        setlocale(LC_TIME, 'id_ID.UTF-8', 'Indonesian_indonesia.1252');
                        return strftime('%A, %d %B %Y', strtotime($model->tanggal_backup));
                    }
                ],             
                [
                    'label' => 'Kategori',
                    'format' => 'raw',
                    'value' => function () {
                        $kategoriOptions = ArrayHelper::map(Kategori::find()->all(), 'id', 'nama_kategori');
                
                        return Html::dropDownList(
                            'kategori_id',
                            null, // Tidak mengambil dari Laporan karena sudah dihapus
                            $kategoriOptions,
                            ['class' => 'form-control', 'id' => 'kategori-select']
                        );
                    },
                ],                                                                             
                [
                    'attribute' => 'status',
                    'label' => 'Status',
                    'format' => 'raw',
                    'value' => function($model) {
                        $updatedAt = $model->updated_at ? date('d-m-Y H:i:s', strtotime($model->updated_at)) : '-';
                
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
                                $statusText = "<small><i class='fa fa-calendar'></i> Disapproved at: $updatedAt</small>";
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
                    'label' => 'Catatan Admin',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::tag('div', 
                            '<strong>Catatan Admin:</strong> ' . Html::encode($model->note->notes) . 
                            '<br><small>Ditambahkan pada: ' . date('d-m-Y H:i', strtotime($model->note->created_at)) . '</small>',
                            ['class' => 'alert alert-danger']
                        );
                    },
                    'visible' => ($model->status === 'Disapproved' && isset($model->note->notes)), // Menyembunyikan atribut jika tidak memenuhi kondisi
                ],                                                                         
            ],
        ]) ?>

        <!-- Tabel Log -->
        <div id="log-table">
        <h4>Daftar Log</h4>
        <small class="text-danger font-weight-bold">*macOS membulatkan ukuran file ke atas agar lebih mudah dibaca oleh pengguna. Lihat ukuran byte untuk mengetahui ukuran file asli</small>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal & Waktu</th>
                        <th>Tipe</th>
                        <th>Nama</th>
                        <th>Ukuran (Byte)</th>
                        <th>Ukuran (KB)</th>
                        <th>Ukuran (MB)</th>
                        <th>Tanggal di Approve</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $index => $log): ?>
                        <tr id="log-row-<?= $log->id ?>">
                            <td><?= $index + 1 ?></td>
                            <td><?= Yii::$app->formatter->asDatetime($log->tanggal_waktu, 'php:l, d F Y H:i:s') ?></td>
                            <td><?= Html::encode($log->tipe) ?></td>
                            <td><?= Html::encode($log->nama) ?></td>
                            <td><?= number_format($log->ukuran, 2, ',', '') . ' byte' ?></td>
                            <td><?= number_format($log->ukuran / 1024, 2, ',', '') . ' KB' ?></td>
                            <td><?= number_format($log->ukuran / 1048576, 8, ',', '') . ' MB' ?></td>
                            <td>
                                <?php if (!empty($log->approved_at)): ?>
                                    <?= Yii::$app->formatter->asDatetime($log->approved_at, 'php:l, d F Y H:i:s') ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isAdmin || ($isOwner && in_array($file->status, ['Waiting for Approval', 'Disapprove']))): ?> 
                                    <?= Html::a('<i class="fa fa-trash"></i> Hapus', ['site/deletelog', 'id' => $log->id], [
                                        'class' => 'btn btn-danger btn-sm',
                                        'data' => [
                                            'confirm' => 'Yakin ingin menghapus log ini?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?= LinkPager::widget(['pagination' => $paginationLogs]) ?>
        </div>

        <!-- Tabel File -->
        <div id="file-table">
            <h4 class="mb-0">Daftar File</h4>
            <div class="d-flex align-items-center">
                <label for="filter-file" class="mr-2 mb-0">Filter File:</label>
                <select id="filter-file" class="form-control" style="width: 200px;">
                    <option value="all">Semua</option>
                    <option value="image">Image (JPG, PNG, JPEG)</option>
                    <option value="csv">CSV</option>
                </select>
            </div>

            <!-- kalau mau di ujung kanan, tapi kurang bagus -->
            <!-- <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">Daftar File</h4>
                </div>
                <div class="col-md-6 d-flex justify-content-end">
                    <label for="filter-file" class="mr-2 mb-0 align-self-center">Filter File:</label>
                    <select id="filter-file" class="form-control" style="width: 200px;">
                        <option value="all">Semua</option>
                        <option value="image">Image (JPG, PNG, JPEG)</option>
                        <option value="csv">CSV</option>
                    </select>
                </div>
            </div> -->

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Direktori File</th>
                        <th>Tipe</th>
                        <th>Tanggal Dibuat</th>
                        <th>Tanggal di Approve</th> <!-- Tambahan Kolom -->
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $index => $file): ?>
                        <tr class="file-row" data-type="<?= Html::encode($file->tipe) ?>" id="file-row-<?= $file->id ?>">
                            <td><?= $index + 1 ?></td>
                            <td><?= Html::encode(str_replace('uploads/', '', $file->direktori_file)) ?></td>
                            <td><?= Html::encode($file->tipe) ?></td>
                            <td>
                                <?php 
                                    setlocale(LC_TIME, 'id_ID.UTF-8', 'Indonesian_indonesia.1252'); 
                                    echo strftime('%A, %d %B %Y', strtotime($file->created_at)); 
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($file->approved_at)): ?>
                                    <?= strftime('%A, %d %B %Y %H:%M:%S', strtotime($file->approved_at)) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (in_array($file->tipe, ['jpg', 'jpeg', 'png', 'txt'])): ?>
                                    <?= Html::a('<i class="fa fa-eye"></i> View', Url::to('@web/' . $file->direktori_file), [
                                        'class' => 'btn btn-primary btn-sm',
                                        'target' => '_blank'
                                    ]) ?>
                                <?php elseif ($file->tipe === 'csv'): ?>
                                    <?= Html::a('<i class="fa fa-download"></i> Download', Url::to('@web/' . $file->direktori_file), [
                                        'class' => 'btn btn-success btn-sm',
                                        'download' => true
                                    ]) ?>
                                <?php endif; ?>

                                <?php if ($isAdmin || ($isOwner && in_array($model->status, ['Waiting for Approval', 'Disapprove']))): ?> 
                                    <?= Html::a('<i class="fa fa-trash"></i> Hapus', ['site/deletefile', 'id' => $file->id], [
                                        'class' => 'btn btn-danger btn-sm',
                                        'data' => [
                                            'confirm' => 'Yakin ingin menghapus file ini?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?= LinkPager::widget(['pagination' => $paginationFiles]) ?>
        </div>

    </div>
    <div class="box-footer">
        <?= Html::a('<i class="fa fa-arrow-left"></i> Kembali', ['index'], ['class' => 'btn btn-default']) ?>
    </div>
</div>

<?php
    $script = <<< JS
        $(document).ready(function() {
            function toggleTables() {
                var selectedKategori = $('#kategori-select option:selected').text();
                if (selectedKategori === 'Google Drive') {
                    $('#file-table').removeClass('hidden');
                    $('#log-table').addClass('hidden');
                } else if (selectedKategori === 'HDD / SSD') {
                    $('#log-table').removeClass('hidden');
                    $('#file-table').addClass('hidden');
                } else {
                    $('#file-table, #log-table').addClass('hidden');
                }
            }
            
            $('#kategori-select').change(function() {
                toggleTables();
            });

            toggleTables();
        });

        document.getElementById('filter-file').addEventListener('change', function() {
            var selectedType = this.value;
            var rows = document.querySelectorAll('.file-row');

            rows.forEach(function(row) {
                var fileType = row.getAttribute('data-type');
                if (selectedType === 'all') {
                    row.style.display = '';
                } else if (selectedType === 'image' && ['jpg', 'jpeg', 'png'].includes(fileType)) {
                    row.style.display = '';
                } else if (selectedType === 'csv' && fileType === 'csv') {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    JS;
    $this->registerJs($script);
?>