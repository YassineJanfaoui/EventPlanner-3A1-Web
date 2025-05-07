<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Twilio\Rest\Client;



class SmsTelController extends AbstractController
{
   
    // rest of the controller code
    #[Route('/app-sms', name: 'app_sms', methods: ['POST','GET'])]
    public function sendSMS(Request $request)
    {
        $toPhoneNumber = $request->request->get('toPhoneNumber');
        $smsBody = $request->request->get('smsBody');
        
        $sid = 'ACaba77eb7d59bdaa0f2691c38e13bd1a2';
        $token = 'bd0b061e3a94be97ba7520726806f790';
        $client = new Client($sid, $token);
        $message = $client->messages->create(
            "+216".$toPhoneNumber,
            [
                'from' => '+16206477715', 
                'body' => $smsBody,
            ]
        );
      
        return $this->redirectToRoute('app_event_indexx', [], Response::HTTP_SEE_OTHER);
    }
    
}