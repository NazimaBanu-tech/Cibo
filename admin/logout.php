<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

cibo_admin_logout();
cibo_redirect(CIBO_ADMIN_BASE . '/login.php');
