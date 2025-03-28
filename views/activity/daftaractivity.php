<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Activity Log';
$this->params['breadcrumbs'][] = $this->title;
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
                        setlocale(LC_TIME, 'id_ID.UTF-8', 'Indonesian_indonesia.1252');
                        return strftime('%A, %d %B %Y %H:%M:%S', strtotime($model->created_at));
                    },
                ],                                
            ],
        ]); ?>
    </div>
</div>
