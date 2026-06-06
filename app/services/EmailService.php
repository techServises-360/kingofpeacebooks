<?php
class EmailService {
    private $fromEmail;
    private $fromName;
    private $replyTo;
    
    public function __construct() {
        $this->fromEmail = 'noreply@kingofpeacebooks.com';
        $this->fromName = 'KingOfPeace Books';
        $this->replyTo = 'support@kingofpeacebooks.com';
    }
    
    public function sendVerificationEmail($email, $code, $userType = 'user') {
        $subject = 'KingOfPeace Books - ' . ($userType === 'author' ? 'Author' : 'Email') . ' Verification Code';
        
        if ($userType === 'author') {
            $body = "Dear Author,\n\n" .
                   "Your verification code for KingOfPeace Books is: $code\n\n" .
                   "This code expires in 15 minutes. Please use this code to complete your author registration.\n\n" .
                   "Thank you for choosing KingOfPeace Books - Your Home for African Knowledge, Truth, and Wisdom.\n\n" .
                   "Best regards,\n" .
                   "The KingOfPeace Books Team";
        } else {
            $body = "Dear User,\n\n" .
                   "Your verification code for KingOfPeace Books is: $code\n\n" .
                   "This code expires in 15 minutes. Please use this code to complete your registration.\n\n" .
                   "Thank you for choosing KingOfPeace Books - Your Home for African Knowledge, Truth, and Wisdom.\n\n" .
                   "Best regards,\n" .
                   "The KingOfPeace Books Team";
        }
        
        $headers = [
            'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
            'Reply-To: ' . $this->replyTo,
            'X-Mailer: PHP/' . phpversion(),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8'
        ];
        
        return mail($email, $subject, $body, implode("\r\n", $headers));
    }
    
    public function sendHTMLVerificationEmail($email, $code, $userType = 'user') {
        $subject = 'KingOfPeace Books - ' . ($userType === 'author' ? 'Author' : 'Email') . ' Verification Code';
        
        $htmlBody = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . $subject . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { color: #0a4ea1; font-size: 24px; font-weight: bold; }
        .code-box { background: #f8f9fa; border: 2px solid #0a4ea1; padding: 20px; text-align: center; margin: 20px 0; }
        .code { font-size: 32px; font-weight: bold; color: #0a4ea1; letter-spacing: 5px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">KingOfPeace Books</div>
            <h2>Email Verification</h2>
        </div>
        
        <p>Dear ' . ($userType === 'author' ? 'Author' : 'User') . ',</p>
        
        <p>Your verification code for KingOfPeace Books is:</p>
        
        <div class="code-box">
            <div class="code">' . $code . '</div>
        </div>
        
        <p>This code expires in 15 minutes. Please use this code to complete your ' . ($userType === 'author' ? 'author' : '') . ' registration.</p>
        
        <p>Thank you for choosing KingOfPeace Books - Your Home for African Knowledge, Truth, and Wisdom.</p>
        
        <div class="footer">
            <p>Best regards,<br>The KingOfPeace Books Team</p>
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>';
        
        $textBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $htmlBody));
        
        $boundary = uniqid('np');
        
        $headers = [
            'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
            'Reply-To: ' . $this->replyTo,
            'X-Mailer: PHP/' . phpversion(),
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"'
        ];
        
        $message = "--$boundary\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= $textBody . "\r\n\r\n";
        
        $message .= "--$boundary\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= $htmlBody . "\r\n\r\n";
        
        $message .= "--$boundary--";
        
        return mail($email, $subject, $message, implode("\r\n", $headers));
    }
}
