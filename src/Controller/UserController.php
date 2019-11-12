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

use App\Services\JwtAuth;


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

        //jsonResponse($users)
        return $this->json($users);
    }


    /**
     * @Route("/register", name="register", methods={"POST"})
     */
    public function create(Request $request)
    {

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
           $lastname = (!empty($params->lastname)) ? $params->lastname : null;
           $email = (!empty($params->email)) ? $params->email : null;
           $password = (!empty($params->password)) ? $params->password : null;

            $validator = Validation::createValidator();
            $validateEmail = $validator->validate($email, [
                new Email()
            ]);

            if(!empty($email) && count($validateEmail) == 0 && !empty($password) && !empty($firstname)) {

                // if validate OK, create user object
                $user = new User();

                $user -> setFirstname($firstname)
                      -> setLastname($lastname)
                      -> setEmail($email)
                      -> setPassword(hash('sha256', $password)); // crypt password


                // verify if user exists
                $doctrine = $this->getDoctrine();
                $em = $doctrine->getManager();
                $userRepo = $doctrine->getRepository(User::class);

                $issetUser = $userRepo->findBy(array(
                    'email' => $email
                ));

                // if not exists save in the DB
                if(count($issetUser) === 0) {

                    $em->persist($user);
                    $em->flush();
                    $data = [
                        'status'  => 'success',
                        'code'    => 200,
                        'message' => 'Utilisateur créé avec succès!',
                        'user'    => $user,
                    ];
                }
                else {
                    $data = [
                        'status'  => 'error',
                        'code'    => 200,
                        'message' => 'Erreur, l\'utilisateur existe déjà!',
                    ];
                }

            }

        }

        // Make json response
        return new JsonResponse($data);
    }


    /**
     * @param Request $request
     * @Route("/login", name="login", methods={"POST"})
     */
    public function login(Request $request, JwtAuth $jwtAuth)
    {
        // 1. get data by POST method
        $json = $request->get('json', null);
        $params = json_decode($json);

        // 2. Default Array to return
        $data =  [
            'status' => 'Error',
            'code'  => 200,
            'message' => 'Erreur de connexion!',
        ];

        // 3. Verfify and validate data
        if($json !== null) {
            $email = (!empty($params->email)) ? $params->email : null;
            $password = (!empty($params->password)) ? $params->password : null;
            $getToken = (!empty($params->getToken)) ? $params->getToken : null;

            $validator = Validation::createValidator();
            $validateEmail = $validator->validate($email, [
                new Email()
            ]);

            if(!empty($email) && !empty($password) && count($validateEmail) == 0) {

                // 4. Crypt password
                $pwd = hash('sha256', $password);
                // 5. if validation is OK, call a service for identify User (jwt, token or an obj)
                if($getToken) {
                    $signup = $jwtAuth->signup($email, $pwd, $getToken);

                }else {
                    $signup = $jwtAuth->signup($email, $pwd);
                }

                return new JsonResponse($signup);
                // 6. create jwt service


            }

        }


        // 7. if data is OK, response
        return $this -> jsonResponse($data);
    }
}
