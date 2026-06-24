<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * ChefLideresAsset
 *
 * Registra los estilos y scripts exclusivos de la página Chef Líderes.
 * Coloca los archivos en:
 *   web/css/chef-lideres.css
 *   web/js/chef-lideres.js
 */
class ChefLideresAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl  = '@web';

    public $css = [
        'css/chef-lideres.css',
    ];

    public $js = [
        'js/chef-lideres.js',
    ];

    // Se carga después de jQuery (AppAsset ya lo incluye)
    public $depends = [
        'app\assets\AppAsset',
    ];
}
