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
use App\Services\JwtAuth;
use Symfony\Component\HttpFoundation\Request;
use App\Services\Responses;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


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
     * @Route("/upload/{id}", name="uploadByItem", methods={"POST"})
     **/
    public function uploadByItemId(Request $request, FileUploader $fileUploader, Responses $responses) {

        $data = $responses->error("Aucune image chargée");

        $files = $request->files->get('file');
        $id = $request->get('id');


        $item = $this->getDoctrine()
            ->getManager()
            ->getRepository(Item::class)
            ->findOneBy(['id' => $id]);

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
     * @Route("/images/delete/{id}", name="images-delete", methods={"DELETE"})
     * @return JsonResponse
     **/
    public function delete(Request $request, JwtAuth $jwtAuth, Responses $responses, $id = null) {

        $data = $responses->error('Aucune image a été supprimé, tentez plus tard');

        // 1. get token
        $token = $request->headers->get('Authorization');

        // 2. Verify and validate token
        $checkToken = $jwtAuth->checkToken($token);

        if($checkToken) {
            // get Doctrine
            $doctrine = $this->getDoctrine();
            // get EntityManager
            $entityManager = $doctrine->getManager();
            // get item to delete
            $image = $doctrine->getRepository(Image::class)->findOneBy([ 'id' => $id,]);
            // Remove item from DB
            if($image && is_object($image)) {
                $entityManager->remove($image);
                $entityManager->flush();
                // success response
                $data = $responses->success($image, "L'image a été supprimée avec succès.");
            }
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