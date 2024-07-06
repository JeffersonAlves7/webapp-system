<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= isset($pageDescription) ? $pageDescription : 'Default description'; ?>">
    <meta name="keywords" content="<?= isset($pageKeywords) ? $pageKeywords : 'default, keywords'; ?>">
    <meta name="author" content="JeffersonAlves7">
    <title><?= isset($pageTitle) ? "WebPage - " . $pageTitle : "WebPage"; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/public/global.css">
    <?= isset($extraHeadContent) ? $extraHeadContent : ''; ?>
</head>

<body>
    <?= isset($content) ? $content : ""; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="/public/global.js"></script>
</body>

</html>