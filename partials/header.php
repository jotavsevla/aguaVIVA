<?php
session_start();
if (!isset($_SESSION['userlogged'])) {
    header('Location: ' . getBaseUrl() . 'login.php');
    exit;
}

// Ajusta o caminho dos assets baseado na profundidade do diretório
$isSubDirectory = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
$basePathPrefix = $isSubDirectory ? '../' : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Sistema Água Mineral VIVA'; ?></title>
    <link rel="stylesheet" href="<?php echo $basePathPrefix; ?>assets/css/styles.css">
</head>
<body>
<div class="container">
    <?php include __DIR__ . '/menu.php'; ?>
    <div class="content">
        <div class="content-header">
            <h1><?php echo $pageTitle ?? 'Sistema Água Mineral VIVA'; ?></h1>
        </div>
        <div class="content-area">
