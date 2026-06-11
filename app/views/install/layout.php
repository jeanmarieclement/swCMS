<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>swCMS Installation Wizard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .installer {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
        }
        
        .header {
            background: #4f46e5;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .progress {
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            margin-top: 20px;
        }
        
        .progress-bar {
            height: 100%;
            background: #10b981;
            transition: width 0.3s ease;
        }
        
        .content {
            padding: 40px;
        }
        
        .step-title {
            font-size: 24px;
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .step-description {
            color: #6b7280;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.2s ease;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #4f46e5;
        }
        
        .btn {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        
        .btn:hover {
            background: #4338ca;
        }
        
        .btn-secondary {
            background: #6b7280;
            margin-right: 10px;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .btn-test {
            background: #059669;
            margin-left: 10px;
        }
        
        .btn-test:hover {
            background: #047857;
        }
        
        .actions {
            margin-top: 30px;
            text-align: right;
        }
        
        .error {
            background: #fef2f2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc2626;
        }
        
        .success {
            background: #f0fdf4;
            color: #16a34a;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #16a34a;
        }
        
        .check-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .check-item:last-child {
            border-bottom: none;
        }
        
        .check-status {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
        }
        
        .check-status.pass {
            background: #10b981;
        }
        
        .check-status.fail {
            background: #dc2626;
        }
        
        .check-details {
            flex: 1;
        }
        
        .check-name {
            font-weight: 600;
            color: #1f2937;
        }
        
        .check-description {
            font-size: 14px;
            color: #6b7280;
            margin-top: 2px;
        }
        
        .check-value {
            font-size: 14px;
            color: #4b5563;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="installer">
        <div class="header">
            <h1>swCMS Installation</h1>
            <p>Welcome to the installation wizard for swCMS</p>
            <div class="progress">
                <div class="progress-bar" style="width: <?php echo ($step / 6) * 100; ?>%"></div>
            </div>
        </div>
        
        <div class="content">
            <?php if (!empty($errors)) : ?>
                <div class="error">
                    <?php foreach ($errors as $error) : ?>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php
            // Include the specific step view
            $stepFile = APP_PATH . "/views/install/step_{$view}.php";
            if (file_exists($stepFile)) {
                include $stepFile;
            } else {
                echo "<p>Step view not found: {$view}</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>