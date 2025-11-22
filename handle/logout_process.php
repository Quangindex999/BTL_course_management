<?php
session_start();
require_once '../functions/db_connection.php';
require_once '../functions/auth.php';

logoutUser();
setAlert('Đã đăng xuất thành công', 'success');
redirect(SITE_URL . '/index.php');
?>