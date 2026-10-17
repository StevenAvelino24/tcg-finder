<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Auth\ForgotPasswordFormType;
use App\Form\Auth\RegistrationFormType;
use App\Form\Auth\ResetPasswordFormType;
use App\Repository\UserRepository;
use App\Service\User\UserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;

#[Route('/', name: 'auth_')]
final class AuthenticationController extends AbstractController
{
    use ResetPasswordControllerTrait;

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
            'fr' => '/s-enregistrer',
            'en' => '/register',
            'it' => '/register',
            'de' => '/register'
        ],
        name: 'register',
        methods: ['GET', 'POST']
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
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

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
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $user = $this->userRepository->findOneBy(['email' => $request->query->getString('email')]);

        if (!$user) {
            $this->addFlash('success', 'register.flash.verify_resent');
            return $this->redirectToRoute('auth_login');
        }

        if ($user->getIsVerified()) {
            $this->addFlash('success', 'register.flash.verify_resent');
            return $this->redirectToRoute('auth_login');
        }

        $this->userService->sendVerificationEmail($user->getId(), $user->getEmail());

        $this->addFlash('success', 'register.flash.verify_resent');

        return $this->redirectToRoute('auth_login');
    }

    #[Route('/backend/user', name: 'delete', methods: ['DELETE'])]
    public function delete(): JsonResponse
    {
        $this->entityManager->remove($this->getUser());
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(
        path: [
            'fr' => '/mot-de-passe-oublie',
            'en' => '/forgot-password',
            'it' => '/forgot-password',
            'de' => '/forgot-password'
        ],
        name: 'forgot_password',
        methods: ['GET', 'POST']
    )]
    public function forgotPassword(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(ForgotPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userRepository->findOneBy(['email' => $form->get('email')->getData()]);

            if (!$user) {
                $this->addFlash('success', 'auth.forgot_password.sent');

                return $this->redirectToRoute('auth_login');
            }

            $this->userService->sendForgotPasswordEmail($user);

            $this->addFlash('success', 'auth.forgot_password.sent');

            return $this->redirectToRoute('auth_login');
        }

        return $this->render('auth/forgot-password.html.twig', ['form' => $form]);
    }

    #[Route(
        path: [
            'fr' => '/reset-mot-de-passe',
            'en' => '/reset-password',
            'it' => '/reset-password',
            'de' => '/reset-password'
        ],
        name: 'reset_password',
        methods: ['GET', 'POST']
    )]
    public function resetPassword(Request $request) : Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $queryToken = $request->query->getString('token');

        if ($queryToken !== '') {
            $this->storeTokenInSession($queryToken);

            return $this->redirectToRoute('auth_reset_password');
        }

        $token = $this->getTokenFromSession();

        if (!$token) {
            throw $this->createNotFoundException('No reset password token found.');
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface) {
            $this->addFlash('error', 'auth.reset_password.invalid_token');

            $this->redirectToRoute('auth_forgot_password');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->resetPasswordHelper->removeResetRequest($token);

            $user->setPassword($this->passwordHasher->hashPassword($user, $form->get('password')->getData()));
            $this->entityManager->flush();

            $this->cleanSessionAfterReset();
            $this->addFlash('success', 'auth.reset_password.flash.success');

            return $this->redirectToRoute('auth_login');
        }

        return $this->render('auth/reset-password.html.twig', ['form' => $form]);
    }
}