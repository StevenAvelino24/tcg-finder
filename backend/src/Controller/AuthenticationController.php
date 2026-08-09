<?php

namespace App\Controller;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\DetailUserDTO;
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
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

#[Route('/api', name: 'auth_')]
final class AuthenticationController extends AbstractController
{
    public function __construct(
        protected readonly UserServiceInterface $userService,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly VerifyEmailHelperInterface $verifyEmailHelper,
        protected readonly MailerInterface $mailer,
        protected readonly UserRepository $userRepository,
        private readonly ParameterBagInterface $params,
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

    #[Route('/verify/email', name: 'verify_email')]
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

        return $this->json([]);
    }

    #[Route('/resend_verify_email', name: 'resend_verify_email')]
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

        return $this->json([]);
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
                'signedUrl' => $this->params->get('app.frontend_url') . '?' . parse_url($signatureComponents->getSignedUrl(), PHP_URL_QUERY),
            ]);

        $this->mailer->send($email);
    }
}