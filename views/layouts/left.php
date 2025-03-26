<?php
use yii\bootstrap\Nav;
use yii\helpers\Html;

?>
<aside class="main-sidebar">
    <section class="sidebar">
        <div class="user-panel">
            <?php $user = !Yii::$app->user->isGuest ? Yii::$app->user->identity : null; ?>

            <div class="pull-left image">
                <span class="glyphicon glyphicon-user" style="font-size: 40px; color: white;"></span>
            </div>

            <div class="pull-left info">
                <p><?= $user ? Html::encode($user->nama) : 'Guest' ?></p>

                <?php if ($user): ?>
                    <?php if ($user->username === 'admin'): ?>
                        <a href="#"><i class="fa fa-user-shield"></i> Admin</a>
                    <?php else: ?>
                        <a href="#"><i class="fa fa-briefcase"></i> <?= Html::encode($user->biroPekerjaan->nama) ?></a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <?= Nav::widget([
            'encodeLabels' => false,
            'options' => ['class' => 'sidebar-menu'],
            'items' => array_filter([
                '<li class="header">Menu</li>',
                Yii::$app->user->identity && Yii::$app->user->identity->username === 'admin' ? 
                ['label' => '<span class="fa fa-file-code-o"></span> Gii', 'url' => ['/gii']] : null,
                
                Yii::$app->user->identity ? 
                    ['label' => '<i class="fa fa-folder-open"></i> Lihat Laporan Backup', 'url' => ['/site/index']] : null,

                Yii::$app->user->identity && Yii::$app->user->identity->username === 'admin' ? 
                    ['label' => '<i class="fa fa-users"></i> Lihat Daftar Pengguna', 'url' => ['/pengguna/daftarpengguna']] : null,

                Yii::$app->user->identity && Yii::$app->user->identity->username === 'admin' ? 
                    ['label' => '<i class="fa fa-building"></i> Lihat Daftar Biro Pekerjaan', 'url' => ['/biro/daftarbiro']] : null,

                Yii::$app->user->identity && Yii::$app->user->identity->username === 'admin' ? 
                    ['label' => '<i class="fa fa-tags"></i> Lihat Daftar Kategori', 'url' => ['/kategori/daftarkategori']] : null,


                Yii::$app->user->identity ?
                ['label' => '<i class="fa fa-plus-square"></i> Tambah Laporan', 'url' => ['/site/tambahlaporan']] : null,

                Yii::$app->user->identity ?
                '<li class="header"></li>' : null,

                Yii::$app->user->isGuest ? 
                ['label' => '<span class="glyphicon glyphicon-lock"></span> Masuk', 'url' => ['/site/login']] : 
                ['label' => '<span class="glyphicon glyphicon-off"></span> Keluar (' . Yii::$app->user->identity->nama . ')',
                'url' => ['/site/logout'],
                'linkOptions' => ['data-method' => 'post']],
            ]),
        ]); 
        ?>
    </section>

</aside>
