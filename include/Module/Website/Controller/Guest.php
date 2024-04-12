<?php

namespace MerapiPanel\Module\Website\Controller;

use MerapiPanel\Box\Module\__Fragment;
use MerapiPanel\Utility\Http\Request;
use MerapiPanel\Views\View;
use MerapiPanel\Utility\Http\Response;
use MerapiPanel\Utility\Router;
use PHPMailer\PHPMailer\PHPMailer;

class Guest extends __Fragment
{

    function onCreate(\MerapiPanel\Box\Module\Entity\Module $module)
    {

    }


    public function register()
    {

        Router::GET("/", "index", self::class);
        Router::GET("/", "index", self::class);
        Router::GET("/{lang[id,en,cn,jp,es,ru]}", "index", self::class);
        Router::GET("/about", "about", self::class);
        Router::GET("/{lang[id,en,cn,jp,es,ru]}/about", "about", self::class);
        Router::GET("/contact-us", "contactUs", self::class);
        Router::GET("/{lang[id,en,cn,jp,es,ru]}/contact-us", "contactUs", self::class);
        Router::POST("/contact/send/email", "sendEmail", self::class);
    }


    function sendEmail(Request $request)
    {
        $captcha = $request->gRecaptchaResponse();
        if (!$captcha || !$this->verifyCaptcha($captcha)) {
            return new Response("Captcha verification failed!", 200);
        }

        if (isset($_COOKIE['CTX_EMAIL'])) {
            return new Response("Email already sent!, try again later.", 200);
        }

        $cc_emails = [
            [
                "name" => "Ilham B",
                "email" => "durianbohong@gmail.com"
            ],
            [
                "name" => "Said shodiq mufadhol",
                "email" => "shodiqmufadhol1@gmail.com"
            ]
        ];

        $name = $request->name();
        $email = $request->email();
        $message = $request->message();
        $subject = $request->subject();
        $headers = "From: " . $name . " <" . $email . ">\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $sender = [
            "email" => "server@tecnofy.id",
            "password" => "=CnVsoP=kzbC"
        ];

        $mail = new PHPMailer(true);
        $body = <<<EOT
        <style>
            table, th, td {
                border: none;
                cellspacing: 0;
                border-collapse: collapse;
                width: 100%;
            }
        </style>
        <p>new email from $name</p>
        <table>
            <tr>
                <td>Name</td>
                <td>$name</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>$email</td>
            </tr>
        </table>
        <p>$message</p>
        EOT;

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = "mail.tecnofy.id";
            $mail->SMTPAuth = true;
            $mail->Username = $sender["email"];
            $mail->Password = $sender["password"];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            // Recipients
            $mail->setFrom($sender["email"], "tecnofy.id");
            $mail->addAddress($sender["email"]);

            // CC
            array_map(function ($cc) use ($mail) {
                $mail->addCC($cc["email"], $cc["name"]);
            }, $cc_emails);

            $mail->addReplyTo($email, $name);
            $mail->addCC("server@tecnofy.id", "tecnofy.id");
            $mail->addBCC("server@tecnofy.id", "tecnofy.id");
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            if (!$mail->send()) {
                return new Response("Message could not be sent. Mailer Error: {$mail->ErrorInfo}", 200);
            }

            $this->sayThanks($email, $name);

            setcookie("CTX_EMAIL", "1", time() + 3600, "/");
            return "OK";
        } catch (\Exception $e) {
            return new Response("Message could not be sent. Mailer Error: {$mail->ErrorInfo}", 200);
        }
    }

    public function sayThanks($email, $name)
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = "mail.tecnofy.id";
        $mail->SMTPAuth = true;
        $mail->Username = "server@tecnofy.id";
        $mail->Password = "=CnVsoP=kzbC";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        // Recipients
        $mail->setFrom("server@tecnofy.id", "tecnofy.id");
        $mail->addAddress($email, $name);
        $mail->addReplyTo("server@tecnofy.id", "tecnofy.id");
        $mail->addReplyTo("durianbohong@gmail.com", "ilham b");
        $mail->addCC("server@tecnofy.id", "tecnofy.id");
        $mail->addBCC("server@tecnofy.id", "tecnofy.id");


        // Content
        $mail->isHTML(true);
        $mail->Subject = "Email anda telah kami terima";

        $pdfContent = file_get_contents(__DIR__ . "/../Assets/pdf/konfirmasi-email.pdf");
        $pdfBase64 = base64_encode($pdfContent);
        // Embed the PDF in the email body
        $mail->Body = "<h1>Email anda telah kami terima</h1>";
        $mail->Body .= "<p>Terima kasih telah menghubungi kami $name, Email anda telah kami terima, tim kami akan segera menghubungi anda.</p>";
        $mail->Body .= '<embed src="data:application/pdf;base64,' . $pdfBase64 . '" width="100%" height="500px">';

        // Add the PDF as an attachment (optional)
        $mail->addStringAttachment($pdfContent, 'attached_pdf.pdf', 'base64', 'application/pdf');


        $mail->AltBody = "Terima kasih telah menghubungi kami $name, Email anda telah kami terima, tim kami akan segera menghubungi anda.";
        if (!$mail->send()) {
            return new Response("Message could not be sent. Mailer Error: {$mail->ErrorInfo}", 200);
        }
        return "OK";
    }

    private function verifyCaptcha($request)
    {
        $serversideapi = "6LfNebIpAAAAAEgNvypLAHFngFm93eDWrLKIkQ7r";
        $endpoint = "https://www.google.com/recaptcha/api/siteverify";
        $clientIp = $_SERVER['REMOTE_ADDR'];

        $data = [
            'secret' => $serversideapi,
            'response' => $request,
            'remoteip' => $clientIp
        ];

        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($endpoint, false, $context);
        $result = json_decode($result);
        return $result->success;
    }


    public function index($request)
    {
        $lang = $request->lang();
        // throw new \Exception("Hello World");



        return View::render("index.html.twig", [
            "lang" => $lang
        ], $lang);
    }


    public function about($request)
    {
        $lang = $request->lang();

        return View::render("about.html.twig", [
            "lang" => $lang
        ], $lang);
    }


    function contactUs($request)
    {

        $referer = $request->http("referer");
        if (empty($referer)) {
            $referer = $_SERVER['HTTP_REFERER'];
        }

        $link = "https://wa.me/6287742222966?text=" . rawurlencode("Hi, I'm interested in your business. I'm from " . $referer);
        header("Location: " . $link);

        $response = new Response("Redirecting to " . $link);
        $response->setHeader("Location", $link);

        return $response;
    }
}
