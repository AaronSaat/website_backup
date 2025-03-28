<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use app\models\User;
use app\models\Activity;
use yii\web\NotFoundHttpException;

class PenggunaController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'tambahlaporan'], // Halaman yang ingin dibatasi aksesnya
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Hanya untuk pengguna yang sudah login
                    ],
                    [
                        'allow' => false,
                        'roles' => ['?'], // Jika guest, maka redirect ke login
                        'denyCallback' => function ($rule, $action) {
                            return Yii::$app->response->redirect(['site/login']);
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionDaftarpengguna()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find()
                ->where(['!=', 'id', 1]), // Jangan tampilkan user dengan id 1
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
                // add activity log message
                // pertama dari sisi admin
                // kedua dari sisi user
                $activity = new Activity();
                $activity->user_id = Yii::$app->user->identity->id;
                $activity->action_type = 'Create';
                $activity->notes = Yii::$app->user->identity->nama . " menambahkan user: " . $model->nama;
                $activity->save();

                $activity = new Activity();
                $activity->user_id = $model->id;
                $activity->action_type = 'Create';
                $activity->notes = "Akun anda telah dibuat oleh " . Yii::$app->user->identity->nama; 
                $activity->save();

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
                // add activity log message
                // pertama dari sisi admin
                // kedua dari sisi user
                $activity = new Activity();
                $activity->user_id = Yii::$app->user->identity->id;
                $activity->action_type = 'Edit';
                $activity->notes = Yii::$app->user->identity->nama . " mengedit user: " . $model->nama;
                $activity->save();

                $activity = new Activity();
                $activity->user_id = $model->id;
                $activity->action_type = 'Edit';
                $activity->notes = "Akun anda telah diedit oleh " . Yii::$app->user->identity->nama; 
                $activity->save();

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
        $model = $this->findModel($id);
        
        $namaUser = $model->nama; // Simpan nama sebelum dihapus
        if ($model->delete()) {
            // add activity log message
            $activity = new Activity();
            $activity->user_id = Yii::$app->user->identity->id;
            $activity->action_type = 'Delete';
            $activity->notes = Yii::$app->user->identity->nama . " menghapus user: " . $namaUser;
            $activity->save();

            Yii::$app->session->setFlash('success', "User $namaUser berhasil dihapus.");
        } else {
            Yii::$app->session->setFlash('error', "Gagal menghapus user $namaUser.");
        }

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
