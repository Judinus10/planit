<?php
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

// require 'vendor/autoload.php';  // Include Composer's autoloader

// $otp = rand(100000, 999999);
// $user_email = 'jjudinas@gmail.com';  // Replace with recipient email

// $mail = new PHPMailer(true);

// try {
//     //Server settings
//     $mail->isSMTP();
//     $mail->Host       = 'smtp.gmail.com';
//     $mail->SMTPAuth   = true;
//     $mail->Username   = 'judinushu@gmail.com';   // Your Gmail address
//     $mail->Password   = 'tlcm hmuq lvhv fynw';      // Your Gmail App Password
//     $mail->SMTPSecure = 'tls';
//     $mail->Port       = 587;

//     //Recipients
//     $mail->setFrom('judinushu@gmail.com', 'Xampp Project');
//     $mail->addAddress($user_email);

//     //Content
//     $mail->isHTML(false);
//     $mail->Subject = 'Your OTP Code';
//     $mail->Body    = "Your OTP code is: $otp";

//     $mail->send();
//     echo 'OTP email sent successfully!';
// } catch (Exception $e) {
//     echo "Mailer Error: {$mail->ErrorInfo}";
// }

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendOtpEmail($recipient_email, $otp) {
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'judinushu@gmail.com';  
        $mail->Password   = 'tlcm hmuq lvhv fynw';    
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Sender & recipient
        $mail->setFrom('judinushu@gmail.com', 'Xampp Project');
        $mail->addAddress($recipient_email);

        // Email content
        $mail->isHTML(false);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = "Your OTP code is: $otp";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

function sendEmail($to, $body, $subject = "Task Reminder") {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'judinushu@gmail.com';   
        $mail->Password   = 'tlcm hmuq lvhv fynw';      
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('judinushu@gmail.com', 'Task Manager');
        $mail->addAddress($to);

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>

