<?php

namespace App\Controller;

use App\Services\Responses;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Entity\User;
use App\Entity\Item;
use App\Entity\Category;

use App\Services\JwtAuth;

class ItemController extends AbstractController
{
    /**
     * List items
     * @Route("/", name="homepage", methods={"GET"})
     */
    public function items()
    {
        $itemRepo = $this->getDoctrine()->getRepository(Item::class);

        $items = $itemRepo->findAll();

        return new JsonResponse($items);
    }


    /**
     * @param Request $request
     * @param JwtAuth $jwtAuth
     * @Route("/items/new", name="new", methods={"POST"})
     * @return JsonResponse
     */
    public function create(Request $request, JwtAuth $jwtAuth, ValidatorInterface $validator) {
        // Response by default
        $data = [
            'status' => 'error',
            'code'  => 400,
            'message' => 'L\'objet n\'a pas était créé',
        ];

        // 1. get token
        $token = $request->headers->get('Authorization', null);

        // 2. Verify token is OK
        $checkToken = $jwtAuth->checkToken($token);
        if($checkToken) {

            // 3. get data by POST
            $json = $request->get('json', null);
            $params = json_decode($json);

            // 4. get logged user object
            $identity = $jwtAuth->checkToken($token, true);

            // 5. verify and validate data
            if(!empty($json)) {
                $userId      = ($identity->sub !== null) ? $identity->sub :  null;
                $title       = (!empty($params->title)) ? $params->title : '';
                $description = (!empty($params->description)) ? $params->description : null;
                $image       = (!empty($params->image)) ? $params->image : '';
                $type        = ($params->type) ? $params->type : 0;
                $category    = (!empty($params->category)) ? $params->category : null;
                $status      = ($params->status) ? $params->status : 'Normal';

                if(!empty($userId)) {
                    // 6. save item in DB
                    $entityManager = $this->getDoctrine()->getManager();
                    $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                        'id' => $userId
                    ]);

                    $categoryEntity = $this->getDoctrine()->getRepository(Category::class)->findOneBy([
                        'id' => $category,
                    ]);

                    //create object
                    $item = new Item();
                    $item ->setUser($user)
                          ->setTitle($title)
                          ->setDescription($description)
                          ->setImage($image)
                          ->setType($type)
                          ->setCategory($categoryEntity)
                          ->setStatus($status);

                    // Validation asserts Item entity
                    $errors = $validator->validate($item);
                    if(count($errors) > 0) {
                        // Return all asserts errors
                        return $this->json($errors);
                    }

                    // If all OK, save object
                    $entityManager->persist($item);
                    $entityManager->flush();

                    // Success Response
                    $data = [
                        'status' => 'succèss',
                        'code'  => 200,
                        'message' => 'L\'objet a était créé!',
                        'item' => $item,
                    ];
                }
            }

        }

        // 7. return response
        return new JsonResponse($data);
    }

    /**
     * @Route("/items/{id}", name="show_item", methods={"GET"})
     * @return JsonResponse
     **/
    public function show($id = null, Responses $responses) {
        // Default response
        $data = $responses->error('Cet objet n\'existe pas.');

        // 1. get item
        $item = $this->getDoctrine()->getRepository(Item::class)->findOneBy([
            'id' => $id
        ]);

        // 2. Verify and validate item
        if($item && is_object($item)) {
           // 3. Success response
            $data = $responses->success($item);
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/items/edit/{id}", name="item_edit", methods={"PUT"})
     */
    public function edit(Request $request, JwtAuth $jwtAuth, $id = null, Responses $responses) {
        // Default response
        $data = $responses->error('Objet non trouvé.');

        // 1. get token
        $token = $request->headers->get('Authorization');

        // 2. create method to verify if token is OK
        $checkToken = $jwtAuth->checkToken($token);

        if($checkToken) {
            // get data logged user
            $identity = $jwtAuth->checkToken($token, true);
            $doctrine = $this->getDoctrine();
            $entityManager = $doctrine->getManager();
            $userId = (!empty($identity->sub)) ? $identity->sub : null;

            if(!empty($userId) || $identity->role === 'admin' ) {

                // get item to update
                $item = $doctrine->getRepository(Item::class)->findOneBy([
                    'id' => $id,
                    'user' => $userId
                ]);

                if($item && is_object($item)) {
                    // Get data by POST
                    $json = $request->get('json', null);
                    $params = json_decode($json);
                    // verify and validate data
                    if(!empty($json)) {
                        $title = (!empty($params->title)) ? $params->title : null;
                        $description = (!empty($params->description)) ? $params->description : null;
                        $image = (!empty($params->image)) ? $params->image : null;
                        $type = (!empty($params->type)) ? $params->type : 0;
                        $status = $params->status;
                        $category = (!empty($params->category)) ? $params->category : null;


                        if(!empty($title)  && !empty($description) && !empty($image)) {
                            // assign new data to user objet
                            $item->setTitle($title)
                                ->setDescription($description)
                                ->setImage($image)
                                ->setType($type)
                                ->setStatus($status)
                                ->setUpdatedAt( new \DateTime("now"))
                                ->setImage($category);

                            // save changes in DB
                            $entityManager->persist($item);
                            $entityManager->flush();

                            // response if user update OK
                            $data = $responses->success($item, 'Objet mis à jour avec succès!');
                        }
                    }

                }
            }
        }else {
            // Response if not updated
            $data = $responses->error('Vous ne pouvez pas realiser cette action.', 401);
        }
        return new JsonResponse($data);
    }

}
