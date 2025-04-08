<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use app\models\User;
use app\models\Activity;
use app\models\BiroPekerjaan;
use yii\web\NotFoundHttpException;

class PenggunaController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['gantipassword'], // Halaman yang ingin dibatasi aksesnya
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], 
                    ],
                ],
            ],
        ];
    }

    public function actionDaftarpengguna()
    {
        if (!Yii::$app->user->can('admin') && !Yii::$app->user->can('superadmin')) {
            throw new \yii\web\ForbiddenHttpException('Anda tidak punya izin untuk mengakses pengguna.');
        }

        $query = User::find();

        // Jika admin biasa, jangan tampilkan superadmin (id = 2)
        if (Yii::$app->user->can('admin') && !Yii::$app->user->can('superadmin')) {
            $query->where(['not in', 'id', [1, 2]]);
        }

        $biroList = BiroPekerjaan::find()->asArray()->all();
        $selectedBiro = Yii::$app->request->get('biro');

        if ($selectedBiro) {
            $query->andWhere(['user.biro_pekerjaan_id' => $selectedBiro]);
        }
        
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
        ]);

        return $this->render('daftarpengguna', [
            'dataProvider' => $dataProvider,
            'biroList' => $biroList,
            'selectedBiro' => $selectedBiro,
        ]);
    }
        
    public function actionTambahpengguna()
    {
        if (!Yii::$app->user->can('admin')) {
            throw new \yii\web\ForbiddenHttpException('Anda bukan admin dan tidak punya izin untuk mengakses pengguna.');
        }
        $model = new User();

        if ($model->load(Yii::$app->request->post())) {
            $model->created_at = date('Y-m-d H:i:s');

            if (!empty($model->password)) {
                $model->password_hash = Yii::$app->security->generatePasswordHash($model->password);
            }

            $model->status = 10;

            if ($model->save()) {
                // Assign role 'user'
                $auth = Yii::$app->authManager;
                $userRole = $auth->getRole('user');
                $auth->assign($userRole, $model->id);

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
        } else {
            $model->password = strtolower(substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 6));
        }

        return $this->render('tambahpengguna', ['model' => $model]);
    }

    public function actionCreate()
    {
        // ga dipake
    }

    public function actionUpdate($id)
    {
        if (!Yii::$app->user->can('admin')) {
            throw new \yii\web\ForbiddenHttpException('Anda bukan admin dan tidak punya izin untuk mengakses pengguna.');
        }
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
        if (!Yii::$app->user->can('admin')) {
            throw new \yii\web\ForbiddenHttpException('Anda bukan admin dan tidak punya izin untuk mengakses pengguna.');
        }
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
        return $this->redirect(['pengguna/daftarpengguna']);
    }

    public function actionTogglestatus($id)
    {
        if (!Yii::$app->user->can('admin')) {
            throw new \yii\web\ForbiddenHttpException('Anda tidak punya izin.');
        }

        $model = $this->findModel($id);
        $oldStatus = $model->status;

        $model->status = $model->status == 10 ? 1 : 10;

        if ($model->save(false)) {
            // log aktivitas
            $activity = new Activity();
            $activity->user_id = Yii::$app->user->identity->id;
            $activity->action_type = 'Update';
            $activity->notes = Yii::$app->user->identity->nama . " mengubah status user {$model->nama} dari " . ($oldStatus == 10 ? 'Aktif' : 'Nonaktif') . " menjadi " . ($model->status == 10 ? 'Aktif' : 'Nonaktif');
            $activity->save();

            Yii::$app->session->setFlash('success', "Status user {$model->nama} berhasil diubah.");
        } else {
            Yii::$app->session->setFlash('error', "Gagal mengubah status user {$model->nama}.");
        }

        return $this->redirect(['pengguna/daftarpengguna']);
    }
    
    public function actionGantipassword()
    {
        if (!(Yii::$app->user->can('admin') || Yii::$app->user->can('gantipassword'))) {
            throw new \yii\web\ForbiddenHttpException('Anda tidak punya izin.');
        }

        $user = User::findOne(Yii::$app->user->id);

        if ($user->load(Yii::$app->request->post()) && $user->validate()) {
            $user->password_hash = Yii::$app->security->generatePasswordHash($user->password);
            if ($user->save(false)) {
                // log aktivitas
                $activity = new Activity();
                $activity->user_id = Yii::$app->user->identity->id;
                $activity->action_type = 'Update';
                $activity->notes = Yii::$app->user->identity->nama . " mengganti password akun";
                $activity->save();

                Yii::$app->session->setFlash('success', 'Password berhasil diganti.');
                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Gagal mengganti password.');
            }
        }

        return $this->render('gantipassword', ['model' => $user]);
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('User tidak ditemukan.');
    }
}
