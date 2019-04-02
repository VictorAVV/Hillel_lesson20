<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

//class CommonController extends Controller
class CommonController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {

        //$paginator  = $this->get('knp_paginator');
        //dump($paginator  = $this->getSubscribedServices());
        return $this->render('common/index.html.twig', [
            'controller_name' => 'CommonController',
            'title' => 'Homepage'
        ]);
    }

    /**
     * @Route("/about-us", name="aboutUs")
     */
    public function aboutUs()
    {
        return $this->render('common/aboutUs.html.twig', [
            'controller_name' => 'CommonController',
            'title' => 'About us'
        ]);
    }
}
