<?php
/**
 * Created by PhpStorm.
 * User: danny
 * Date: 11/12/2019
 * Time: 23:18
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Swift_Mailer;

use App\Services\Responses;

class EmailController extends AbstractController
{
//    public function __construct(Swift_Mailer $swift_Mailer)
//    {
//
//    }

    /**
     * @param Request $request
     * @Route("/item-contact", name="item-contact", methods={"POST"})
     */
    public function index(Request $request, Swift_Mailer $swift_Mailer, Responses $responses) {

        $data = $responses->error("Erreur au momment de l'envoie du message");
        // receive data by post
        $json = $request->getContent();

        // decode json
        $params = json_decode($json);

        if($json !== null ) {
            $dest = (!empty($params->author)) ? $params->author : null;
            $from = (!empty($params->email)) ? $params->email : null;
            $ref = (!empty($params->ref)) ? $params->ref : null;
            $desc = (!empty($params->desc)) ? $params->desc : null;

            $message = (new \Swift_Message('Objets trouvés - Possible propriétaire'))
                ->setFrom('dannyfr.03@gmail.com')
                ->setTo($dest)
                ->setBody(
                    $this->renderView(
                    // templates/emails/registration.html.twig
                        'emails/contactItem.html.twig',
                        [
                            'emailFrom'     => $from,
                            'emailDest'     => $dest,
                            'ref'           => $ref,
                            'description'   => $desc
                        ]
                    ),
                    'text/html'
                );
            $swift_Mailer -> send($message);
            $data = $responses->success($params, 'Message envoyé avec succès');
        }

        return new JsonResponse($data);
    }
}