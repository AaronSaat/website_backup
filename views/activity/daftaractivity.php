<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Activity Log';

function formatTanggalIndonesia($tanggal) {
    $hari = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];

    $bulan = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember',
    ];

    $timestamp = strtotime($tanggal);
    $namaHari = $hari[date('l', $timestamp)];
    $tgl = date('d', $timestamp);
    $bln = date('m', $timestamp);
    $thn = date('Y', $timestamp);
    $waktu = date('H:i:s', $timestamp); // Tambahan waktu jam:menit:detik

    return $namaHari . ', ' . $tgl . ' ' . $bulan[$bln] . ' ' . $thn . ' ' . $waktu;
}
?>

<div class="card">
    <div class="card-header
    </div>
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-bordered table-striped'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'action_type',
                    'label' => 'Tipe',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $createdAt = $model->created_at ? date('d-m-Y H:i:s', strtotime($model->created_at)) : '-';
                
                        switch ($model->action_type) {
                            case 'Create':
                                $actionLabel = '<span class="label label-primary">Create</span>';
                                break;
                            case 'Edit':
                                $actionLabel = '<span class="label label-warning">Edit</span>';
                                break;
                            case 'Delete':
                                $actionLabel = '<span class="label label-default">Delete</span>';
                                break;
                            case 'Approve':
                                $actionLabel = '<span class="label label-success">Approve</span>';
                                break;
                            case 'Disapprove':
                                $actionLabel = '<span class="label label-danger">Disapprove</span>';
                                break;
                            case 'Update':
                                $actionLabel = '<span class="label label-info">Update</span>';
                                break;
                            default:
                                $actionLabel = '<span class="label label-default">Unknown</span>';
                                break;
                        }
                
                        return "$actionLabel";
                    }
                ],                                
                [
                    'attribute' => 'notes',
                    'label' => 'Notes',
                    'format' => 'ntext',
                ],
                [
                    'attribute' => 'created_at',
                    'label' => 'Hari, Tanggal, Waktu',
                    'value' => function ($model) {
                        return formatTanggalIndonesia($model->created_at);
                    },
                ],                                
            ],
        ]); ?>
    </div>
</div>
