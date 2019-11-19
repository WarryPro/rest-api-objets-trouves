<?php
/**
 * Created by PhpStorm.
 * User: danny
 * Date: 18/11/2019
 * Time: 23:25
 */

namespace App\Controller;

use App\Services\Responses;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;


use App\Entity\Category;

use App\Services\JwtAuth;

class CategoryController extends AbstractController
{
    /**
     * Categories list
     * @Route("/categories", name="categories", methods={"GET"})
     * @return JsonResponse($data)
     **/
    public function categories() {
        $data = $this->getDoctrine()->getRepository(Category::class)->findAll();
        return new JsonResponse($data);
    }

    /**
     * @Route("/admin/categories/new", name="category_create", methods={"POST"})
     * @return  JsonResponse()
     **/
    public function create(Request $request, JwtAuth $jwtAuth, Responses $responses, ValidatorInterface $validator) {
        // Default response
        $data = $responses->error("Vous devez être connecté en tant qu'admin pour créer une catégorie.", 401);
        // 1. get token
        $token = $request->headers->get('Authorization');
        // 2. verify and validate token
        $checkToken = $jwtAuth->checkToken($token);
        if($checkToken) {
            // 3. get data by POST
            $json = $request->get('json', null);
            $params = json_decode($json);

            // 4. get logged user object
            $identity = $jwtAuth->checkToken($token, true);

            // 5. Verify and validate admin OK
            if($identity->role === 'admin') {
                if(!empty($json)) {
                    $name = !empty($params->name) ? $params->name : null;
                    $doctrine = $this->getDoctrine();

                    // verify category not exist
                    $issetCategory = $doctrine->getRepository(Category::class)->findBy([
                        'name' => $name,
                    ]);

                    // save if category not exist
                    if(count($issetCategory) === 0 ) {
                        // Validation asserts Category entity
                        if(!empty($name) && $name !== null) {
                            $category = new Category();
                            $category->setName($name);
                            $em = $doctrine->getManager();
                            $em->persist($category);
                            $em->flush();
                            $data = $responses->success($category,"Catégorie créée avec succès.");
                        }
                        else {
                            $data = $responses->error("Il faut remplir tous les champs.", 422);
                        }
                    }else {
                        $data = $responses->error("Cette catégorie existe déjà.", 422);
                    }
                }
            }
        }

        return new JsonResponse($data);
    }


}