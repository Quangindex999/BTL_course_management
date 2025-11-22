<?php
session_start();
require_once '../functions/db_connection.php';
require_once '../functions/auth.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = registerUser($_POST);
    
    if($result['success']) {
        setAlert($result['message'], 'success');
        redirect(SITE_URL . '/views/login.php');
    } else {
        setAlert(implode('<br>', $result['errors']), 'error');
        redirect(SITE_URL . '/views/register.php');
    }
} else {
    redirect(SITE_URL . '/views/register.php');
}
?>