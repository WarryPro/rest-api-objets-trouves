<?php

namespace App\Controller;

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
    public function show($id = null) {
        // Default response
        $data = [
            'status'  => 'error',
            'code'    => 404,
            'message' => 'Cet objet n\'existe pas.'
        ];

        // 1. get item
        $item = $this->getDoctrine()->getRepository(Item::class)->findOneBy([
            'id' => $id
        ]);

        // 2. Verify and validate item
        if($item && is_object($item)) {
           // 3. Success response
            $data = [
                'status'    => 'success',
                'code'      => 200,
                'item'      => $item
            ];
        }

        return new JsonResponse($data);
    }



}
