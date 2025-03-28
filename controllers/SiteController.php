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
use app\models\Notes;
use app\models\Activity;
use dektrium\user\models\User;
use yii\data\ActiveDataProvider;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
use yii\data\Pagination;
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
            // ->where(['!=', 'user_id', 1]); // Jangan tampilkan user dengan id 1

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
            ->joinWith(['user', 'user.biroPekerjaan', 'note'])
            ->where(['laporan.user_id' => $user_id])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException('Data laporan tidak ditemukan.');
        }
            
        // $files = File::find()->where(['user_id' => $user_id])
        //         ->orderBy(['created_at' => SORT_DESC])
        //         ->all();
        // $logs = Log::find()->where(['user_id' => $user_id])        
        //         ->orderBy(['tanggal_waktu' => SORT_DESC])
        //         ->all();

        // Query untuk Files
        $queryFiles = File::find()->where(['user_id' => $user_id])->orderBy(['created_at' => SORT_DESC]);
        $countFiles = clone $queryFiles;
        $paginationFiles = new Pagination(['totalCount' => $countFiles->count(), 'pageSize' => 50]); 
        $files = $queryFiles->offset($paginationFiles->offset)->limit($paginationFiles->limit)->all();

        // Query untuk Logs
        $queryLogs = Log::find()->where(['user_id' => $user_id])->orderBy(['tanggal_waktu' => SORT_DESC]);
        $countLogs = clone $queryLogs;
        $paginationLogs = new Pagination(['totalCount' => $countLogs->count(), 'pageSize' => 50]);
        $logs = $queryLogs->offset($paginationLogs->offset)->limit($paginationLogs->limit)->all();
        $kategoriList = ArrayHelper::map(Kategori::find()->all(), 'id', 'nama_kategori');

        return $this->render('detail', [
            'model' => $model,
            'logs' => $logs,
            'files' => $files,
            'kategoriList' => $kategoriList,
            'paginationFiles' => $paginationFiles,
            'paginationLogs' => $paginationLogs,
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
            $model->updated_at = Yii::$app->formatter->asDatetime($model->tanggal_backup, 'php:Y-m-d H:i:s');
        } else {
            $model->status = 'Waiting for Approval';
            $model->updated_at = Yii::$app->formatter->asDatetime($model->tanggal_backup, 'php:Y-m-d H:i:s');
        }

        if ($model->load(Yii::$app->request->post())) {
            if (empty($model->tanggal_backup)) {
                Yii::$app->session->setFlash('error', 'Tanggal backup harus diisi.');
                return $this->render('tambahlaporan', ['model' => $model]);
            }

            $model->tanggal_backup = Yii::$app->formatter->asDate($model->tanggal_backup, 'php:Y-m-d');
            $model->updated_at = Yii::$app->formatter->asDatetime($model->tanggal_backup, 'php:Y-m-d H:i:s');

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

                Notes::deleteAll(['user_id' => Yii::$app->user->id]);

                // add activity log message
                // pertama dari sisi user
                // kedua dari sisi admin
                $activity = new Activity();
                $activity->user_id = Yii::$app->user->id;
                $activity->action_type = 'Create';
                $activity->notes = Yii::$app->user->identity->nama . " menambahkan laporan baru";
                $activity->save();

                $activity = new Activity();
                $adminUser = User::find()->where(['username' => 'admin'])->one(); //find id admin
                $activity->user_id = $adminUser ? $adminUser->id : null;
                $activity->action_type = 'Create';
                $activity->notes = "Laporan baru telah dibuat oleh " . Yii::$app->user->identity->nama; 
                $activity->save();

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

        // add activity log message
        // pertama dari sisi admin
        // kedua dari sisi user
        $activity = new Activity();
        $activity->user_id = Yii::$app->user->identity->id;
        $activity->action_type = 'Delete';
        $activity->notes = Yii::$app->user->identity->nama . " menghapus laporan milik " . 
        (User::findOne($user_id)->nama ?? 'Tidak diketahui');
        $activity->save();

        $activity = new Activity();
        $activity->user_id = $user_id;
        $activity->action_type = 'Delete';
        $activity->notes = "Laporan anda telah dihapus oleh " . Yii::$app->user->identity->nama; 
        $activity->save();

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
            $deletedFile = str_replace('uploads/', '', $file->direktori_file);

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            if ($file->delete()) {
                // add activity log message
                $activity = new Activity();
                $activity->user_id = Yii::$app->user->id;
                $activity->action_type = 'Delete';
                $activity->notes = Yii::$app->user->identity->nama . " menghapus file " . $deletedFile;
                $activity->save();

                Yii::$app->session->setFlash('success', 'File berhasil dihapus dari server.');
                return $this->redirect(['view', 'user_id' => $user_id]);
            }
        }

        Yii::$app->session->setFlash('error', 'File tidak ditemukan.');
        return $this->redirect(['index']);
    } 

    public function actionDeletelog($id)
    {
        $log = Log::findOne($id);

        if ($log) {
            $user_id = $log->user_id;

            $user = User::findOne($log->user_id);
            $userName = $user ? $user->nama : 'Unknown';

            $deletedLogDetails = "Log dihapus: \n" .
                "Nama: $userName, " .
                "Tanggal & Waktu: " . Yii::$app->formatter->asDatetime($log->tanggal_waktu, 'php:d-m-Y H:i:s') . ", " .
                "Tipe: $log->tipe, " .
                "Nama File: $log->nama, " .
                "Ukuran: " . number_format($log->ukuran, 2, ',', '') . " byte";

            if ($log->delete()) {
                // Catat aktivitas
                $activity = new Activity();
                $activity->user_id = Yii::$app->user->id;
                $activity->action_type = 'Delete';
                $activity->notes = Yii::$app->user->identity->nama . " menghapus " . $deletedLogDetails;
                $activity->save();

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
            $model->updated_at = date('Y-m-d H:i:s'); 

            if ($model->save(false)) {
                $approvedTime = date('Y-m-d H:i:s');
                File::updateAll(
                    ['approved_at' => $approvedTime],
                    ['user_id' => $user_id, 'approved_at' => null]
                );
                Log::updateAll(
                    ['approved_at' => $approvedTime],
                    ['user_id' => $user_id, 'approved_at' => null]
                ); 

                // add activity log message
                // pertama dari sisi admin
                // kedua dari sisi user
                $activity = new Activity();
                $activity->user_id = Yii::$app->user->identity->id;
                $activity->action_type = 'Approve';
                $activity->notes = Yii::$app->user->identity->nama . " melakukan approval pada laporan milik " . 
                (User::findOne($user_id)->nama ?? 'Tidak diketahui');
                $activity->save();

                $activity = new Activity();
                $activity->user_id = $user_id;
                $activity->action_type = 'Approve';
                $activity->notes = "Laporan anda telah diapprove oleh " . Yii::$app->user->identity->nama; 
                $activity->save();

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

        if (!$model) {
            Yii::$app->session->setFlash('error', 'Laporan tidak ditemukan.');
            return $this->redirect(['index']);
        }

        $noteModel = new Notes();
        $noteModel->user_id = $user_id;

        if ($noteModel->load(Yii::$app->request->post()) && $noteModel->validate()) {
            $noteModel->save(false);

            // Update status laporan 
            $model->status = 'Disapproved';
            $model->updated_at = date('Y-m-d H:i:s');
            $model->save(false);

            // add activity log message
            // pertama dari sisi admin
            // kedua dari sisi user
            $activity = new Activity();
            $activity->user_id = Yii::$app->user->identity->id;
            $activity->action_type = 'Disapprove';
            $activity->notes = Yii::$app->user->identity->nama . " melakukan disapproval pada laporan milik " . 
            (User::findOne($user_id)->nama ?? 'Tidak diketahui');
            $activity->save();

            $activity = new Activity();
            $activity->user_id = $user_id;
            $activity->action_type = 'Disapprove';
            $activity->notes = "Laporan anda telah didisapprove oleh " . Yii::$app->user->identity->nama; 
            $activity->save();

            Yii::$app->session->setFlash('success', 'Notes disapproved telah ditambakan.');
            return $this->redirect(['index']);
        }
        
        return $this->render('tambahnote', ['model' => $noteModel]);
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

    private function processTxtFile($filePath)
    {
        if (!file_exists($filePath)) {
            return;
        }

        $fileHandle = fopen($filePath, 'rb'); // Gunakan 'rb' untuk membaca dengan benar
        if ($fileHandle === false) {
            return;
        }

        $count = 0; // Inisialisasi penghitung

        while (($line = fgets($fileHandle)) !== false) {
            // Lewati jika baris kosong
            if (empty($line)) {
                continue;
            }

            // Pisahkan berdasarkan koma
            $row = array_map('trim', explode(',', $line)); // Hapus spasi ekstra di setiap elemen
            
            // Pastikan jumlah kolom sesuai dengan format (minimal 4 kolom)
            if (count($row) < 4) {
                continue;
            }

            // Parsing tanggal dan waktu (perhatikan formatnya)
            $tanggalWaktuObj = DateTime::createFromFormat('d/m/Y H:i:s', $row[0]);
            $tanggalWaktu = $tanggalWaktuObj ? $tanggalWaktuObj->format('Y-m-d H:i:s') : null;

            $tipe = $row[1];
            $nama = $row[2];

            // Ambil angka dari ukuran (hilangkan 'byte' di belakang)
            $ukuran = (double) filter_var($row[3], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

            // Tambahkan penghitung baris
            $count++;

            // Simpan ke model Log
            $logModel = new Log();
            $logModel->user_id = Yii::$app->user->id;
            $logModel->tanggal_waktu = $tanggalWaktu;
            $logModel->tipe = $tipe;
            $logModel->nama = $nama;
            $logModel->ukuran = $ukuran;

            // Simpan data ke database
            if (!$logModel->save()) {
                var_dump("Gagal menyimpan:", $logModel->getErrors()); // Debug jika save() gagal
            }
        }

        fclose($fileHandle);
    }
}
