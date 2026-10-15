<?php

use Plugins\asamiyaeditor\Controllers\AssetController;

return function($r) {
    $r->addRoute('GET', '/asamiya-editor/asset/{file:js|css|theme}', [AssetController::class, 'serve']);
};
