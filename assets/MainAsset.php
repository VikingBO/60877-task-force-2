<?php

namespace app\assets;
use yii\web\AssetBundle;
use yii\web\View;

class MainAsset extends AssetBundle
{
    public $basePath = '@webroot';

    public $css = [
        'css/style.css',
        'css/autocomplete.min.css'
    ];

    public $js = [
        'js/main.js',
        'js/custom-autocompplete.js',
    ];
    public $jsOptions = [
      //'position'=>View::POS_HEAD
    ];
}