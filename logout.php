<?php
require_once __DIR__ . '/config/config.php';

// Destruir sesión
session_destroy();

// Redirigir
redirect('/index.php');
