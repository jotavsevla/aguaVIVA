<?php
if (session_status() == PHP_SESSION_NONE) session_start();

$session_timeout = 3600; // 1 hora

function checkAuthentication() {
    global $session_timeout;

    if (!isset($_SESSION['userlogged'])) {
        return false;
    }

    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
        session_unset();
        session_destroy();
        return false;
    }

    $_SESSION['last_activity'] = time();
    return true;
}

function getBaseUrl() {
    return '/aguaVIVA/';
}

if (!checkAuthentication()) {
    header('Location: ' . getBaseUrl() . 'login.php');
    exit();
}

function renderPage($pageTitle, $content) {
    $baseUrl = getBaseUrl();
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($pageTitle); ?> - <?php echo TITLE; ?></title>
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>assets/css/styles.css">
    </head>
    <body>
    <div class="container">
        <?php include_once '../partials/header.php'; ?>
        <div class="content">
            <div class="content-header">
                <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
            </div>
            <div class="content-area">
                <?php echo $content; ?>
            </div>
        </div>
        <?php include_once '../partials/footer.php'; ?>
    </div>
    </body>
    </html>
    <?php
}