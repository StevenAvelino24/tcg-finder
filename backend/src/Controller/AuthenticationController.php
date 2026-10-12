<?php

namespace App\Controller;

use App\DTO\User\DetailUserDTO;
use App\DTO\User\ForgotPasswordDTO;
use App\DTO\User\ResetPasswordDTO;
use App\DTO\User\UpdateUserDTO;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Service\User\UserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/', name: 'auth_')]
final class AuthenticationController extends AbstractController
{
    public function __construct(
        protected readonly UserServiceInterface $userService,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly VerifyEmailHelperInterface $verifyEmailHelper,
        protected readonly MailerInterface $mailer,
        protected readonly UserRepository $userRepository,
        protected readonly ParameterBagInterface $params,
        protected readonly ResetPasswordHelperInterface $resetPasswordHelper,
        protected readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    #[Route(
        path: [
            'fr' => '/se-connecter',
            'en' => '/login',
            'it' => '/login',
            'de' => '/login'
        ],
        name: 'login'
    )]
    public function login(AuthenticationUtils $authUtils): Response
    {
        $error = $authUtils->getLastAuthenticationError();

        $lastUsername = $authUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
             'last_username' => $lastUsername,
             'error'         => $error,
        ]);
    }

    #[Route(
        path: [
            'fr' => '/admin/users',
            'en' => '/admin/users',
            'it' => '/admin/users',
            'de' => '/admin/users',
        ],
        name: 'list',
        methods: ['GET']
    )]
    public function list(
        Request $request
    ): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 25)));
        $search = $request->query->getString('search', '');
        $verified = $request->query->getBoolean('verified', true);

        $users = $this->userRepository->findSearchAndPaginated(
            $page,
            $limit,
            $search === '' ? null : $search,
            $verified
        );

        return $this->json(
            [
                'users' => array_map(
                    fn (User $user) => DetailUserDTO::fromEntity($user),
                    $users['data']
                ),
                'total' => $users['total'],
            ],
            Response::HTTP_OK
        );
    }

    #[Route(
        path: [
            'fr' => '/admin/users/delete/{id}',
            'en' => '/admin/users/delete/{id}',
            'it' => '/admin/users/delete/{id}',
            'de' => '/admin/users/delete/{id}',
        ],
        name: 'admin_delete',
        methods: ['DELETE']
    )]
    public function adminDelete(
        #[MapEntity(mapping: ['id' => 'id'])] User $user,
    ): JsonResponse
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(
        path: [
            'fr' => '/s-enregistrer',
            'en' => '/register',
            'it' => '/register',
            'de' => '/register'
        ],
        name: 'register',
    )]
    public function register(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $form->get('password')->getData()));
            $user->setRoles(['ROLE_USER']);

            $this->entityManager->persist($user);
            $this->entityManager->flush();


            $this->userService->sendVerificationEmail((string) $user->getId(), $user->getEmail());

            $this->addFlash('success', 'register.flash.check_email');

            return $this->redirectToRoute('auth_login');
        }

        return $this->render('auth/register.html.twig', ['form' => $form]);
    }

    #[Route('/verify_email', name: 'verify_email', methods: ['GET'])]
    public function verifyEmail(Request $request): Response
    {
        /** @var User **/
        $user = $this->userRepository->find($request->query->get('id'));

        if (!$user) {
            throw $this->createNotFoundException();
        }

        try {
            $this->verifyEmailHelper->validateEmailConfirmationFromRequest($request, (string) $user->getId(), $user->getEmail());
        } catch (Exception $e) {
            $this->addFlash('error', 'register.flash.verify_failed');
            return $this->redirectToRoute('app_login');
        }

        $user->setIsVerified(true);
        $this->entityManager->flush();

        $this->addFlash('success', 'register.flash.verified');
        
        return $this->redirectToRoute('auth_login');
    }

    #[Route('/resend_verify_email', name: 'resend_verify_email', methods: ['GET'])]
    public function resendVerifyEmail(Request $request): Response
    {
        $user = $this->userRepository->findOneBy(['email' => $request->query->getString('email')]);

        if (!$user) {
            return $this->json(null, Response::HTTP_OK);
        }

        if ($user->getIsVerified()) {
            return $this->json(null, Response::HTTP_OK);
        }

        $this->userService->sendVerificationEmail($user->getId(), $user->getEmail());

        $this->addFlash('success', 'register.flash.verify_resent');

        return $this->redirectToRoute('auth_login');
    }

    #[Route('/backend/user', name: 'update', methods: ['PUT'])]
    public function update(
        #[MapRequestPayload()] UpdateUserDTO $dto,
    ): JsonResponse {
        /** @var User **/
        $user = $this->getUser();
        $this->userService->updateFromDTO($dto, $user);

        $this->entityManager->flush();

        return $this->json(
            DetailUserDTO::fromEntity($user),
            Response::HTTP_OK
        );
    }

    #[Route('/backend/user', name: 'delete', methods: ['DELETE'])]
    public function delete(): JsonResponse
    {
        $this->entityManager->remove($this->getUser());
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/forgot_password', name: 'forgot_password', methods: ['POST'])]
    public function forgotPassword(
        #[MapRequestPayload()] ForgotPasswordDTO $dto,
    ): JsonResponse {
        $user = $this->userRepository->findOneBy(['email' => $dto->email]);

        if (!$user) {
            return $this->json([], Response::HTTP_OK);
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->json([], Response::HTTP_OK);
        }

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@tcg-finder.ch', 'TCG Finder'))
            ->to($user->getEmail())
            ->subject('Reset your password')
            ->htmlTemplate('emails/reset_password.html.twig')
            ->context(['resetUrl' => $this->params->get('app.frontend_url') . '/reset_password?token=' . $resetToken->getToken(), 'expiresAt' => $resetToken->getExpiresAt()]);

        $this->mailer->send($email);

        return $this->json([], Response::HTTP_OK);
    }

    #[Route('/reset_password', name: 'reset_password', methods: ['POST'])]
    public function resetPassword(
        #[MapRequestPayload()] ResetPasswordDTO $dto,
    ) : JsonResponse {
        $user = null;

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($dto->token);
        } catch (Exception $e) {
            $this->json([], Response::HTTP_BAD_REQUEST);
        }

        if (!$user) {
            return $this->json([], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userService->resetPassword($user, $dto->password);
        $this->entityManager->flush();

        return $this->json([]);
    }
}