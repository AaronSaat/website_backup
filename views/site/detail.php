<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\models\Kategori; 

/** @var $model app\models\Laporan */
/** @var $files app\models\File[] */
/** @var $logs app\models\Log[] */

$this->title = 'Detail Laporan';
$this->params['breadcrumbs'][] = ['label' => 'Laporan', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$user = Yii::$app->user->identity;
$isAdmin = $user->username === 'admin';
$isOwner = $model->user_id === $user->id;

// Ambil daftar kategori dari database
$kategoriList = ArrayHelper::map(Kategori::find()->all(), 'id', 'nama_kategori');
?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Html::encode($this->title) ?></h3>
        <div class="pull-right">
            <?= Html::a('<i class="fa fa-plus"></i> Tambah Laporan', ['site/tambahlaporan'], ['class' => 'btn btn-primary']) ?>
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
                    'label' => 'Status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $icon = '';
                        switch ($model->status) {
                            case 'Approved':
                                $statusClass = 'success';
                                $icon = '<i class="fa fa-check-circle"></i> ';
                                break;
                            case 'Waiting for Approval':
                                $statusClass = 'warning';
                                $icon = '<i class="fa fa-clock-o"></i> ';
                                break;
                            case 'Disapproved':
                                $statusClass = 'danger';
                                $icon = '<i class="fa fa-times-circle"></i> ';
                                break;
                            default:
                                $statusClass = 'default';
                                $icon = '<i class="fa fa-question-circle"></i> ';
                                break;
                        }
                
                        $statusLabel = "<span class='label label-{$statusClass}'>" . $icon . Html::encode($model->status) . "</span>";
                        $updatedAt = $model->updated_at ? "<br><i class='fa fa-calendar'></i><small> Updated: " . date('d-m-Y', strtotime($model->updated_at)) . "</small>" : '';
                        $adminNotes = $model->status === 'Disapproved' ? "<br><small>Check admin notes</small>" : '';
                
                        return $statusLabel . $updatedAt . $adminNotes;
                    },
                ],                
            ],
        ]) ?>

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
                        <th>Created At</th>
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
                                <?php if (in_array($file->tipe, ['jpg', 'jpeg', 'png'])): ?>
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
                                
                                <?php if ($isAdmin || $isOwner): ?>
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
        </div>

        <!-- Tabel Log -->
        <div id="log-table">
            <h4>Daftar Log</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal & Waktu</th>
                        <th>Tipe</th>
                        <th>Nama</th>
                        <th>Ukuran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $index => $log): ?>
                        <tr id="log-row-<?= $log->id ?>">
                            <td><?= $index + 1 ?></td>
                            <td><?= Yii::$app->formatter->asDatetime($log->tanggal_waktu, 'php:l, d F Y H:i') ?></td>
                            <td><?= Html::encode($log->tipe) ?></td>
                            <td><?= Html::encode($log->nama) ?></td>
                            <td><?= number_format($log->ukuran, 2, ',', '') . ' KB' ?></td>
                            <td>
                                <?php if ($isAdmin || $isOwner): ?>
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