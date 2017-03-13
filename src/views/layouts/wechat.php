<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use raysoft\weui\WeuiAsset;

WeuiAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div style="height:90vh;overflow:hidden;">
<?= $content ?>
</div>
<div class="weui-footer" style="height:10vh;line-height:10vh;">
    <p class="weui-footer__text">Copyright © 2008-2016 weui.io</p>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
