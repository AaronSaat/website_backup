<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use app\models\Biropekerjaan;
use app\models\Activity;
use yii\web\NotFoundHttpException;

class BiroController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        // 'roles' => ['@'], 
                        'roles' => ['admin'], 
                    ],
                ],
            ],
        ];
    }

    public function actionDaftarbiro()
    {
        if (!Yii::$app->user->can('admin')) {
            throw new \yii\web\ForbiddenHttpException('Anda bukan admin dan tidak punya izin untuk mengakses biro pekerjaan.');
        }
        $dataProvider = new ActiveDataProvider([
            'query' => BiroPekerjaan::find(),
            'pagination' => ['pageSize' => 10],
        ]);

        return $this->render('daftarbiro', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionTambahbiro()
    {
        if (!Yii::$app->user->can('admin')) {
            throw new \yii\web\ForbiddenHttpException('Anda bukan admin dan tidak punya izin untuk mengakses biro pekerjaan.');
        }
        $model = new BiroPekerjaan();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // add activity log message
            $activity = new Activity();
            $activity->user_id = Yii::$app->user->identity->id;
            $activity->action_type = 'Create';
            $activity->notes = Yii::$app->user->identity->nama . " menambahkan biro pekerjaan: " . $model->nama;
            $activity->save();
            
            Yii::$app->session->setFlash('success', 'Biro Pekerjaan berhasil ditambahkan.');
            return $this->redirect(['daftarbiro']);
        }

        return $this->render('tambahbiro', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        // ga dipake
    }

    public function actionUpdate($id)
    {
        // ga dipake
    }

    public function actionDelete($id)
    {
        if (!Yii::$app->user->can('admin')) {
            throw new \yii\web\ForbiddenHttpException('Anda bukan admin dan tidak punya izin untuk mengakses biro pekerjaan.');
        }
        $model = BiroPekerjaan::findOne($id);
        if ($model) {
            $namaBiro = $model->nama; 
            $model->delete();

            // add activity log message
            $activity = new Activity();
            $activity->user_id = Yii::$app->user->identity->id;
            $activity->action_type = 'Delete';
            $activity->notes = Yii::$app->user->identity->nama . " menghapus biro pekerjaan: " . $namaBiro;
            $activity->save();
            Yii::$app->session->setFlash('success', 'Biro berhasil dihapus.');
        } else {
            Yii::$app->session->setFlash('error', 'Biro tidak ditemukan.');
        }
        return $this->redirect(['biro/daftarbiro']);
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('User tidak ditemukan.');
    }
}
