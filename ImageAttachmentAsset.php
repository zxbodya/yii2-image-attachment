<?php

namespace zxbodya\yii2\imageAttachment;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\AssetBundle;
use yii\web\View;
use yii\widgets\InputWidget;

/**
 * This is just an example.
 */
class ImageAttachmentAsset extends AssetBundle
{
    public $sourcePath = '@zxbodya/yii2/imageAttachment/assets';
    public $js = [
        'jquery.iframe-transport.js',
        'jquery.imageAttachment.js',
        // 'jquery.iframe-transport.min.js',
        // 'jquery.imageAttachment.min.js',
    ];
    public $css = [
        'imageAttachment.css'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

}