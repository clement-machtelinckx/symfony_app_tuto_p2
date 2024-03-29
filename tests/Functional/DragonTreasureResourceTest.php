<?php

namespace App\Test\Functional;

use App\Entity\ApiToken;
use Zenstruck\Browser\Json;
use App\Factory\UserFactory;
use App\Factory\ApiTokenFactory;
use Zenstruck\Browser\HttpOptions;
use App\Tests\Functional\ApiTestCase;
use App\Factory\DragonTreasureFactory;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;



class DrangonTreasureResourceTest extends ApiTestCase
{

    use ResetDatabase;


    public function testGetCollectionOfTreasure(): void
    {
        DragonTreasureFactory::createMany(5);
        $json = $this->browser()
            ->get('/api/treasures')
            ->assertJson()
            ->assertJsonMatches('"hydra:totalItems"', 5)
            ->assertJsonMatches('length("hydra:member")', 5)
            ->json()
        ;
        $this->assertSame(array_keys($json->decoded()['hydra:member'][0]), [
            '@id',
            '@type',
            'name',
            'description',
            'value',
            'coolFactor',
            'owner',
            'shortDescription',
            'plunderedAtAgo',
        ]);
    }

    public function testPostToCreateTreasure(): void
    {
        $user = UserFactory::createOne();
        $this->browser()
            ->actingAs($user)
            ->post('/api/treasures', [
                'json' => [],
            ])
            ->assertStatus(422)
            ->post('/api/treasures', HttpOptions::json([
                'name' => 'A shiny thing',
                'description' => 'It sparkles when I wave it in the air.',
                'value' => 1000,
                'coolFactor' => 5,
                'owner' => '/api/users/'.$user->getId(),
            ]))
            ->assertStatus(201)
            ->assertJsonMatches('name', 'A shiny thing')
        ;
    }

    public function testPostToCreateTreasureWithApiKey(): void
    {
        $token = ApiTokenFactory::createOne([
            'scopes' => [ApiToken::SCOPE_TREASURE_CREATE]
        ]);

        $this->browser()
            ->post('/api/treasures', [
                'json' => [],
                'headers' => [
                    'Authorization' => 'Bearer '.$token->getToken(),
                ]
            ])
            ->assertStatus(422);
    }

    public function testPostToCreateTreasureDeniedWithoutScope(): void
    {
        $token = ApiTokenFactory::createOne([
            'scopes' => [ApiToken::SCOPE_TREASURE_EDIT]
        ]);
        $this->browser()
            ->post('/api/treasures', [
                'json' => [],
                'headers' => [
                    'Authorization' => 'Bearer '.$token->getToken()
                ]
            ])
            ->assertStatus(403)
        ;
    }

    public function testPatchToUpdateTreasure(): void
    {
        $user = UserFactory::createone();
        $treasure = DragonTreasureFactory::createOne(['owner' => $user]);
        
        $this->browser()
            ->actingAs($user)
            ->patch('/api/treasures/'. $treasure->getId(), [
                'json' => [
                    'value' => 12345,
                ],
            ])
            ->assertStatus(200)
            ->assertJsonMatches('value', 12345);



        $user2 = UserFactory::createOne();

            $this->browser()
            ->actingAs($user2)
            ->patch('/api/treasures/'. $treasure->getId(), [
                'json' => [
                    'value' => 6789,
                ],
            ])
            ->assertStatus(403)
            // ->assertJsonmatches('value', 12345)
                ;


            $this->browser()
            ->actingAs($user)
            ->patch('/api/treasures/'. $treasure->getId(), [
                'json' => [
                    'owner' => '/api/users/'.$user2->getId(),
                ],
            ])
            ->assertStatus(403);
    }


    /**
     * not fucking working 
     */
    public function testAdminCanPatchToEditTreasure(): void
    {
        $admin = UserFactory::new()->asAdmin()->create();
        $treasure = DragonTreasureFactory::createOne([
            'isPublished' => true,
        ]);

        $this->browser()
            ->actingAs($admin)
            ->patch('/api/treasures/'.$treasure->getId(), [
                'json' => [
                    'value' => 12345,
                ],
            ])
            ->assertStatus(200)
            ->assertJsonMatches('value', 12345)
            ->assertJsonMatches('isPublished', true)
        ;
    }

    /**
     * same ici sa marche pas cette mlerde
     */
    public function testOwnerCanSeeIsPublishedField(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne([
            'isPublished' => false,
            'owner' => $user,
        ]);

        $this->browser()
            ->actingAs($user)
            ->patch('/api/treasures/'. $treasure->getId(), [
                'json' => [
                    'value' => 12345,
                ],
            ])
            ->assertStatus(200)
            ->assertJsonMatches('value', 12345)
            ->assertJsonMatches('isPublished', false)
            ;

    }

    


}