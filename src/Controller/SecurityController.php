<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\FormError;

use App\Entity\User;
use App\Form\UserFormType;


class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }


  /**
     * @Route("/admin/user/", name="user")
     */
    public function userList()
    {
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        // chercher tous les utilisateurs
        $userList = $userRepository->findAll();
        //afficher dans le twig
        return $this->render('security/index.html.twig', [
            'userList' => $userList,
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/admin/user/gestion/{id?}",name="user_register_edit")
     */
    public function gestion(EntityManagerInterface $em,Request $request,UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();

        if($request->get('id') != null){
            $userRepository = $this->getDoctrine()->getRepository(User::class);
            $id = $request->get('id');
            $user = $userRepository->find($id);
        }

        $form = $this->createForm(UserFormType::class,$user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $roles = $form->get('roles')->getData();
            $user->setRoles([0 => $roles]);

            if($form['password']->getData() != null){
                $plainPassword = $form['password']->getData();
                if (trim($plainPassword) != '') {
                    //encrypt pass
                    $password = $passwordEncoder->encodePassword($user, $plainPassword);
                    $user->setPassword($password);
                } else {
                    $passError = new FormError("Age must be greater than 18");
                    $form->get('password')->addError($passError);
                }
            }

            $em->persist($user);
            $em->flush();
            $this->addFlash('success','User added successfully');
        }

        return $this->render('security/gestion.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/delete/{id}",name="user_delete")
     */
    public function deleteUser(User $user, EntityManagerInterface $em)
    {
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'User successfully deleted');

        return $this->redirectToRoute('admin/user', []);
    }
}
