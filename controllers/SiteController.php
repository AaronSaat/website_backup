<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Laporan;
use app\models\BiroPekerjaan;
use dektrium\user\models\User;
use yii\data\ActiveDataProvider;
use yii\web\UploadedFile;
use DateTime;

class SiteController extends Controller
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

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        $query = Laporan::find()
        ->joinWith(['user', 'user.biroPekerjaan', 'kategori'])
        ->orderBy(['created_at' => SORT_DESC]);;

        $biroList = BiroPekerjaan::find()->asArray()->all();
        $userList = User::find()->asArray()->all();

        $selectedBiro = Yii::$app->request->get('biro');
        $selectedUser = Yii::$app->request->get('user');

        $currentUser = Yii::$app->user->identity;
        $isAdmin = ($currentUser->username === 'admin'); // Cek apakah user adalah admin

        if (!$isAdmin) {
            // Jika user bukan admin, hanya lihat data sesuai biro pekerjaannya
            $query->andWhere(['user.biro_pekerjaan_id' => $currentUser->biro_pekerjaan_id]);
        }

        if ($selectedBiro) {
            $query->andWhere(['user.biro_pekerjaan_id' => $selectedBiro]);
        }

        if ($selectedUser) {
            $query->andWhere(['user.id' => $selectedUser]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
        ]);

        // Ambil laporan terakhir yang sudah approved
        $lastApproved = Laporan::find()
            ->where(['user_id' => $currentUser->id, 'status' => 'Approved'])
            ->orderBy(['tanggal_backup' => SORT_DESC])
            ->one();

        $today = new DateTime();
        $lastBackupDate = null;
        $cardType = 'danger'; // Default: Mohon melakukan backup
        $daysSinceLastBackup = null; // Selisih hari sejak backup terakhir

        if ($lastApproved) {
            $lastBackupDate = new DateTime($lastApproved->tanggal_backup);
            $daysSinceLastBackup = $lastBackupDate->diff($today)->days;

            if ($daysSinceLastBackup <= 30) {
                $cardType = 'success'; // Jika backup masih dalam 30 hari, tampilkan "Terima kasih"
            }
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'biroList' => $biroList,
            'userList' => $userList,
            'selectedBiro' => $selectedBiro,
            'selectedUser' => $selectedUser,
            'cardType' => $cardType,
            'lastBackupDate' => $lastBackupDate ? $lastBackupDate->format('d-m-Y') : 'Belum ada backup',
            'daysSinceLastBackup' => $daysSinceLastBackup ?? 'N/A',
            'today' => $today->format('d-m-Y'),
        ]);
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        date_default_timezone_set('Asia/Jakarta');
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            Yii::$app->db->createCommand()->update('user', [
                'last_login_at' => date('Y-m-d H:i:s')
            ], ['id' => Yii::$app->user->id])->execute();
            return $this->goBack();
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionView($id)
    {
        $model = Laporan::find()
            ->joinWith(['user', 'user.biroPekerjaan', 'kategori'])
            ->where(['laporan.id' => $id])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException('Data laporan tidak ditemukan.');
        }

        return $this->render('detail', [
            'model' => $model,
        ]);
    }

    public function actionTambahlaporan()
    {
        $model = new Laporan();
    
        if ($model->load(Yii::$app->request->post())) {
            $model->user_id = Yii::$app->user->id; // Simpan ID user yang login
            $model->status = 'Waiting for Approval'; // Default status
            
            // Ambil file yang diunggah
            $uploadedFiles = UploadedFile::getInstances($model, 'files');
            $fileNames = [];
    
            if (!empty($uploadedFiles)) {
                $uploadPath = Yii::getAlias('@webroot/uploads');
                
                // Pastikan folder uploads ada
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
    
                // Simpan setiap file
                foreach ($uploadedFiles as $file) {
                    date_default_timezone_set('Asia/Jakarta');
                    $fileName = date('YmdHis') . '_' . $file->baseName . '.' . $file->extension;
                
                    if ($file->saveAs($uploadPath . '/' . $fileName)) {
                        $fileNames[] = $fileName;
                    }
                }                
    
                // Simpan nama file dalam database (dipisahkan koma jika lebih dari satu)
                $model->file = implode(',', $fileNames);
            }
    
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Laporan berhasil ditambahkan!');
                return $this->redirect(['site/index']); // Redirect ke halaman index
            } else {
                Yii::$app->session->setFlash('error', 'Gagal menambahkan laporan.');
            }
        }
    
        return $this->render('tambahlaporan', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = Laporan::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Laporan tidak ditemukan.');
        }

        $oldFiles = !empty($model->file) ? explode(',', $model->file) : []; // Ambil file lama
        $uploadPath = Yii::getAlias('@webroot/uploads');
        
        if ($model->load(Yii::$app->request->post())) {
            $uploadedFiles = UploadedFile::getInstances($model, 'files'); // Ambil file baru
            
            $newFiles = [];
            foreach ($uploadedFiles as $file) {
                $fileName = date('YmdHis', time()) . '_' . $file->baseName . '.' . $file->extension; // Format yyyymmddhhmmss
                if ($file->saveAs($uploadPath . '/' . $fileName)) {
                    $newFiles[] = $fileName;
                }
            }

            // Gabungkan file lama + baru, lalu batasi max 5 file
            $allFiles = array_slice(array_merge($oldFiles, $newFiles), 0, 5);
            $model->file = implode(',', $allFiles); 

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Laporan berhasil diperbarui.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = Laporan::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('Data laporan tidak ditemukan.');
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Laporan berhasil dihapus.');
        } else {
            Yii::$app->session->setFlash('error', 'Gagal menghapus laporan.');
        }

        return $this->redirect(['index']);
    }

    public function actionDeleteFile($id, $file)
    {
        $model = Laporan::findOne($id);

        if ($model) {
            $files = array_filter(explode(',', $model->file));
            $filePath = Yii::getAlias('@webroot/uploads/') . $file;

            if (in_array($file, $files) && file_exists($filePath)) {
                unlink($filePath); // Hapus file dari server
                $files = array_diff($files, [$file]); // Hapus dari daftar di database
                $model->file = implode(',', $files);
                $model->save(false);
            }
        }

        return json_encode(['success' => true]);
    }

    public function actionLogout()
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
        }
        return $this->redirect(['site/login']);
    }   
    
    public function actionApprove($id)
    {
        $model = Laporan::findOne($id);
        if ($model !== null) {
            $model->status = 'Approved';
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Laporan berhasil disetujui.');
            } else {
                Yii::$app->session->setFlash('error', 'Gagal menyetujui laporan.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Laporan tidak ditemukan.');
        }
        return $this->redirect(['index']);
    }

    public function actionDisapprove($id)
    {
        $model = Laporan::findOne($id);
        if ($model !== null) {
            $model->status = 'Disapproved';
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Laporan berhasil ditolak.');
            } else {
                Yii::$app->session->setFlash('error', 'Gagal menolak laporan.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Laporan tidak ditemukan.');
        }
        return $this->redirect(['index']);
    }
}
