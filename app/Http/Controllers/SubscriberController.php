<?php
namespace App\Http\Controllers;
use App\Mail\Subscribe;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Raju\EWSMail\ExchangeMailServer;
use \jamesiarmes\PhpEws\Client;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require '/Applications/XAMPP/xamppfiles/htdocs/mail-app/vendor/autoload.php';


class SubscriberController extends Controller
{
    public function subscribe(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required|email'
        ]);
        if($validator->fails()){
            return new JsonResponse(
                [
                    'success' => false, 
                    'message' => $validator->errors()
                ], 422
            );
        }
        $email = $request->all()['email'];
        $subscriber = Subscriber::create([
            'email' => $email
        ]);
        if($subscriber){
            /** Using Laravel package **/
            // Mail::to($email)->send(new Subscribe($email));

            // $ews = new Client("mail.treasurecomms.com", "tango", 
            // "c3OsFUCOSQ14deTrK4Yp", "Exchange2019");

            /** Using ExchangeMailServer package **/
            // ExchangeMailServer::sendEmail(
            //     ['name' => 'Tango Cloud', 
            //     'email' => $email], 

            //     ['subject' => 'Mail From Package', 
            //     'body' => 'Message Body']);

            $mail = new PHPMailer(true);
            $mail->SMTPOptions = array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true));

            try {
                //Server settings
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
                $mail->isSMTP();   
                $mail->Host = 'mail.treasurecomms.com';  // Specify main and backup SMTP servers
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = 'tango.treasurecomms.com';                 // SMTP username
                $mail->Password = 'c3OsFUCOSQ14deTrK4Yp';                          //SMTP password
                $mail->SMTPSecure = 'tls';              //Enable implicit TLS encryption
                $mail->Port = 587;                                  //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                $mail->setFrom('tango@treasurecomms.com', 'Tango Cloud');
                $mail->addAddress($email);
                // $mail->addCC('cc@example.com');
                // $mail->addBCC('bcc@example.com');

                //Attachments
                // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
                // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

                //Content
                $mail->isHTML(true);                                  //Set email format to HTML
                $mail->Subject = 'Here is the subject';
                $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
                $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                $mail->send();
                echo 'Message has been sent';
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
            


            return new JsonResponse(
                [
                    // 'success' => true,
                    // 'message' => "Thank you for subscribing to our email, please check your inbox"
                ], 200
            );
        }
    }
}