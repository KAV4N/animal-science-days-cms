<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Deleted - Animal Science Days</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #dc3545;
            margin: 0;
            font-size: 28px;
        }
        .alert {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 25px;
            border-left: 4px solid #dc3545;
        }
        .alert h2 {
            margin-top: 0;
            color: #721c24;
            font-size: 24px;
        }
        .alert .icon {
            font-size: 48px;
            text-align: center;
            margin-bottom: 15px;
        }
        .details-section {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #6c757d;
        }
        .details-section h3 {
            margin-top: 0;
            color: #495057;
        }
        .detail-item {
            margin: 10px 0;
            padding: 10px;
            background-color: white;
            border-radius: 3px;
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
        }
        .detail-value {
            background-color: #f8f9fa;
            padding: 5px 8px;
            border-radius: 3px;
            border: 1px solid #dee2e6;
            display: inline-block;
            margin-left: 10px;
            font-family: monospace;
        }
        .contact-section {
            background-color: #cce5ff;
            border: 1px solid #b3d9ff;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .contact-section h3 {
            margin-top: 0;
            color: #004085;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .section-icon {
            font-size: 18px;
            margin-right: 8px;
        }
        .timestamp {
            font-size: 12px;
            color: #6c757d;
            font-style: italic;
        }
        ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Animal Science Days</h1>
            <p>Conference Management System</p>
        </div>

        <div class="alert">
            <div class="icon">⚠️</div>
            <h2>Account Deletion Notice</h2>
            <p><strong>Dear {{ $user->name }},</strong></p>
            <p>We are writing to inform you that your account for the Animal Science Days Conference Management System has been permanently deleted.</p>
        </div>

        <!-- ACCOUNT DETAILS SECTION -->
        <div class="details-section">
            <h3><span class="section-icon">👤</span>Deleted Account Details</h3>
            <div class="detail-item">
                <span class="detail-label">Full Name:</span>
                <span class="detail-value">{{ $user->name }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Email Address:</span>
                <span class="detail-value">{{ $user->email }}</span>
            </div>
            @if($user->university)
            <div class="detail-item">
                <span class="detail-label">University:</span>
                <span class="detail-value">{{ $user->university->full_name }}</span>
            </div>
            @endif
            @if($user->roles->isNotEmpty())
            <div class="detail-item">
                <span class="detail-label">Role:</span>
                <span class="detail-value">{{ ucfirst($user->roles->first()->name) }}</span>
            </div>
            @endif
            <div class="detail-item">
                <span class="detail-label">Deletion Date:</span>
                <span class="detail-value">{{ $deletedAt->format('F j, Y \a\t g:i A T') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Deleted By:</span>
                <span class="detail-value">{{ $deletedBy->name }} ({{ $deletedBy->email }})</span>
            </div>
        </div>



        <!-- IMPORTANT INFORMATION SECTION -->
        <div class="important-info">
            <h3><span class="section-icon">ℹ️</span>Important Information</h3>
            <ul>
                <li><strong>Data Removal:</strong> All your personal data and account information have been permanently removed from our system.</li>
                <li><strong>Access Revoked:</strong> You can no longer access the Conference Management System with your previous credentials.</li>
                <li><strong>Conference Data:</strong> Any conferences you created or participated in may have been transferred to other administrators or archived according to our data retention policy.</li>
                <li><strong>Email Communications:</strong> You will no longer receive system notifications or updates related to the platform.</li>
            </ul>
        </div>

        <!-- CONTACT SECTION -->
        <div class="contact-section">
            <h3><span class="section-icon">📞</span>Need Assistance?</h3>
            <p>If you believe this account deletion was made in error, or if you have any questions about this action, please contact your system administrator immediately.</p>
            <p><strong>Important:</strong> Once an account is deleted, it cannot be recovered. If you need access to the system again, a new account will need to be created.</p>
        </div>

        <div class="footer">
            <p>
                This email was sent from the Animal Science Days Conference Management System.<br>
                This notification is for informational purposes only.
            </p>
            <p class="timestamp">
                Email sent on {{ now()->format('F j, Y \a\t g:i A T') }}
            </p>
            <p style="font-size: 12px; color: #999;">
                This is an automated message. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>