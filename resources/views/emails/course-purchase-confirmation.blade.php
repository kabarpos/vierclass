<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Purchase Confirmation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px 20px;
        }
        .course-info {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }
        .course-thumbnail {
            width: 100%;
            max-width: 300px;
            height: auto;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .course-title {
            font-size: 20px;
            font-weight: 600;
            color: #1e40af;
            margin: 0 0 10px 0;
        }
        .course-price {
            font-size: 18px;
            font-weight: 600;
            color: #059669;
            margin: 5px 0;
        }
        .transaction-info {
            background-color: #ecfdf5;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .transaction-info h3 {
            margin: 0 0 10px 0;
            color: #059669;
            font-size: 16px;
        }
        .transaction-detail {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
        }
        .button:hover {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
        }
        .next-steps {
            background-color: #fef3c7;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }
        .next-steps h3 {
            margin: 0 0 10px 0;
            color: #d97706;
        }
        .next-steps ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .next-steps li {
            margin: 5px 0;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 5px 0;
            color: #64748b;
            font-size: 14px;
        }
        .social-links {
            margin: 15px 0;
        }
        .social-links a {
            color: #3b82f6;
            text-decoration: none;
            margin: 0 10px;
        }
        @media only screen and (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 4px;
            }
            .content {
                padding: 20px 15px;
            }
            .header {
                padding: 20px 15px;
            }
            .button {
                display: block;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🎉 Purchase Successful!</h1>
            <p>Thank you for your course purchase</p>
        </div>

        <!-- Main Content -->
        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            
            <p>Congratulations! Your course purchase has been successfully processed. You now have <strong>lifetime access</strong> to your new course.</p>

            <!-- Course Information -->
            <div class="course-info">
                @if($courseThumbnail)
                    <x-lazy-image 
                        src="{{ $courseThumbnail }}" 
                        alt="{{ $course->name }}" 
                        class="course-thumbnail"
                        loading="lazy" />
                @endif
                <h2 class="course-title">{{ $course->name }}</h2>
                <p>{{ $course->tagline ?? 'Start learning today and advance your skills!' }}</p>
                <div class="course-price">Rp {{ number_format($course->price, 0, ',', '.') }}</div>
            </div>

            <!-- Transaction Details -->
            <div class="transaction-info">
                <h3>Transaction Details</h3>
                <div class="transaction-detail">
                    <span>Transaction ID:</span>
                    <span><strong>{{ $transaction->booking_trx_id }}</strong></span>
                </div>
                <div class="transaction-detail">
                    <span>Purchase Date:</span>
                    <span>{{ $transaction->created_at->format('d M Y, H:i') }}</span>
                </div>
                <div class="transaction-detail">
                    <span>Payment Status:</span>
                    <span><strong class="email-success-text">✓ Paid</strong></span>
                </div>
                <div class="transaction-detail">
                    <span>Access Status:</span>
                    <span><strong class="email-success-text">✓ Active</strong></span>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="email-center">
                <a href="{{ $courseUrl }}" class="button">Start Learning Now</a>
            </div>

            <!-- Next Steps -->
            <div class="next-steps">
                <h3>What's Next?</h3>
                <ul>
                    <li><strong>Access Your Course:</strong> Click the button above to start learning immediately</li>
                    <li><strong>Lifetime Access:</strong> You can return to this course anytime - it's yours forever!</li>
                    <li><strong>Track Progress:</strong> Visit your <a href="{{ $dashboardUrl }}" class="email-link-primary">dashboard</a> to see all your courses</li>
                    <li><strong>Get Support:</strong> Need help? Contact our <a href="{{ $supportUrl }}" class="email-link-primary">support team</a></li>
                </ul>
            </div>

            <p>We're excited to be part of your learning journey. If you have any questions or need assistance, don't hesitate to reach out to our support team.</p>

            <p>Happy learning!</p>
            <p><strong>The {{ \App\Helpers\WebsiteSettingHelper::get('site_name', 'LMS Platform') }} Team</strong></p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This email was sent to {{ $user->email }}</p>
            <p>© {{ date('Y') }} {{ \App\Helpers\WebsiteSettingHelper::get('site_name', 'LMS Platform') }}. All rights reserved.</p>
            
            <div class="social-links">
                <a href="#">Follow us on Social Media</a>
            </div>
            
            <p class="email-footer-text">
                If you're having trouble clicking the "Start Learning Now" button, copy and paste the URL below into your web browser:<br>
                <a href="{{ $courseUrl }}" class="email-link-blue">{{ $courseUrl }}</a>
            </p>
        </div>
    </div>
</body>
</html>