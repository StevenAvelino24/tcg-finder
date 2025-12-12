<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api', name: 'auth_')]
class AuthenticationController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ): JsonResponse
    {
        try {
            $user = $serializer->deserialize($request->getContent(), User::class, 'json');
            
            $data = json_decode($request->getContent(), true);
            if (!isset($data['password'])) {
                 return new JsonResponse(['message' => $translator->trans('auth.password.not_blank')], Response::HTTP_BAD_REQUEST);
            }
            $user->setPlainPassword($data['password']);

        } catch (\Exception $e) {
            return new JsonResponse(['message' => $translator->trans('auth.register.bad_request')], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($user); 

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }

            return new JsonResponse([
                'message' => $translator->trans('auth.register.form_not_valid'), 
                'errors' => $errorMessages
            ], Response::HTTP_UNPROCESSABLE_ENTITY); 
        }

        if ($entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()])) {
            return new JsonResponse(['message' => $translator->trans('auth.register.user_exists')], Response::HTTP_CONFLICT);
        }
        $user->setRoles(['ROLE_USER']);

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $user->getPlainPassword()
        );
        $user->setPassword($hashedPassword);
        $user->eraseCredentials(); 

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'message' => $translator->trans('auth.register.success'),
            'email' => $user->getEmail(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    #[IsGranted('ROLE_USER')] 
    public function getAuthenticatedUser(TranslatorInterface $translator): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => $translator->trans('auth.user.not_found')], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([

            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }
}