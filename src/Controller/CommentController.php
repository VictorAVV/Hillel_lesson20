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

        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $comment->setUser($this->getUser());
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

        $commentsCount = $commentRepository->getCountAllCommentsOfArticle($article->getId());
        $comments = [];
        $allRootComments = $commentRepository->getAllRootCommentsOfArticle($article->getId());
        //dump($allRootComments);

        //если вызвать getTree() только один раз, то не прогрузятся все дочерние комментарии
        //поэтому вызываем getTree() для корневых комментариев у которых есть дочерние комментарии
        foreach($allRootComments as $value){
            if ($value['numberOfChild']) {
                $comments[] = $this->getDoctrine()->getRepository(Comment::class)->getTree('/' . ($value[0]->getId()));
            } else {
                $comments[] = $value[0];
            }
        }

        //dump($comments);
        return $this->render('comment/articleComments.html.twig', [
            'comments' => $comments,
            'commentsCount' => $commentsCount,
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
