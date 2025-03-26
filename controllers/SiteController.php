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
use app\models\Log;
use app\models\File;
use app\models\Kategori;
use app\models\BiroPekerjaan;
use dektrium\user\models\User;
use yii\data\ActiveDataProvider;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
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
            ->joinWith(['user', 'user.biroPekerjaan'])
            ->orderBy(['created_at' => SORT_DESC]);

        $biroList = BiroPekerjaan::find()->asArray()->all();
        $userList = User::find()->asArray()->all();

        $selectedBiro = Yii::$app->request->get('biro');
        $searchNama = trim(Yii::$app->request->get('search_nama'));

        $currentUser = Yii::$app->user->identity;
        $isAdmin = ($currentUser->username === 'admin');

        if (!$isAdmin) {
            $query->andWhere(['user.biro_pekerjaan_id' => $currentUser->biro_pekerjaan_id]);
        }

        if ($selectedBiro) {
            $query->andWhere(['user.biro_pekerjaan_id' => $selectedBiro]);
        }

        if (!empty($searchNama)) {
            $query->andWhere(['LIKE', 'user.nama', $searchNama]); 
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 30],
        ]);

        // Ambil laporan satu-satunya user yang sedang login
        $userReport = Laporan::findOne(['user_id' => $currentUser->id]);

        // Format tanggal dalam bahasa Indonesia
        setlocale(LC_TIME, 'id_ID.UTF-8');
        $today = new DateTime();
        $formattedToday = strftime('%A, %d %B %Y', $today->getTimestamp());

        // Default values
        $lastBackupDate = 'Belum ada backup';
        $cardType = 'danger'; // Mohon melakukan backup
        $message = 'Mohon melakukan backup bulan ini';
        $daysSinceLastBackup = 'N/A';

        if ($userReport) {
            $lastUpdatedDate = new DateTime($userReport->tanggal_backup);
            $lastBackupDate = strftime('%A, %d %B %Y', $lastUpdatedDate->getTimestamp());

            if ($userReport->status === 'Approved') {
                $daysSinceLastBackup = $lastUpdatedDate->diff($today)->days;
                if ($daysSinceLastBackup <= 30) {
                    $cardType = 'success';
                    $message = 'Terima kasih telah melakukan backup bulan ini';
                }
            } elseif ($userReport->status === 'Waiting for Approval') {
                $cardType = 'warning';
                $message = 'Menunggu approval dari admin';
                $lastBackupDate = null; // Tidak perlu menampilkan last backup
                $daysSinceLastBackup = null;
            }
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'biroList' => $biroList,
            'userList' => $userList,
            'selectedBiro' => $selectedBiro,
            'searchNama' => $searchNama,
            'cardType' => $cardType,
            'message' => $message,
            'lastBackupDate' => $lastBackupDate,
            'daysSinceLastBackup' => $daysSinceLastBackup,
            'formattedToday' => $formattedToday,
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

    public function actionView($user_id)
    {
        $model = Laporan::find()
            ->joinWith(['user', 'user.biroPekerjaan'])
            ->where(['laporan.user_id' => $user_id])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException('Data laporan tidak ditemukan.');
        }

        $files = File::find()->where(['user_id' => $user_id])->all();
        $logs = Log::find()->where(['user_id' => $user_id])->all();
        $kategoriList = ArrayHelper::map(Kategori::find()->all(), 'id', 'nama_kategori');

        return $this->render('detail', [
            'model' => $model,
            'files' => $files,
            'logs' => $logs,
            'kategoriList' => $kategoriList,
        ]);
    }

    public function actionTambahlaporan()
    {
        date_default_timezone_set('Asia/Jakarta'); 

        $model = Laporan::findOne(['user_id' => Yii::$app->user->id]);
        $isNewRecord = !$model; //untuk flashnya

        if (!$model) {
            $model = new Laporan();
            $model->user_id = Yii::$app->user->id;
            $model->status = 'Waiting for Approval'; 
        } else {
            $model->status = 'Waiting for Approval';
        }

        if ($model->load(Yii::$app->request->post())) {
            if (empty($model->tanggal_backup)) {
                Yii::$app->session->setFlash('error', 'Tanggal backup harus diisi.');
                return $this->render('tambahlaporan', ['model' => $model]);
            }

            $model->tanggal_backup = Yii::$app->formatter->asDate($model->tanggal_backup, 'php:Y-m-d');
            $model->updated_at = Yii::$app->formatter->asDate($model->tanggal_backup, 'php:Y-m-d');

            if ($model->save()) {
                $uploadedFiles = UploadedFile::getInstances($model, 'files');

                if (empty($uploadedFiles)) {
                    Yii::$app->session->setFlash('error', 'Tidak ada file yang diunggah.');
                    return $this->render('tambahlaporan', ['model' => $model]);
                }

                $uploadPath = Yii::getAlias('@webroot/uploads');

                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                foreach ($uploadedFiles as $file) {
                    $fileName = date('YmdHis') . '_' . $file->baseName . '.' . $file->extension;
                    $filePath = $uploadPath . '/' . $fileName;

                    if ($file->saveAs($filePath)) {
                        // Simpan informasi file ke tabel `file`
                        $fileModel = new File();
                        $fileModel->user_id = Yii::$app->user->id;
                        $fileModel->direktori_file = 'uploads/' . $fileName;
                        $fileModel->tipe = $file->extension;
                        $fileModel->created_at = $model->tanggal_backup;

                        if ($fileModel->save()) {
                            if ($file->extension === 'csv') {
                                $this->processCsvFile($filePath);
                            }
                        } else {
                            Yii::$app->session->setFlash('error', 'Gagal menyimpan file ke database.');
                            return $this->redirect(['site/index']);
                        }
                    }
                }

                if ($isNewRecord) {
                    Yii::$app->session->setFlash('success', 'Laporan baru dan datanya berhasil disimpan!');
                    return $this->redirect(['index']);
                } else {
                    Yii::$app->session->setFlash('success', 'Laporan berhasil diperbarui!');
                    return $this->redirect(['view', 'user_id' => $model->user_id]); 
                }
                return $this->redirect(['site/index']);
            } else {
                Yii::$app->session->setFlash('error', 'Gagal menyimpan laporan.');
            } 
        }

        return $this->render('tambahlaporan', [
            'model' => $model,
        ]);
    }

    // public function actionUpdate($id)
    // {
    //     $model = Laporan::findOne($id);
    //     if (!$model) {
    //         throw new NotFoundHttpException('Laporan tidak ditemukan.');
    //     }

    //     $oldFiles = !empty($model->file) ? explode(',', $model->file) : []; // Ambil file lama
    //     $uploadPath = Yii::getAlias('@webroot/uploads');
        
    //     if ($model->load(Yii::$app->request->post())) {
    //         $uploadedFiles = UploadedFile::getInstances($model, 'files'); // Ambil file baru
            
    //         $newFiles = [];
    //         foreach ($uploadedFiles as $file) {
    //             $fileName = date('YmdHis', time()) . '_' . $file->baseName . '.' . $file->extension; // Format yyyymmddhhmmss
    //             if ($file->saveAs($uploadPath . '/' . $fileName)) {
    //                 $newFiles[] = $fileName;
    //             }
    //         }

    //         // Gabungkan file lama + baru, lalu batasi max 5 file
    //         $allFiles = array_slice(array_merge($oldFiles, $newFiles), 0, 5);
    //         $model->file = implode(',', $allFiles); 

    //         if ($model->save()) {
    //             Yii::$app->session->setFlash('success', 'Laporan berhasil diperbarui.');
    //             return $this->redirect(['view', 'id' => $model->id]);
    //         }
    //     }

    //     return $this->render('update', [
    //         'model' => $model,
    //     ]);
    // }

    public function actionDelete($user_id)
    {
        $model = Laporan::findOne(['user_id' => $user_id]);

        if ($model === null) {
            throw new NotFoundHttpException('Data laporan tidak ditemukan.');
        }

        // Hapus semua log yang terkait
        Log::deleteAll(['user_id' => $user_id]);

        // Hapus semua file yang terkait
        $files = File::findAll(['user_id' => $user_id]);

        foreach ($files as $file) {
            $filePath = Yii::getAlias('@webroot/') . $file->direktori_file;

            // Hapus file dari server jika ada
            if (file_exists($filePath)) {
                if (@unlink($filePath)) {
                    Yii::$app->session->setFlash('success', 'File ' . $file->direktori_file . ' berhasil dihapus.');
                } else {
                    Yii::$app->session->setFlash('error', 'Gagal menghapus file ' . $file->direktori_file);
                }
            }

            // Hapus data file dari database
            $file->delete();
        }

        // Hapus laporan setelah log dan file dihapus
        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Laporan dan semua data terkait berhasil dihapus.');
        } else {
            Yii::$app->session->setFlash('error', 'Gagal menghapus laporan.');
        }

        return $this->redirect(['index']);
    }

    public function actionDeletefile($id)
    {
        $file = File::findOne($id);

        if ($file) {
            $user_id = $file->user_id; 
            $filePath = Yii::getAlias('@webroot/') . $file->direktori_file;

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            if ($file->delete()) {
                Yii::$app->session->setFlash('success', 'File berhasil dihapus dari server.');
                return $this->redirect(['view', 'user_id' => $user_id]);
            }
        }

        Yii::$app->session->setFlash('error', 'File tidak ditemukan');
        return $this->redirect(['view', 'user_id' => Yii::$app->user->id]);
    }   

    public function actionDeletelog($id)
    {
        $log = Log::findOne($id);

        if ($log) {
            $user_id = $log->user_id; // Ambil user_id sebelum dihapus

            if ($log->delete()) {
                Yii::$app->session->setFlash('success', 'Log berhasil dihapus dari database.');
                return $this->redirect(['view', 'user_id' => $user_id]);
            }
        }

        Yii::$app->session->setFlash('error', 'Log tidak ditemukan');
        return $this->redirect(['view', 'user_id' => Yii::$app->user->id]);
    }

    public function actionLogout()
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
        }
        return $this->redirect(['site/login']);
    }   
    
    public function actionApprove($user_id)
    {
        $model = Laporan::findOne(['user_id' => $user_id]);

        if ($model !== null) {
            $model->status = 'Approved';
            $model->updated_at = date('Y-m-d'); 

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

    public function actionDisapprove($user_id)
    {
        $model = Laporan::findOne(['user_id' => $user_id]);

        if ($model !== null) {
            $model->status = 'Disapproved';
            $model->updated_at = date('Y-m-d');

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

    private function processCsvFile($filePath)
    {
        if (!file_exists($filePath)) {
            return;
        }

        $fileHandle = fopen($filePath, 'r');
        if ($fileHandle === false) {
            return;
        }

        // Lewati header CSV
        fgetcsv($fileHandle);

        while (($row = fgetcsv($fileHandle)) !== false) {
            // Pastikan jumlah kolom sesuai dengan format CSV
            if (count($row) < 5) {
                continue;
            }

            // Mapping data dari CSV
            $tanggalObj = DateTime::createFromFormat('d/m/Y', $row[0]);
            $tanggal = $tanggalObj ? $tanggalObj->format('Y-m-d') : null;

            $waktuObj = DateTime::createFromFormat('H:i:s', $row[1]);
            $waktu = $waktuObj ? $waktuObj->format('H:i:s') : null;
            $tipe = $row[2];
            $nama = $row[3];
            $ukuran = (float) str_replace(',', '', $row[4]); // Konversi ukuran ke float

            // Simpan ke model Log
            $logModel = new Log();
            $logModel->user_id = Yii::$app->user->id;
            $logModel->tanggal_waktu = $tanggal && $waktu ? "$tanggal $waktu" : null;
            $logModel->tipe = $tipe;
            $logModel->nama = $nama;
            $logModel->ukuran = $ukuran;

            // Simpan data ke database
            $logModel->save();
        }

        fclose($fileHandle);
    }
}
