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
use Symfony\Component\Validator\Validation;
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


}