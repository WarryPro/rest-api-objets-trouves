<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Item;
use App\Entity\Category;

class ItemController extends AbstractController
{
    /**
     * @Route("/item", name="item")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ItemController.php',
        ]);
    }
}
