<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CommonController extends AbstractController
{
    /**
     * @Route("/", name="common")
     */
    public function index()
    {
        return $this->render('common/index.html.twig', [
            'controller_name' => 'CommonController',
            'title' => 'Homepage'
        ]);
    }
}
