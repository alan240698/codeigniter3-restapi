<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            text-align: center;
        }

        .icon-container {
            padding: 40px 40px 20px;
        }

        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }

        .icon.error {
            background: #fee2e2;
            color: #dc2626;
        }

        .icon.success {
            background: #d1fae5;
            color: #10b981;
        }

        .icon.warning {
            background: #fef3c7;
            color: #f59e0b;
        }

        .icon.info {
            background: #dbeafe;
            color: #3b82f6;
        }

        .content {
            padding: 0 40px 40px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #1f2937;
        }

        p {
            font-size: 16px;
            color: #6b7280;
            line-height: 1.6;
        }

        .footer {
            background: #f9fafb;
            padding: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .container {
                border-radius: 0;
            }

            .icon-container {
                padding: 30px 20px 15px;
            }

            .content {
                padding: 0 20px 30px;
            }

            h1 {
                font-size: 20px;
            }

            p {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-container">
            <div class="icon <?= $type ?>">
                <?php if ($type === 'error'): ?>
                    ✗
                <?php elseif ($type === 'success'): ?>
                    ✓
                <?php elseif ($type === 'warning'): ?>
                    ⚠
                <?php else: ?>
                    ℹ
                <?php endif; ?>
            </div>
        </div>

        <div class="content">
            <h1><?= htmlspecialchars($title) ?></h1>
            <p><?= htmlspecialchars($message) ?></p>
        </div>

        <div class="footer">
            <a href="<?= base_url() ?>">Return to Home</a>
        </div>
    </div>
</body>
</html>
