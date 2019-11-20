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
    public function create(Request $request, JwtAuth $jwtAuth, Responses $responses) {
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

    /**
     * @Route("/categories/{id}", name="category_show", methods={"GET"})
     */
    public function show(Responses $responses, $id = null) {
        // Default response
        $data = $responses->error('Cette catégorie n\'existe pas.');

        // 1. get category
        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy([
            'id' => $id
        ]);
        // 2. Verify and validate item
        if($category && is_object($category)) {
            // 3. Success response
            $data = $responses->success($category);
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/categories/edit/{id}", name="category_edit", methods={"PUT"})
     */
    public function edit(Request $request, JwtAuth $jwtAuth, Responses $responses, $id = null) {
        // Default response
        $data = $responses->error("Cette catégorie n'existe pas ou vous n'avez pas le droit de réaliser cette action.", 401);

        // 1. get token
        $token = $request->headers->get('Authorization');

        // 2. verify token
        $checkToken = $jwtAuth->checkToken($token);
        // 3. if token is OK
        if($checkToken) {
            // get data logged user
            $identity = $jwtAuth->checkToken($token, true);
            // 4. verify admin role
            if(!empty($identity) && $identity->role === 'admin') {
                $doctrine = $this->getDoctrine();
                // 5. get category objet
                $category = $doctrine->getRepository(Category::class)->findOneBy(['id' => $id]);

                // 6. verify category objet
                if($category && is_object($category)) {
                    // 7. Get data by POST
                    $json = $request->get('json', null);
                    $params = json_decode($json);
                    // verify data json
                    if(!empty($json)) {
                        $name = (!empty($params->name)) ? $params->name : null;

                        if(!empty($name)) {
                            $category->setName($name)->setUpdatedAt(new \DateTime('now'));

                            // save changes in DB
                            $entityManager = $doctrine->getManager();
                            $entityManager->persist($category);
                            $entityManager->flush();

                            // response if category update OK
                            $data = $responses->success($category, 'Catégorie mis à jour avec succès!');
                        }
                        else { $data = $responses->error("Il faut remplir le nom de la catégorie.", 422); }
                    }
                }
            }
        }
        else { $data = $responses->error("Le token n'est pas valide, essayez de vous reconnecter.", 401); }

        return new JsonResponse($data);
    }


}