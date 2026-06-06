function doGet(e) {
  // Handle different parameter structures
  const params = e.parameters || e.parameter || {};
  const email = (params.email || '').trim();
  const code = (params.code || '').trim();
  const action = (params.action || '').trim().toLowerCase();

  if (action !== 'send') return text('invalid');
  if (!email || !code) return text('missing');

  // Customize subject/body based on user type or default
  const userType = (params.type || '').trim().toLowerCase();
  let subject;
  
  if (userType === 'author') {
    subject = 'KingOfPeace Books - Author Verification Code';
  } else {
    subject = 'KingOfPeace Books - Email Verification Code';
  }
  
  // Create beautiful HTML email template with branding
  const htmlBody = createVerificationEmailHTML(code, userType);
  
  // Send HTML email using GmailApp
  try {
    GmailApp.sendEmail(email, subject, '', {
      htmlBody: htmlBody,
      name: 'KingOfPeace Books',
      replyTo: 'support@kingofpeacebooks.com'
    });
    return text('ok');
  } catch (error) {
    return text('error: ' + error.toString());
  }
}

function createVerificationEmailHTML(code, userType) {
  const isAuthor = userType === 'author';
  const greeting = isAuthor ? 'Dear Author' : 'Dear User';
  const message = isAuthor ? 
    'This code expires in 15 minutes. Please use this code to complete your author registration.' :
    'This code expires in 15 minutes. Please use this code to complete your registration.';
  
  return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KingOfPeace Books - Email Verification</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #0a4ea1 0%, #1a5fb4 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.15)"/><circle cx="10" cy="50" r="0.5" fill="rgba(255,255,255,0.15)"/><circle cx="90" cy="30" r="0.5" fill="rgba(255,255,255,0.15)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .logo-container {
            position: relative;
            z-index: 1;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: bold;
            background: linear-gradient(45deg, #0a4ea1, #e0b100);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .brand-name {
            color: white;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .tagline {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            font-style: italic;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #0a4ea1;
            margin-bottom: 20px;
        }
        
        .message {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .code-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #0a4ea1;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
            position: relative;
            overflow: hidden;
        }
        
        .code-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(224, 177, 0, 0.1), transparent);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .code-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .code {
            font-size: 36px;
            font-weight: 700;
            color: #0a4ea1;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            background: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(10, 78, 161, 0.2);
            position: relative;
            z-index: 1;
        }
        
        .expiry {
            margin-top: 15px;
            font-size: 12px;
            color: #e74c3c;
            font-weight: 600;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        
        .brand-message {
            color: #0a4ea1;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .signature {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .support {
            font-size: 12px;
            color: #999;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .highlight {
            color: #e0b100;
            font-weight: 600;
        }
        
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 8px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .code {
                font-size: 28px;
                letter-spacing: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <div class="logo">
                    <div class="logo-text">KP</div>
                </div>
                <div class="brand-name">KingOfPeace Books</div>
                <div class="tagline">Your Home for African Knowledge, Truth, and Wisdom</div>
            </div>
        </div>
        
        <div class="content">
            <div class="greeting">${greeting},</div>
            
            <div class="message">
                Welcome to KingOfPeace Books! Your verification code is ready to activate your account and unlock your journey to discovering amazing African literature and knowledge.
            </div>
            
            <div class="code-section">
                <div class="code-label">Your Verification Code</div>
                <div class="code">${code}</div>
                <div class="expiry">⏰ This code expires in 15 minutes</div>
            </div>
            
            <div class="message">
                ${message}
            </div>
            
            <div class="message">
                Thank you for choosing <span class="highlight">KingOfPeace Books</span> - where every book opens a door to wisdom, culture, and the rich heritage of Africa.
            </div>
        </div>
        
        <div class="footer">
            <div class="brand-message">📚 Empowering Minds Through African Literature</div>
            <div class="signature">
                Best regards,<br>
                <strong>The KingOfPeace Books Team</strong>
            </div>
            <div class="support">
                This is an automated message. For support, please reply to this email or contact us at support@kingofpeacebooks.com
            </div>
        </div>
    </div>
</body>
</html>`;
}

function doPost(e) { 
  return doGet(e); 
}

function text(s) {
  return ContentService.createTextOutput(s).setMimeType(ContentService.MimeType.TEXT);
}
