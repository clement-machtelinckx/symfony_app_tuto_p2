<?php

namespace App\Teast\Functional;

use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;



class DrangonTreasureResourceTest extends KernelTestCase
{
    use HasBrowser;
    use ResetDatabase;

    public function testGetCollectionOfTreasure(): void
    {
        $this->browser()
            ->get('/api/treasures')
            ->dump();
    }   
}