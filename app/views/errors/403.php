<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accesso Negato - <?= isset(
$site_name) ? $site_name : 'CMS' ?></title>
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
            color: #fd7e14;
            margin-bottom: 0;
            font-weight: 700;
            line-height: 1;
        }
        .error-message {
            font-size: 28px;
            color: #343a40;
            margin-top: 20px;
        }
        .error-description {
            font-size: 18px;
            color: #6c757d;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">403</div>
        <div class="error-message">Accesso Negato</div>
        <div class="error-description">Non hai i permessi necessari per accedere a questa risorsa.</div>
        <a href="/" class="btn btn-warning mt-4">Torna alla Home</a>
    </div>
</body>
</html>
