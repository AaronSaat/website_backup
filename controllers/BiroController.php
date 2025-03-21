<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use app\models\Biropekerjaan;
use yii\web\NotFoundHttpException;

class BiroController extends Controller
{
    public function actionDaftarbiro()
    {
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
        $model = new BiroPekerjaan();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Biro Pekerjaan berhasil ditambahkan.');
            return $this->redirect(['daftarbiro']);
        }

        return $this->render('tambahbiro', [
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
        $model = BiroPekerjaan::findOne($id);
        if ($model) {
            $model->delete();
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
