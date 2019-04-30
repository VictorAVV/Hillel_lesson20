<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
//use Symfony\Component\Security\Core\Encoder\EncoderFactory;
//use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }
    
    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout()
    {
        // controller can be blank: it will never be executed!
        return new RedirectResponse($this->urlGenerator->generate('blog'));
    }

    /**
     * @Route("/newUser", name="create_new_user", methods={"GET","POST"})
     */
    public function createNewUser(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            /* пример из:
            https://symfony.com/doc/current/components/security/authentication.html
            нафига нужен - непонятно.

            $defaultEncoder = new MessageDigestPasswordEncoder('sha512', true, 5000);
            $encoderFactory = new EncoderFactory([
                User::class => $defaultEncoder,
            ]);
            $encoder = $encoderFactory->getEncoder($user);
            $encodedPassword = $encoder->encodePassword($request->request->get('userPassword'), $user->getSalt());
            */
            
            $encodedPassword = $encoder->encodePassword($user, $request->request->get('user')['userPassword']);
            $user->setPassword($encodedPassword);

            dump($user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->render('security/viewUser.html.twig', [
                'user' => $user,
                'message' => 'Новый пользователь создан',
            ]);
        }

        return $this->render('security/newUser.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/viewUser", name="view_user")
     */
    public function viewUser(): Response
    {
        $user = $this->getUser();

        return $this->render('security/viewUser.html.twig', [
            'user' => $user,
            'message' => 'Информация о пользователе',
        ]);
    }

    /**
     * @Route("/editUser", name="edit_user", methods={"GET","POST"})
     */
    public function editUser(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->render('security/viewUser.html.twig');
        }
        
        $user = $this->getUser();
        
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encodedPassword = $encoder->encodePassword($user, $request->request->get('user')['userPassword']);
            $user->setPassword($encodedPassword);
            $this->getDoctrine()->getManager()->flush();

            return $this->render('security/viewUser.html.twig', [
                'user' => $user,
                'message' => 'Изменения сохранены',
            ]);
        }

        return $this->render('security/editUser.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/deleteUser", name="delete_user", methods={"DELETE"})
     */
    public function deleteUser(Request $request): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->render('security/viewUser.html.twig');
        }
        
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('delete'.$user->getEmail(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }
        
        //выдает ошибку:
        //You cannot refresh a user from the EntityUserProvider that does not contain an identifier. The user object has to be serialized with its own identifier mapped by Doctrine.
        
        return $this->redirectToRoute('app_logout');

        /*return $this->render('security/viewUser.html.twig', [
            'user' => $user,
            'message' => 'Пользователь удален',
        ]);*/
    }
}
