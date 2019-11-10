<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validation;


use App\Entity\User;
use App\Entity\Item;
use App\Entity\Category;

class UserController extends AbstractController
{
    /*
     * */
    private function jsonResponse($data) {
        // Serialize data with serializer service
        $json = $this->get('serializer')->serialize($data, 'json');

        // Response with HttpFoundation
        $response = new Response();

        // Assign content to response
        $response ->setContent($json);

        // Format response
        $response -> headers ->set('Content-type', 'application/json');

        // Return the response
        return $response;

    }


    /**
     * @Route("/user", name="user")
     */
    public function index()
    {

        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $ItemRepo = $this->getDoctrine()->getRepository(Item::class);

        $users = $userRepo->findAll();


//        foreach ($users as $user) {
//            echo "<p>{$user->getFirstname()} {$user->getLastname()}</p>";
//        }


        $data = [
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ];
        //jsonResponse($users)
        return $this->json($users);
    }


    /**
     * @Route("/register", name="register", methods={"POST"})
     */
    public function create(Request $request) {

        // receive data by post
        $json = $request->get('json', null);

        // decode json
        $params = json_decode($json);

        // resp by default
        $data = [
            'status'  => 'error',
            'code'    => 200,
            'message' => 'L\'utilisateur n\'est pas été créé',
        ];

        // verify and validate data
        if($json !== null) {
           $firstname = (!empty($params->firstname)) ? $params->firstname : null;
           $email = (!empty($params->email)) ? $params->email : null;
           $password = (!empty($params->password)) ? $params->password : null;

            $validator = Validation::createValidator();
            $validateEmail = $validator->validate($email, [
                new Email()
            ]);

            if(!empty($email) && count($validateEmail) == 0 && !empty($password) && !empty($firstname)) {
                $data = [
                    'status'  => 'success',
                    'code'    => 200,
                    'message' => 'Validation correcte!',
                ];
            }else {
                $data = [
                    'status'  => 'error',
                    'code'    => 200,
                    'message' => 'Validation erronée',
                ];
            }

        }

        // if validate OK, create user object

        // crypt password

        // verify if user exists

        // if not exists save in the DB

        // Make json response

        return new JsonResponse($data);
    }
}
