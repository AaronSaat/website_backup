<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use app\models\User;
use yii\web\NotFoundHttpException;

class PenggunaController extends Controller
{
    public function actionDaftarpengguna()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
            'pagination' => ['pageSize' => 10],
        ]);

        return $this->render('daftarpengguna', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionTambahpengguna()
    {
        $model = new User();

        if ($model->load(Yii::$app->request->post())) {
            $model->created_at = date('Y-m-d H:i:s');

            if (!empty($model->password)) {
                $model->password_hash = Yii::$app->security->generatePasswordHash($model->password);
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Pengguna berhasil ditambahkan.');
                return $this->redirect(['pengguna/daftarpengguna']);
            } else {
                Yii::$app->session->setFlash('error', 'Gagal menambahkan pengguna.');
            }
        }

        return $this->render('tambahpengguna', ['model' => $model]);
    }

    public function actionCreate()
    {
        // ga dipake
    }

    public function actionUpdate($id)
    {
        $model = User::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException("Pengguna tidak ditemukan.");
        }

        if ($model->load(Yii::$app->request->post())) {
            if (!empty($model->password)) {
                $model->password_hash = Yii::$app->security->generatePasswordHash($model->password);
            } else {
                unset($model->password); // Hindari error jika kosong
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Pengguna berhasil diperbarui.');
                return $this->redirect(['pengguna/daftarpengguna']);
            } else {
                Yii::$app->session->setFlash('error', 'Gagal menyimpan perubahan.');
            }
        }

        return $this->render('editpengguna', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        
        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
            'pagination' => ['pageSize' => 10],
        ]);

        return $this->render('daftarpengguna', [
            'dataProvider' => $dataProvider,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('User tidak ditemukan.');
    }
}
