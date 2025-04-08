<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use app\models\Kategori;
use app\models\Activity;
use yii\web\NotFoundHttpException;

class KategoriController extends Controller
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

    public function actionDaftarkategori()
    {
        if (!Yii::$app->user->can('admin')) {
            throw new \yii\web\ForbiddenHttpException('Anda bukan admin dan tidak punya izin untuk mengakses kategori.');
        }
        $dataProvider = new ActiveDataProvider([
            'query' => Kategori::find(),
            'pagination' => ['pageSize' => 10],
        ]);

        return $this->render('daftarkategori', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionTambahkategori()
    {
        if (!Yii::$app->user->can('admin')) {
            throw new \yii\web\ForbiddenHttpException('Anda bukan admin dan tidak punya izin untuk mengakses kategori.');
        }
        $model = new Kategori();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // add activity log message
            $activity = new Activity();
            $activity->user_id = Yii::$app->user->identity->id;
            $activity->action_type = 'Create';
            $activity->notes = Yii::$app->user->identity->nama . " menambahkan kategori: " . $model->nama_kategori;
            $activity->save();
            
            Yii::$app->session->setFlash('success', 'Kategori berhasil ditambahkan.');
            return $this->redirect(['daftarkategori']);
        }

        return $this->render('tambahkategori', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        // ga pake
    }

    public function actionUpdate($id)
    {
        // ga apke
    }

    public function actionDelete($id)
    {
        if (!Yii::$app->user->can('admin')) {
            throw new \yii\web\ForbiddenHttpException('Anda bukan admin dan tidak punya izin untuk mengakses kategori.');
        }
        $model = Kategori::findOne($id);
        if ($model) {
            $namaKategori = $model->nama_kategori; 
            $model->delete();

            // add activity log message
            $activity = new Activity();
            $activity->user_id = Yii::$app->user->identity->id;
            $activity->action_type = 'Delete';
            $activity->notes = Yii::$app->user->identity->nama . " menghapus kategori: " . $namaKategori;
            $activity->save();
            Yii::$app->session->setFlash('success', 'Kategori berhasil dihapus.');
        } else {
            Yii::$app->session->setFlash('error', 'Kategori tidak ditemukan.');
        }
        return $this->redirect(['daftarkategori']);
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('User tidak ditemukan.');
    }
}
