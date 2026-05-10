<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/common.php';

cibo_app_start_named_session('ADMIN_SESSION');
define('CIBO_ADMIN_BASE', '/food-app/admin');
define('CIBO_ADMIN_FALLBACK_NAME', 'Cibo Admin');
define('CIBO_ADMIN_FALLBACK_EMAIL', 'admin@cibo.local');
define('CIBO_ADMIN_FALLBACK_PASSWORD', 'cibo123');
