<?php

namespace App\Tests\Api;

use App\Entity\Event;
use App\Factory\EventFactory;
use App\Factory\GameFactory;
use App\Factory\ParticipantFactory;
use App\Factory\ShopFactory;
use App\Factory\UserFactory;
use App\Repository\EventRepository;
use App\Repository\ParticipantRepository;
use App\Repository\UserRepository;
use App\Search\Index\EventIndex;
use App\Tests\AuthWebTestCase;
use DateTimeImmutable;
use Elastic\Elasticsearch\Client;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class EventControllerTest extends AuthWebTestCase
{
    use ResetDatabase, Factories;

    private UserRepository $userRepository;
    private EventRepository $eventRepository;
    private ParticipantRepository $participantRepository;
    private Client $esClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->eventRepository = static::getContainer()->get(EventRepository::class);
        $this->participantRepository = static::getContainer()->get(ParticipantRepository::class);
        $this->esClient = static::getContainer()->get(Client::class);

        if ($this->esClient->indices()->exists(['index' => EventIndex::NAME])->asBool()) {
            $this->esClient->indices()->delete(['index' => EventIndex::NAME]);
        }
    }

    protected function tearDown(): void
    {
        if ($this->esClient->indices()->exists(['index' => EventIndex::NAME])->asBool()) {
            $this->esClient->indices()->delete(['index' => EventIndex::NAME]);
        }
        parent::tearDown();
    }

    protected function getAllEsEventDocuments(): array
    {
        $response = $this->esClient->search([
            'index' => EventIndex::NAME,
            'body'  => [
                'query' => [
                    'match_all' => (object) [],
                ]
            ],
            'size' => 1000,
        ]);

        return array_map(
            fn (array $hit) => $hit['_source'],
            $response['hits']['hits']
        );
    }

    public function testListEventsReturnsErrorIfNotAdmin(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234');

        $this->client->jsonRequest(
            'GET',
            '/api/admin/events'
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testListEventsReturnsAllEventsIfAdmin(): void
    {
        EventFactory::createOne();
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_ADMIN']);

        $this->client->jsonRequest(
            'GET',
            '/api/admin/events'
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $data);
    }

    public function testListByShopEventsReturnsAnErrorIfNotLoggedIn(): void
    {
        $this->client->jsonRequest(
            'GET',
            '/api/backend/shops/test/events'
        );

        $this->assertResponseStatusCodeSame(expectedCode: 401);
    }

    public function testListByShopEventsReturnsAnErrorIfUserShopIsNotEnabled(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => false]);

        $this->client->jsonRequest(
            'GET',
            '/api/backend/shops/' . $shop->getSlug() . '/events'
        );

        $this->assertResponseStatusCodeSame(expectedCode: 403);
    }

    public function testListByShopEventsReturnsEventsRelatedToTheShop(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_ADMIN']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => true]);

        $secondUser = UserFactory::createOne();
        $secondShop = ShopFactory::createOne(['user' => $secondUser, 'enabled' => true]);

        EventFactory::createOne(['shop' => $shop]);
        EventFactory::createOne(['shop' => $secondShop]);

        $this->client->jsonRequest(
            'GET',
            '/api/backend/shops/' . $shop->getSlug() . '/events'
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $data);
    }

    public function testShowEventReturnsErrorIfEventShopIsNotEnabled(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_ADMIN']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => false]);

        $event = EventFactory::createOne(['shop' => $shop]);

        $this->client->jsonRequest(
            'GET',
            '/api/events/' . $event->getSlug() 
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testShowEventReturnsEventIfShopIsEnabled(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_ADMIN']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => true]);

        $event = EventFactory::createOne(['shop' => $shop]);

        $this->client->jsonRequest(
            'GET',
            '/api/events/' . $event->getSlug() 
        );

        $this->assertResponseStatusCodeSame(200);
    }

    public function testCreateEventReturnsErrorIfNotLoggedIn(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/api/backend/events',
            []
        );

        $this->assertResponseStatusCodeSame(expectedCode: 401);
    }

    public function testCreateEventReturnsErrorIfShopIsNotEnabled(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => false]);
        $game = GameFactory::createOne();
        $date = new DateTimeImmutable();

        $this->client->jsonRequest(
            'POST',
            '/api/backend/events',
            [
                'name' => 'Test Event',
                'gameId' => $game->getId(),
                'format' => 'standard',
                'entryCost' => "5 francs",
                'description' => 'Test description',
                'numberParticipants' => 10,
                'startDateTime' => $date->format('d M Y H:i:s'),
                'endDateTime' => $date->format('d M Y H:i:s'),
                'shopId' => $shop->getId(),
            ]
        );

        $this->assertResponseStatusCodeSame(expectedCode: 403);
    }

    public function testCreateEventReturnsErrorIfDataIsNotValid(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        ShopFactory::createOne(['user' => $user, 'enabled' => true]);

        $this->client->jsonRequest(
            'POST',
            '/api/backend/events',
            [
                'name' => 'Test Event',
            ]
        );

        $this->assertResponseStatusCodeSame(expectedCode: 422);
    }

    public function testCreateEventReturnsSuccessDataIsCorrect(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => true]);
        $game = GameFactory::createOne();
        $date = new DateTimeImmutable();

        $this->client->jsonRequest(
            'POST',
            '/api/backend/events',
            [
                'name' => 'Test Event',
                'gameId' => $game->getId(),
                'format' => 'standard',
                'entryCost' => "5 francs",
                'description' => 'Test description',
                'numberParticipants' => 10,
                'startDateTime' => $date->format('d M Y H:i:s'),
                'endDateTime' => $date->format('d M Y H:i:s'),
                'shopId' => $shop->getId(),
            ]
        );

        $this->assertResponseStatusCodeSame(expectedCode: 201);

        $event = $this->eventRepository->findOneBy(['slug' => 'test-event']);
        $this->assertInstanceOf(Event::class, $event);
    }

    public function testUpdateEventReturnsErrorIfNotLoggedIn(): void
    {
        $event = EventFactory::createOne();

        $this->client->jsonRequest(
            'PUT',
            '/api/backend/events/' . $event->getSlug(),
            []
        );

        $this->assertResponseStatusCodeSame(expectedCode: 401);
    }

    public function testUpdateEventReturnsErrorIfUserIsNotShopOwner(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = UserFactory::createOne();
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => true]);
        $event = EventFactory::createOne(['shop' => $shop]);

        $this->client->jsonRequest(
            'PUT',
            '/api/backend/events/' . $event->getSlug(),
            []
        );

        $this->assertResponseStatusCodeSame(expectedCode: 403);
    }

    public function testUpdateEventReturnsErrorIfEventShopIsNotEnabled(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => false]);
        $event = EventFactory::createOne(['shop' => $shop]);

        $this->client->jsonRequest(
            'PUT',
            '/api/backend/events/' . $event->getSlug(),
            []
        );

        $this->assertResponseStatusCodeSame(expectedCode: 403);
    }

    public function testUpdateEventReturnsErrorIfDataIsNotValid(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => true]);
        $event = EventFactory::createOne(['shop' => $shop]);

        $this->client->jsonRequest(
            'PUT',
            '/api/backend/events/' . $event->getSlug(),
            [
                'name' => 'Test'
            ]
        );

        $this->assertResponseStatusCodeSame(expectedCode: 422);
    }

    public function testUpdateEventReturnsSuccessIfDataIsValid(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => true]);
        $event = EventFactory::createOne(['shop' => $shop]);
        $game = GameFactory::createOne();
        $date = new DateTimeImmutable();

        $this->client->jsonRequest(
            'PUT',
            '/api/backend/events/' . $event->getSlug(),
            [
                'name' => 'Test Event',
                'gameId' => $game->getId(),
                'format' => 'standard',
                'entryCost' => "5 francs",
                'description' => 'Test description',
                'numberParticipants' => 10,
                'startDateTime' => $date->format('d M Y H:i:s'),
                'endDateTime' => $date->format('d M Y H:i:s'),
                'shopId' => $shop->getId(),
            ]
        );

        $this->assertResponseStatusCodeSame(expectedCode: 200);
        $event = $this->eventRepository->findOneBy(['slug' => $event->getSlug()]);
        $this->assertSame('Test Event', $event->getName());
    }

    public function testDeleteEventReturnsErrorIfUserNotLoggedIn(): void
    {
        $event = EventFactory::createOne();

        $this->client->jsonRequest(
            'DELETE',
            '/api/backend/events/' . $event->getSlug(),
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDeleteEventReturnsErrorIfUserNotShopOwner(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = UserFactory::createOne();
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => true]);
        $event = EventFactory::createOne(['shop' => $shop]);

        $this->client->jsonRequest(
            'DELETE',
            '/api/backend/events/' . $event->getSlug()
        );

        $this->assertResponseStatusCodeSame(expectedCode: 403);
    }

    public function testDeleteEventReturnsErrorIfShopIsNotEnabled(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => false]);
        $event = EventFactory::createOne(['shop' => $shop]);

        $this->client->jsonRequest(
            'DELETE',
            '/api/backend/events/' . $event->getSlug()
        );

        $this->assertResponseStatusCodeSame(expectedCode: 403);
    }

    public function testDeleteEventReturnsSuccessIfUserHasAccess(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => true]);
        $event = EventFactory::createOne(['shop' => $shop]);

        $this->client->jsonRequest(
            'DELETE',
            '/api/backend/events/' . $event->getSlug()
        );

        $this->assertResponseStatusCodeSame(expectedCode: 204);

        $event = $this->eventRepository->findOneBy(['slug' => $event->getSlug()]);
        $this->assertNull($event);
    }

    public function testRegisterEventReturnsErrorIfNoMoreSpots(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => true]);
        $event = EventFactory::createOne(['shop' => $shop, 'numberParticipants' => 1]);
        ParticipantFactory::createOne(['event' => $event]);

        $this->client->jsonRequest(
            'POST',
            '/api/events/' . $event->getSlug() . '/register',
            [
                'firstName' => 'Toto',
                'lastName' => 'Tata',
                'email' => 'test@toto.ch'
            ]
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testRegisterEventReturnsErrorIfPayloadIsNotCorrect(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => true]);
        $event = EventFactory::createOne(['shop' => $shop, 'numberParticipants' => 5]);

        $this->client->jsonRequest(
            'POST',
            '/api/events/' . $event->getSlug() . '/register',
            ['firstName' => 'test']
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testRegisterEventReturnsErrorIfShopIsNotEnabled(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => false]);
        $event = EventFactory::createOne(['shop' => $shop, 'numberParticipants' => 5]);

        $this->client->jsonRequest(
            'POST',
            '/api/events/' . $event->getSlug() . '/register',
            [
                'firstName' => 'Toto',
                'lastName' => 'Tata',
                'email' => 'test@toto.ch'
            ]
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testRegisterEventReturnsSuccess(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => true]);
        $event = EventFactory::createOne(['shop' => $shop]);

        $this->client->jsonRequest(
            'POST',
            '/api/events/' . $event->getSlug() . '/register',
            [
                'firstName' => 'Toto',
                'lastName' => 'Tata',
                'email' => 'test@toto.ch'
            ]
        );

        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame([
            'firstName' => 'Toto',
            'lastName' => 'Tata',
            'email' => 'test@toto.ch'
        ], $data);

        $this->assertEmailCount(1);
    }

    public function testUnregisterEventReturnsErrorIfNoTokenInQueryStrings(): void
    {
        $this->client->jsonRequest(
            'DELETE',
            '/api/events/unregister'
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUnregisterEventReturnsErrorIfTokenIsNotTiedToAnyParticipant(): void
    {
        ParticipantFactory::createOne();

        $this->client->jsonRequest(
            'DELETE',
            '/api/events/unregister?token=toottata'
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUnregisterEventReturnsSuccessIfTokenTiedToParticipant(): void
    {
        $participant = ParticipantFactory::createOne();

        $this->client->jsonRequest(
            'DELETE',
            '/api/events/unregister?token=' . $participant->getUnregisterToken()
        );

        $this->assertResponseStatusCodeSame(204);

        $oldParticipant = $this->participantRepository->findOneBy(['email' => $participant->getEmail()]);
        $this->assertNull($oldParticipant);
    }

    public function testCreateEventIndexesToEsProperly(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => true]);
        $game = GameFactory::createOne();
        $date = new DateTimeImmutable();

        $this->client->jsonRequest(
            'POST',
            '/api/backend/events',
            [
                'name' => 'Test Event',
                'gameId' => $game->getId(),
                'format' => 'standard',
                'entryCost' => "5 francs",
                'description' => 'Test description',
                'numberParticipants' => 10,
                'startDateTime' => $date->format('d M Y H:i:s'),
                'endDateTime' => $date->format('d M Y H:i:s'),
                'shopId' => $shop->getId(),
            ]
        );

        $this->assertResponseStatusCodeSame(expectedCode: 201);

        $this->esClient->indices()->refresh(['index' => EventIndex::NAME]);

        $documents = $this->getAllEsEventDocuments();

        $this->assertCount(1, $documents);
        $this->assertSame('Test Event', $documents[0]['name']);
    }

    public function testUpdateEventUpdateEsDocument(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => true]);
        $event = EventFactory::createOne(['shop' => $shop]);
        $game = GameFactory::createOne();
        $date = new DateTimeImmutable();

        $this->esClient->indices()->refresh(['index' => EventIndex::NAME]);

        $documents = $this->getAllEsEventDocuments();

        $this->assertCount(1, $documents);
        $this->assertSame($event->getName(), $documents[0]['name']);

        $this->client->jsonRequest(
            'PUT',
            '/api/backend/events/' . $event->getSlug(),
            [
                'name' => 'Test Event',
                'gameId' => $game->getId(),
                'format' => 'standard',
                'entryCost' => "5 francs",
                'description' => 'Test description',
                'numberParticipants' => 10,
                'startDateTime' => $date->format('d M Y H:i:s'),
                'endDateTime' => $date->format('d M Y H:i:s'),
                'shopId' => $shop->getId(),
            ]
        );

        $this->assertResponseStatusCodeSame(expectedCode: 200);

        $this->esClient->indices()->refresh(['index' => EventIndex::NAME]);

        $documents = $this->getAllEsEventDocuments();

        $this->assertCount(1, $documents);
        $this->assertSame('Test Event', $documents[0]['name']);

    }

    public function testDeleteEventRemovesEsDocument(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => true]);
        $event = EventFactory::createOne(['shop' => $shop]);

        $this->esClient->indices()->refresh(['index' => EventIndex::NAME]);

        $documents = $this->getAllEsEventDocuments();

        $this->assertCount(1, $documents);
        $this->assertSame($event->getName(), $documents[0]['name']);

        $this->client->jsonRequest(
            'DELETE',
            '/api/backend/events/' . $event->getSlug()
        );

        $this->assertResponseStatusCodeSame(expectedCode: 204);

        $this->esClient->indices()->refresh(['index' => EventIndex::NAME]);

        $documents = $this->getAllEsEventDocuments();

        $this->assertCount(0, $documents);
    }
}