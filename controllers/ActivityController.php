<?php

namespace app\controllers;

use Yii;
use app\models\Activity;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\filters\AccessControl;

class ActivityController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionDaftaractivity()
    {
        if (!Yii::$app->user->can('activity')) {
            throw new \yii\web\ForbiddenHttpException('Anda belum login dan tidak punya izin untuk mengakses activity.');
        }
        setlocale(LC_TIME, 'id_ID.UTF-8');
        $userId = Yii::$app->user->id;

        $dataProvider = new ActiveDataProvider([
            'query' => Activity::find()->where(['user_id' => $userId])->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 50],
        ]);

        return $this->render('daftaractivity', [
            'dataProvider' => $dataProvider,
        ]);
    }
}
