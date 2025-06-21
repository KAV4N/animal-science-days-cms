<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Details - Animal Science Days</title>
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
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 28px;
        }
        .welcome {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 25px;
        }
        .created-by {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
            font-size: 14px;
        }
        .created-by h4 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 16px;
        }
        .created-by-details {
            color: #495057;
        }
        .created-by-details strong {
            color: #007bff;
        }
        .credentials {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        .credentials h3 {
            margin-top: 0;
            color: #dc3545;
        }
        .user-info {
            background-color: #d1ecf1;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #17a2b8;
        }
        .user-info h3 {
            margin-top: 0;
            color: #17a2b8;
        }
        .credential-item, .info-item {
            margin: 10px 0;
            padding: 10px;
            background-color: white;
            border-radius: 3px;
        }
        .credential-label, .info-label {
            font-weight: bold;
            color: #495057;
        }
        .credential-value, .info-value {
            font-family: monospace;
            background-color: #f8f9fa;
            padding: 5px 8px;
            border-radius: 3px;
            border: 1px solid #dee2e6;
            display: inline-block;
            margin-left: 10px;
        }
        .changes-section {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        .changes-section h3 {
            margin-top: 0;
            color: #856404;
        }
        .change-item {
            margin: 15px 0;
            padding: 10px;
            background-color: white;
            border-radius: 3px;
            border: 1px solid #f0f0f0;
        }
        .change-label {
            font-weight: bold;
            color: #495057;
            text-transform: capitalize;
        }
        .change-values {
            margin-top: 5px;
        }
        .old-value {
            color: #dc3545;
            text-decoration: line-through;
            font-style: italic;
        }
        .new-value {
            color: #28a745;
            font-weight: bold;
        }
        .important-note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 15px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .section-icon {
            font-size: 18px;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Animal Science Days</h1>
            <p>Conference Management System</p>
        </div>

        <div class="welcome">
            <h2>
                @if($isNewUser)
                    Welcome, {{ $user->name }}!
                @else
                    Hello {{ $user->name }},
                @endif
            </h2>
            <p>
                @if($isNewUser)
                    Your account has been successfully created for the Animal Science Days Conference Management System. Below are your login credentials and account information.
                @else
                    Your account details have been updated. Below are your current login credentials, account information, and the changes that were made.
                @endif
            </p>
        </div>

        @if($createdBy)
        <div class="created-by">
            <h4><span class="section-icon">👤</span>
                @if($isNewUser)
                    Account Created By
                @else
                    Account Modified By
                @endif
            </h4>
            <div class="created-by-details">
                <strong>Name:</strong> {{ $createdBy->name }}<br>
                <strong>Email:</strong> {{ $createdBy->email }}
            </div>
        </div>
        @endif

        @if(!$isNewUser && !empty($changedFields))
        <div class="changes-section">
            <h3><span class="section-icon">🔄</span>Changes Made to Your Account</h3>
            @foreach($changedFields as $field => $values)
                <div class="change-item">
                    <div class="change-label">{{ ucfirst(str_replace('_', ' ', $field)) }}:</div>
                    <div class="change-values">
                        @if($field === 'password')
                            <span class="old-value">Previous password</span> → 
                            <span class="new-value">New password (shown below)</span>
                        @else
                            <span class="old-value">{{ $values['old'] }}</span> → 
                            <span class="new-value">{{ $values['new'] }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        <!-- LOGIN CREDENTIALS SECTION -->
        <div class="credentials">
            <h3><span class="section-icon">🔐</span>Login Credentials</h3>
            <div class="credential-item">
                <span class="credential-label">Email:</span>
                <span class="credential-value">{{ $user->email }}</span>
            </div>
            @if($password)
            <div class="credential-item">
                <span class="credential-label">Password:</span>
                <span class="credential-value">{{ $password }}</span>
            </div>
            @endif
        </div>

        <!-- USER INFORMATION SECTION -->
        <div class="user-info">
            <h3><span class="section-icon">👤</span>Account Information</h3>
            <div class="info-item">
                <span class="info-label">Full Name:</span>
                <span class="info-value">{{ $user->name }}</span>
            </div>
            @if($user->university)
            <div class="info-item">
                <span class="info-label">University:</span>
                <span class="info-value">{{ $user->university->full_name }}</span>
            </div>
            @endif
            @if($user->roles->isNotEmpty())
            <div class="info-item">
                <span class="info-label">Role:</span>
                <span class="info-value">{{ ucfirst($user->roles->first()->name) }}</span>
            </div>
            @endif
        </div>

        @if($password)
        <div class="important-note">
            <strong>Important Security Notice:</strong>
            <ul>
                <li>You will be required to change your password upon first login</li>
                <li>Please keep your login credentials secure and do not share them</li>
                <li>If you suspect your account has been compromised, contact your administrator immediately</li>
            </ul>
        </div>
        @endif

        <div style="text-align: center;">
            <a href="{{ config('app.frontend_url', config('app.url')) }}" class="button">
                Login to Your Account
            </a>
        </div>

        <div class="footer">
            <p>
                This email was sent from the Animal Science Days Conference Management System.<br>
                If you have any questions, please contact your system administrator.
            </p>
            <p style="font-size: 12px; color: #999;">
                This is an automated message. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>