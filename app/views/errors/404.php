<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagina Non Trovata - <?= isset($site_name) ? $site_name : 'CMS' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 40px;
            background-color: #f8f9fa;
        }
        .error-container {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .error-code {
            font-size: 120px;
            color: #0d6efd;
            margin-bottom: 0;
            font-weight: 700;
            line-height: 1;
        }
        .error-title {
            margin-top: 0;
            color: #495057;
        }
        .home-link {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <h1 class="error-code">404</h1>
            <h2 class="error-title">Pagina Non Trovata</h2>
            <p>La pagina che stai cercando non esiste o è stata spostata.</p>
            <p>Verifica che l'URL inserito sia corretto o utilizza il menu di navigazione.</p>
            <div class="home-link">
                <a href="<?= isset($site_url) ? $site_url : '/' ?>" class="btn btn-primary">Torna alla Home</a>
            </div>
        </div>
    </div>
</body>
</html>
