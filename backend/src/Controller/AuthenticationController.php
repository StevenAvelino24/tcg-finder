<?php

namespace App\Controller;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\DetailUserDTO;
use App\DTO\User\ForgotPasswordDTO;
use App\DTO\User\ResetPasswordDTO;
use App\DTO\User\UpdateUserDTO;
use App\Entity\User;
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
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

#[Route('/api', name: 'auth_')]
final class AuthenticationController extends AbstractController
{
    public function __construct(
        protected readonly UserServiceInterface $userService,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly VerifyEmailHelperInterface $verifyEmailHelper,
        protected readonly MailerInterface $mailer,
        protected readonly UserRepository $userRepository,
        protected readonly ParameterBagInterface $params,
        protected readonly ResetPasswordHelperInterface $resetPasswordHelper
    ) {}

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload()] CreateUserDTO $dto
    ): JsonResponse
    {
        $user = $this->userService->createFromDTO($dto);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->sendVerificationEmail($user->getId(), $user->getEmail());

        return $this->json(
            DetailUserDTO::fromEntity($user),
            Response::HTTP_CREATED
        );
    }

    #[Route('/verify_email', name: 'verify_email', methods: ['GET'])]
    public function verifyEmail(Request $request): JsonResponse
    {
        /** @var User **/
        $user = $this->userRepository->find($request->query->get('id'));

        if (!$user) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        if ($user->getIsVerified()) {
            return $this->json(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $this->verifyEmailHelper->validateEmailConfirmationFromRequest($request, (string) $user->getId(), $user->getEmail());
        } catch (Exception $e) {
            return $this->json(['id' => $user->getId()], 400);
        }

        $user->setIsVerified(true);
        $this->entityManager->flush();

        return $this->json([], Response::HTTP_OK);
    }

    #[Route('/resend_verify_email', name: 'resend_verify_email', methods: ['GET'])]
    public function resendVerifyEmail(Request $request): JsonResponse
    {
        $user = $this->userRepository->find($request->query->get('id'));

         if (!$user) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        if ($user->getIsVerified()) {
            return $this->json(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->sendVerificationEmail($user->getId(), $user->getEmail());

        return $this->json([], Response::HTTP_OK);
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

    #[Route('/backend/user', name: 'show', methods: ['GET'])]
    public function getAuthenticatedUser(): JsonResponse
    {
        /** @var User **/
        $user = $this->getUser();

        return $this->json(
            DetailUserDTO::fromEntity($user),
            Response::HTTP_OK
        );
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

    protected function sendVerificationEmail(string $userId, string $userEmail)
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            'auth_verify_email',
            $userId,
            $userEmail,
            ['id' => $userId]
        );

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@tcg-finder.ch', 'TCG Finder'))
            ->to((string) $userEmail)
            ->subject('Please confirm your email')
            ->htmlTemplate('emails/verify_email.html.twig')
            ->context([
                'userEmail' => $userEmail,
                'signedUrl' => $this->params->get('app.frontend_url') . '/verify_email?' . parse_url($signatureComponents->getSignedUrl(), PHP_URL_QUERY),
            ]);

        $this->mailer->send($email);
    }
}