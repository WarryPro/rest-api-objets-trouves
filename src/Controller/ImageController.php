<?php
/**
 * Created by PhpStorm.
 * User: danny
 * Date: 16/12/2019
 * Time: 14:12
 */

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Item;
use App\Services\FileUploader;
use Symfony\Component\HttpFoundation\Request;
use App\Services\Responses;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;


class ImageController extends AbstractController
{

    /**
     * @Route("/upload", name="upload", methods={"POST"})
     **/
    public function upload(Request $request, FileUploader $fileUploader, Responses $responses) {

        $data = $responses->error("Aucune image chargée");

        $files = $request->files->get('file');


        $item = $this->getDoctrine()
            ->getManager()
            ->getRepository(Item::class)
            ->findLastInserted();

            $image = new Image();
            $image->setName($fileUploader->upload($files));
            $image->setItem($item);

            if(is_object($image)) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($image);
                $entityManager->flush();

                $data = $responses->success($image, "Image chargée avec succès");
            }


        return new JsonResponse($data);
    }

    /**
     * @Route("/images", name="images", methods={"GET"})
     * @return JsonResponse
     **/
    public function images() {
        $response = new Responses();
        $data = $response->error("Aucune image");

        $data= $this->getDoctrine()->getManager()->getRepository(Image::class)->findAll();

        return new JsonResponse($data);
    }

}