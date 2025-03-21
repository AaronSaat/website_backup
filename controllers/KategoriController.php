<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use app\models\Kategori;
use yii\web\NotFoundHttpException;

class KategoriController extends Controller
{
    public function actionDaftarkategori()
    {
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
        $model = new Kategori();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Kategori berhasil ditambahkan.');
            return $this->redirect(['daftarkategori']);
        }

        return $this->render('tambahkategori', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new User();

        if ($model->load(Yii::$app->request->post())) {
            $model->password_hash = Yii::$app->security->generatePasswordHash($model->password_hash);
            if ($model->save()) {
                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if (!empty($model->password_hash)) {
                $model->password_hash = Yii::$app->security->generatePasswordHash($model->password_hash);
            }
            if ($model->save()) {
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = Kategori::findOne($id);
        if ($model) {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Kategori berhasil dihapus.');
        } else {
            Yii::$app->session->setFlash('error', 'Kategori tidak ditemukan.');
        }
        return $this->redirect(['kategori/daftarkategori']);
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('User tidak ditemukan.');
    }
}
