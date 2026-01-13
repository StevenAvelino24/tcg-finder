<?php 

namespace App\Tests\Api;

use App\Factory\EventFactory;
use App\Factory\GameFactory;
use App\Factory\ShopFactory;
use App\Search\Index\EventIndex;
use App\Search\Index\ShopIndex;
use App\Search\Transformer\EventDocumentTransformer;
use App\Search\Transformer\ShopDocumentTransformer;
use App\Tests\AuthWebTestCase;
use Elastic\Elasticsearch\Client;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class SearchControllerTest extends AuthWebTestCase
{
    use ResetDatabase, Factories;

    private Client $esClient;

    private EventDocumentTransformer $eventDocumentTransformer;

    private ShopDocumentTransformer $shopDocumentTransformer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->esClient = self::getContainer()->get(Client::class);
        $this->eventDocumentTransformer = new EventDocumentTransformer();
        $this->shopDocumentTransformer = new ShopDocumentTransformer();
    }

    protected function tearDown(): void
    {
        $this->deleteIndex(ShopIndex::NAME . '_test');
        $this->deleteIndex(EventIndex::NAME . '_test');
        parent::tearDown();
    }

    protected function createIndex(string $name, array $mapping): void
    {
        $this->esClient->indices()->create([
            'index' => $name,
            'body' => $mapping,
        ]);
    }

    protected function deleteIndex(string $name): void
    {
        if (!$this->esClient->indices()->exists(['index' => $name])->asBool()) {
            return;
        }

        $this->esClient->indices()->delete(['index' => $name]);
    }

    protected function indexFixture(): void
    {
        $game = GameFactory::createOne(['name' => 'Magic']);
        $game2 = GameFactory::createOne(['name' => 'Pokemon']);
        $shops = [];
        $events = [];

        $shops[] = ShopFactory::createOne([
            'longitude' => 6.6336,
            'latitude' => 46.5199,
            'games' => [$game],
        ]);
        $events[] = EventFactory::createOne(['shop' => $shops[0], 'game' => $game]);

        $shops[] = ShopFactory::createOne([
            'longitude' => 6.6265,
            'latitude' => 46.5075,
            'games' => [$game, $game2],
        ]);
        $events[] = EventFactory::createOne(['shop' => $shops[1], 'game' => $game]);

        $shops[] = ShopFactory::createOne([
            'longitude' => 6.5880,
            'latitude' => 46.5390,
            'games' => [$game2],
        ]);
        $events[] = EventFactory::createOne(['shop' => $shops[2], 'game' => $game2]);

        $shops[] = ShopFactory::createOne([
            'longitude' => 6.6610,
            'latitude' => 46.5107,
            'games' => [$game2],
        ]);
        $events[] = EventFactory::createOne(['shop' => $shops[3], 'game' => $game2]);

        $shops[] = ShopFactory::createOne([
            'longitude' => 6.6680,
            'latitude' => 46.5510,
            'games' => [$game2],
        ]);
        $events[] = EventFactory::createOne(['shop' => $shops[4], 'game' => $game2]);

        foreach($shops as $shop) {
            $this->esClient->index([
                'index' => ShopIndex::NAME . '_test',
                'id' => $shop->getId(),
                'body' => $this->shopDocumentTransformer->transform($shop),
            ]);
        }

        foreach ($events as $event) {
            $this->esClient->index([
                'index' => EventIndex::NAME . '_test',
                'id' => $event->getId(),
                'body' => $this->eventDocumentTransformer->transform($event),
            ]);
        }

        $this->esClient->indices()->refresh(['index' => ShopIndex::NAME . '_test']);
        $this->esClient->indices()->refresh(['index' => EventIndex::NAME . '_test']);
    }

    public function testSearchReturnsEmptyResultsIfNoBoundingBox(): void
    {
        $this->client->jsonRequest(
            'GET',
            '/api/search?index=shop'
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame([
            'total' => 0,
            'results' => [],
        ], $data);
    }

    public function testSearchReturnsAllShopsInBoundingBox(): void
    {
        $this->createIndex(ShopIndex::NAME . '_test', ShopIndex::mapping());
        $this->indexFixture();

        $this->client->jsonRequest(
            'GET',
            '/api/search?index=' . ShopIndex::NAME . '_test' . '&geoBox[top_left][lat]=46.58&geoBox[top_left][lon]=6.56&geoBox[bottom_right][lat]=46.48&geoBox[bottom_right][lon]=6.72'
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(5, $data['results']);

        $this->deleteIndex(ShopIndex::NAME . '_test');
    }

    public function testSearchReturnsSomeShopsWithSmallerBoundingBox(): void
    {
        $this->createIndex(ShopIndex::NAME . '_test', ShopIndex::mapping());
        $this->indexFixture();

        $this->client->jsonRequest(
            'GET',
            '/api/search?index=' . ShopIndex::NAME . '_test' . '&geoBox[top_left][lat]=46.525&geoBox[top_left][lon]=6.620&geoBox[bottom_right][lat]=46.505&geoBox[bottom_right][lon]=6.640'
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $data['results']);
        $shop = $data['results'][0];
        $shop2 = $data['results'][1];

        $this->assertSame(['lat' => 46.5199, 'lon' => 6.6336], $shop['location']);
        $this->assertSame(['lat' => 46.5075, 'lon' => 6.6265], $shop2['location']);

        $this->deleteIndex(ShopIndex::NAME . '_test');
    }

    public function testSearchReturnsNoShopsWithBoundingBoxElsewhere(): void
    {
        $this->createIndex(ShopIndex::NAME . '_test', ShopIndex::mapping());
        $this->indexFixture();

        $this->client->jsonRequest(
            'GET',
            '/api/search?index=' . ShopIndex::NAME . '_test' . '&geoBox[top_left][lat]=46.48&geoBox[top_left][lon]=6.60&geoBox[bottom_right][lat]=46.45&geoBox[bottom_right][lon]=6.65'
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(0, $data['results']);

        $this->deleteIndex(ShopIndex::NAME . '_test');
    }

    public function testSearchReturnsAllEventsInBoundingBox(): void
    {
        $this->createIndex(EventIndex::NAME . '_test', EventIndex::mapping());
        $this->indexFixture();

        $this->client->jsonRequest(
            'GET',
            '/api/search?index=' . EventIndex::NAME . '_test' . '&geoBox[top_left][lat]=46.58&geoBox[top_left][lon]=6.56&geoBox[bottom_right][lat]=46.48&geoBox[bottom_right][lon]=6.72'
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(5, $data['results']);

        $this->deleteIndex(EventIndex::NAME . '_test');
    }

    public function testSearchReturnsSomeEventsWithSmallerBoundingBox(): void
    {
        $this->createIndex(EventIndex::NAME . '_test', EventIndex::mapping());
        $this->indexFixture();

        $this->client->jsonRequest(
            'GET',
            '/api/search?index=' . EventIndex::NAME . '_test' . '&geoBox[top_left][lat]=46.525&geoBox[top_left][lon]=6.620&geoBox[bottom_right][lat]=46.505&geoBox[bottom_right][lon]=6.640'
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $data['results']);
        $event = $data['results'][0];
        $event2 = $data['results'][1];

        $this->assertSame(['lat' => 46.5199, 'lon' => 6.6336], $event['location']);
        $this->assertSame(['lat' => 46.5075, 'lon' => 6.6265], $event2['location']);

        $this->deleteIndex(EventIndex::NAME . '_test');
    }

    public function testSearchReturnsNoEventsWithBoundingBoxElsewhere(): void
    {
        $this->createIndex(EventIndex::NAME . '_test', EventIndex::mapping());
        $this->indexFixture();

        $this->client->jsonRequest(
            'GET',
            '/api/search?index=' . EventIndex::NAME . '_test' . '&geoBox[top_left][lat]=46.48&geoBox[top_left][lon]=6.60&geoBox[bottom_right][lat]=46.45&geoBox[bottom_right][lon]=6.65'
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(0, $data['results']);

        $this->deleteIndex(EventIndex::NAME . '_test');
    }

    public function testSearchReturnsShopsWithGamesFilter(): void
    {
        $this->createIndex(ShopIndex::NAME . '_test', ShopIndex::mapping());
        $this->indexFixture();

        $this->client->jsonRequest(
            'GET',
            '/api/search?index=' . ShopIndex::NAME . '_test' . '&filters[games][games.name]=Magic&geoBox[top_left][lat]=46.58&geoBox[top_left][lon]=6.56&geoBox[bottom_right][lat]=46.48&geoBox[bottom_right][lon]=6.72'
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $data['results']);
        $this->assertSame([
            'lat' => 46.5199,
            'lon' => 6.6336,
        ], $data['results'][0]['location']);
        $this->assertSame([
            'lat' => 46.5075,
            'lon' => 6.6265,
        ], $data['results'][1]['location']);

        $this->deleteIndex(ShopIndex::NAME . '_test');
    }

    public function testSearchReturnsEventsWithGameFilter(): void
    {
        $this->createIndex(EventIndex::NAME . '_test', EventIndex::mapping());
        $this->indexFixture();

        $this->client->jsonRequest(
            'GET',
            '/api/search?index=' . EventIndex::NAME . '_test' . '&filters[game]=Pokemon&geoBox[top_left][lat]=46.58&geoBox[top_left][lon]=6.56&geoBox[bottom_right][lat]=46.48&geoBox[bottom_right][lon]=6.72'
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3, $data['results']);
        $this->assertSame([
            'lat' => 46.5390,
            'lon' => 6.5880,
        ], $data['results'][0]['location']);
        $this->assertSame([
            'lat' => 46.5107,
            'lon' => 6.6610,
        ], $data['results'][1]['location']);
        $this->assertSame([
            'lat' => 46.5510,
            'lon' => 6.6680,
        ], $data['results'][2]['location']);

        $this->deleteIndex(EventIndex::NAME . '_test');
    }
}