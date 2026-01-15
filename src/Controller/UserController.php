<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
final class UserController extends AbstractController
{
    #[Route('', name: 'app_user_list')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        
        return $this->render('user/list.html.twig', [  
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->isMethod('POST')) {
            $user = new User();
            $user->setEmail($request->request->get('email'))
                ->setNom($request->request->get('nom'))
                ->setPrenom($request->request->get('prenom'))
                ->setTelephone($request->request->get('telephone'))
                ->setRoles([$request->request->get('role')])
                ->setPassword($passwordHasher->hashPassword($user, $request->request->get('password')));

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'User created successfully!');
            return $this->redirectToRoute('app_user_list');
        }

        return $this->render('user/new.html.twig');  
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [  
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->isMethod('POST')) {
            $user->setEmail($request->request->get('email'))
                ->setNom($request->request->get('nom'))
                ->setPrenom($request->request->get('prenom'))
                ->setTelephone($request->request->get('telephone'))
                ->setRoles([$request->request->get('role')]);

            $password = $request->request->get('password');
            if (!empty($password)) {
                $user->setPassword($passwordHasher->hashPassword($user, $password));
            }

            $manager->flush();

            $this->addFlash('success', 'User updated successfully!');
            return $this->redirectToRoute('app_user_list');
        }

        return $this->render('user/edit.html.twig', [  // Changed
            'user' => $user,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $manager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $manager->remove($user);
            $manager->flush();
            $this->addFlash('success', 'User deleted successfully!');
        }

        return $this->redirectToRoute('app_user_list');
    }
}