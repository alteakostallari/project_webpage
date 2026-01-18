<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

function sendEmail($toEmail, $code, $action = 'reset')
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mintplayground624@gmail.com';
        $mail->Password = 'izlotepcovkepgrq'; // User provided app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('mintplayground624@gmail.com', 'Sport Booking');
        $mail->addAddress($toEmail);
        $mail->isHTML(true);

        if ($action === 'reset') {
            $mail->Subject = 'Reset Your Password - MintPlayground';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                    <h2 style='color: #4ade80;'>🔐 Password Reset Request</h2>
                    <p>Your password reset code is:</p>
                    <h1 style='background: #f4f4f4; padding: 10px; display: inline-block; border-radius: 5px; color: #333;'>$code</h1>
                    <p>This code will expire in 1 hour.</p>
                </div>
            ";
        } elseif ($action === 'verify') {
            $mail->Subject = 'Verify Your Account - MintPlayground';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                    <h2 style='color: #4ade80;'>✅ Welcome to MintPlayground!</h2>
                    <p>Please use the code below to verify your account:</p>
                    <h1 style='background: #f4f4f4; padding: 10px; display: inline-block; border-radius: 5px; color: #333;'>$code</h1>
                    <p>If you did not sign up for this account, please ignore this email.</p>
                </div>
            ";
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
