<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!empty($isNotFoundPage)): ?>
        <base href="/">
    <?php endif; ?>
    <title><?php echo isset($titel) ? htmlspecialchars($titel) . ' | ' : '' ?>Garage Sleutelwerk</title>
    <meta name="description" content="Garage Sleutelwerk: APK, onderhoud, airco-service en banden. Maak online een afspraak.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header class="topbalk">
        <div class="container topbalk-inhoud">
            <a href="index.php" class="logo">
                <span class="logo-icoon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                </span>
                <span class="logo-tekst">Sleutel<strong>werk</strong></span>
            </a>

            <input type="checkbox" id="menu-toggle" class="menu-toggle">
            <label for="menu-toggle" class="menu-knop" aria-label="Menu openen"><span></span></label>

            <?php require 'nav.php'; ?>
        </div>
    </header>
