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
                'only' => ['index'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Hanya untuk user yang login
                    ],
                ],
            ],
        ];
    }

    public function actionDaftaractivity()
    {
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
