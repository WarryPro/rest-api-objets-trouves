<?php

namespace App\Controller;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validation;
use Doctrine\ORM\EntityManagerInterface;


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
        $response -> headers->set('Content-type', 'application/json');

        // Return the response
        return $response;

    }

    /**
     * List of users
     * @Route("/admin/users", name="users", methods={"GET"})
     */
    public function users(Request $request, JwtAuth $jwtAuth, PaginatorInterface $paginator, EntityManagerInterface $entityManager)
    {
        // Default response
        $data = [
          'status'  => 'error',
          'code'    => 404,
          'message' => 'Aucun utilisateur à afficher ou vous n\'avez pas le droit pour y accèder.'
        ];

        // 1. Get token
        $token = $request->headers->get('Authorization');

        // 2. Token verify
        $checkToken = $jwtAuth->checkToken($token);

        // 3. if token OK
        if($checkToken) {

            $dql = "SELECT u FROM App:User u ORDER BY u.id DESC";

            $query = $entityManager->createQuery($dql);

            // 6. Get param page
            $page = $request->query->getInt('page', 1);
            $usersPerPage = 6;
            // 7. Invok pagination
            $pagination = $paginator->paginate($query, $page, $usersPerPage);

            $total = $pagination->getTotalItemCount();


            $data = [
                'status'    => 'success',
                'code'      => 200,
                'total_users_count' => $total,
                'current_page'  => $page,
                'users_per_page'    => $usersPerPage,
                'total_pages'       => ceil($total / $usersPerPage),
                'users'             => $pagination->getItems()
            ];

        }
        return new JsonResponse($data);
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

    /**
     * @param Request $request
     * @Route("/user/edit", name="edit", methods={"PUT"})
     */
    public function edit(Request $request, JwtAuth $jwtAuth)
    {
        // 1. get auth header
        $token = $request->headers->get('Authorization');

        // 2. create method to verify if token is OK
        $checkToken = $jwtAuth->checkToken($token);

        // Reponse by default
        $data = [
            'status' => 'error',
            'code'  => 400,
            'message' => 'Utilisateur non autorisé!'
        ];

        // 3. If token OK, update user
        if($checkToken) {
            // Update user

            // get EntityManager
            $entityManager = $this->getDoctrine()->getManager();

            // get data logged user
            $identity = $jwtAuth->checkToken($token, true);

            // get user to update
            $userRepo = $this->getDoctrine()->getRepository(User::class);

            $user = $userRepo->findOneBy([
                'id' => $identity->sub
            ]);


            // Get data by POST
            $json = $request->get('json', null);
            $params = json_decode($json);

            // verify and validate data
            if(!empty($json)) {
                $firstname = (!empty($params->firstname)) ? $params->firstname : null;
                $lastname = (!empty($params->lastname)) ? $params->lastname : null;
                $email = (!empty($params->email)) ? $params->email : null;

                $validator = Validation::createValidator();
                $validateEmail = $validator->validate($email, [
                    new Email()
                ]);

                if(!empty($email) && count($validateEmail) == 0 && !empty($firstname) && !empty($lastname)) {
                    // assign new data to user objet
                    $user->setEmail($email);
                    $user->setFirstname($firstname);
                    $user->setLastname($lastname);

                    // verify duplicate data
                    $issetUser = $userRepo->findBy([
                       'email' => $email
                    ]);

                    // save changes in DB
                    if($issetUser === 0 || $identity->email === $email) {
                        $entityManager->persist($user);
                        $entityManager->flush();

                        // response if user update OK
                        $data = [
                            'status' => 'success',
                            'code'  => 400,
                            'message' => 'Utilisateur mis à jour avec succès!',
                            'user' => $user
                        ];
                    }else {
                        // Response if not updated
                        $data = [
                            'status' => 'error',
                            'code'  => 400,
                            'message' => 'Vous ne pouvez pas utiliser cet email!'
                        ];
                    }

                }
            }

        }

        return $this->JsonResponse($data);
    }

    /**
     * @Route("/admin/users/{id}", name="show", methods={"GET"})
     */
    public function show(Request $request, JwtAuth $jwtAuth, $id = null) {

        // Default response
        $data = [
            'status'  => 'error',
            'code'    => 404,
            'message' => 'L\'utilisateur n\'existe pas ou vous n\'avez pas le droit pour y accèder.'
        ];

        // 1. Get token
        $token = $request->headers->get('Authorization');

        // 2. create method to verify if token is OK
        $checkToken = $jwtAuth->checkToken($token);

        if($checkToken) {
            $identity = $jwtAuth->checkToken($token, true);
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                'id' => $id
            ]);
            // 3. Show user
            if($user && is_object($user) && $identity->role === 'admin') {
                // success response
                $data = [
                    'status'  => 'success',
                    'code'    => 404,
                    'message' => 'Détails de l\'utilisateur.',
                    'user'    => $user
                ];
            }
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/admin/users/delete/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(Request $request, JwtAuth $jwtAuth, $id = null) {

        // 1. Get user token
        $token = $request->headers->get('Authorization');
        $checkToken = $jwtAuth->checkToken($token);

        // Default response
        $data = [
          'status'  => 'error',
          'code'    => 404,
          'message' => 'Cet utilisateur n\'existe pas.'
        ];

        if($checkToken) {
            $identity = $jwtAuth->checkToken($token, true);

            // Get user by id from DB
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $id]);


            // Remove user from DB
            if($user && is_object($user) && $identity->role === 'admin') {

                $em = $this->getDoctrine()->getManager();
                $em->remove($user);
                $em->flush();

                // success response
                $data = [
                    'status'  => 'success',
                    'code'    => 200,
                    'message' => 'L\'utilisateur a été supprimé avec succès.'
                ];
            }

        }

        // Return data
        return new JsonResponse($data);
    }
}
