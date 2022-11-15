<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends CoreController
{
    /**
     * @Route("/users", name="user_list")
     */
    public function listAction()
    {
        return $this->render('user/list.html.twig', ['users' => $this->emi->getRepository(User::class)->findAll()]);
    }

    /**
     * @Route("/users/create", name="user_create")
     */
    public function createAction(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->getUser() !== null && in_array('ROLE_ADMIN',$this->getUser()->getRoles())) {
                $password = $this->pswEncoder->hashPassword($user, $user->getPassword());
                
                $user->setPassword($password);
                
                $this->emi->persist($user);
                $this->emi->flush();
                $this->addFlash('success', "L'utilisateur a bien été ajouté.");
            }else{
                $this->addFlash('error', "Vous ne pouvez pas créer un utilisateur.");
            }

            return $this->redirectToRoute('user_list');
        
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);

    }

    /**
     * @Route("/users/{id}/edit", name="user_edit")
     */
    public function editAction(User $user, Request $request)
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $password = $this->hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($password);

            $this->emi->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
