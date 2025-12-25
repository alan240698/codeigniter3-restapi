<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($instance->template_name) ?></title>
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
            max-width: 800px;
            width: 100%;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .content {
            padding: 40px;
        }

        .email-subject {
            background: #f9fafb;
            border-left: 4px solid #667eea;
            padding: 15px 20px;
            margin-bottom: 30px;
            border-radius: 4px;
        }

        .email-subject strong {
            color: #374151;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .email-subject p {
            margin-top: 8px;
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
        }

        .email-body {
            line-height: 1.8;
            color: #374151;
        }

        .action-status {
            margin-top: 30px;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .action-status.success {
            background: #d1fae5;
            border: 2px solid #10b981;
            color: #065f46;
        }

        .action-status.warning {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            color: #92400e;
        }

        .action-status h3 {
            font-size: 18px;
            margin-bottom: 8px;
        }

        .action-status p {
            font-size: 14px;
        }

        .action-info {
            margin-top: 10px;
            font-size: 12px;
            opacity: 0.8;
        }

        .footer {
            background: #f9fafb;
            padding: 20px 40px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            border-top: 1px solid #e5e7eb;
        }

        .footer p {
            margin: 5px 0;
        }

        .action-form {
            margin-top: 30px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
        }

        .action-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            min-height: 80px;
        }

        .action-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            justify-content: center;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-approve {
            background: #10b981;
            color: white;
        }

        .btn-approve:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }

        .btn-reject {
            background: #ef4444;
            color: white;
        }

        .btn-reject:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        @media (max-width: 600px) {
            .container {
                border-radius: 0;
            }

            .content {
                padding: 20px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= htmlspecialchars($instance->template_name) ?></h1>
            <p>Template Code: <?= htmlspecialchars($instance->template_code) ?></p>
        </div>

        <div class="content">
            <div class="email-subject">
                <strong>Subject</strong>
                <p><?= htmlspecialchars($instance->rendered_subject) ?></p>
            </div>

            <div class="email-body">
                <?= $instance->rendered_body ?>
            </div>

            <?php if ($latest_action): ?>
                <div class="action-status success">
                    <h3>✓ Action Already Performed</h3>
                    <p>This template has already been processed.</p>
                    <div class="action-info">
                        Action: <strong><?= htmlspecialchars(ucfirst($latest_action->action_type)) ?></strong><br>
                        Performed at: <?= date('F j, Y g:i A', strtotime($latest_action->performed_at)) ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="action-form">
                    <form method="POST" action="<?= base_url('template/action/' . $token) ?>">
                        <label for="comment">Additional Comments (Optional)</label>
                        <textarea name="comment" id="comment" placeholder="Enter any additional comments..."></textarea>
                        
                        <div class="action-buttons">
                            <button type="submit" name="action" value="approve" class="btn btn-approve">
                                ✓ Approve
                            </button>
                            <button type="submit" name="action" value="reject" class="btn btn-reject">
                                ✗ Reject
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer">
            <p>This link will expire on <?= date('F j, Y', strtotime($instance->expires_at)) ?></p>
            <?php if ($instance->recipient_email): ?>
                <p>Sent to: <?= htmlspecialchars($instance->recipient_email) ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
