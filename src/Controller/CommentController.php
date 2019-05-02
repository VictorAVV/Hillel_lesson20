<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/comment")
 */
class CommentController extends AbstractController
{
    /**
     * @Route("/", name="comment_index", methods={"GET"})
     */
    public function index(CommentRepository $commentRepository): Response
    {   
        return $this->render('comment/index.html.twig', [
            'comments' => $commentRepository->findAll(),
        ]);
    }

    /**
     * show all comments for article
     * @Route("/articleComments", name="article_comments", methods={"GET", "POST"})
     */
    public function getArticleComments(Request $request, $article = null): Response
    {   
        if (null === $article) {
            throw $this->createNotFoundException('Article not found!');
        }
        
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $comment->setArticle($article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $comment->setUser($this->getUser());
            $commentRepository = $this->getDoctrine()->getRepository(Comment::class);
            $comment->setId(($commentRepository->getMaxId())['idMax'] + 1);

            if (null !== $request->request->get('parentCommentId') && 
                ($commentRepository->find($request->request->get('parentCommentId')))
            ) {
                $parentComment = new Comment();
                $parentComment = $commentRepository->find($request->request->get('parentCommentId'));
                $comment->setChildNodeOf($parentComment);
            } else {
                $parentComment = null;
            }
            
            $entityManager->persist($comment);
            $entityManager->flush();

            unset($comment);
            unset($form);
            $comment = new Comment();
            $form = $this->createForm(CommentType::class, $comment);
            //return $this->redirectToRoute('article_view', ['id' => $article->getId()]);
        }

        $comments = $this->getDoctrine()
            ->getRepository(Comment::class)
            ->findBy(['article' => $article]);

        dump($this->getDoctrine()->getRepository(Comment::class)->getRootNodes());    

        $comments2 = $this->getDoctrine()->getRepository(Comment::class)->getTree('', 't', ['articleId' => $article->getId()]);
        dump($comments2);

        $comments3 = $this->getDoctrine()->getRepository(Comment::class)
            ->createQueryBuilder('c')
            ->Join('c.article', 'carticle')
            ->andWhere("c.materializedPath = ''")
            ->andWhere('carticle.id = :aid')
            ->setParameter('aid', $article->getId())
            ->getQuery()
            ->getResult()
        ;
        dump($comments3);

        return $this->render('comment/articleComments.html.twig', [
            'comments' => $comments3,
            'commentsCount' => count($comments),
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * роут не нужен
     * @Route("/{id}", name="comment_show", methods={"GET"})
     */
    public function show(Comment $comment): Response
    {
        return $this->render('comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    /**
     * роут не нужен
     * @Route("/{id}/edit", name="comment_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Comment $comment): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('comment_index', [
                'id' => $comment->getId(),
            ]);
        }

        return $this->render('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="comment_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Comment $comment): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('comment_index');
    }
}
