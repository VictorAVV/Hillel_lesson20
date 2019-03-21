<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     */
    public function blog()
    {
        return $this->render('blog/blog.html.twig', [
            'controller_name' => 'BlogController',
            'title' => 'Blog',
        ]);
    }

    /**
     * @Route("/blog/article/{id}", name="articleGet", requirements={"id"="\d+"})
     */
    public function article($id)
    {   

        switch ($id) {
            case 1:
                $title = 'Model-View-Controller';
                break;
            case 2:
                $title = 'Объектно-ориентированное программирование';
                break;
            case 3:
                $title = 'Bootstrap';
                break;
            case 4:
                $title = 'Bootstrap (фреймворк)';
                break;
            case 5:
                $title = 'HTML';
                break;
            default:
                //redirect 404
                throw $this->createNotFoundException('Article not found!');
        }

        if ($id) {
            $previousPage = $id - 1;
            $nextPage = $id + 1;
        }
        if ($id == 1) {
            $previousPage = false;
        }
        if ($id == 5) {
            $nextPage = false;
        }

        return $this->render("blog/article$id.html.twig", [
            'controller_name' => 'BlogController',
            'title' => $title,
            'previousPage' => $previousPage,
            'nextPage' => $nextPage,
        ]);
    }
}
