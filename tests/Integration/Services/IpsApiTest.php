<?php

namespace App\Tests\Integration\Services;

use App\Services\IpsApi;
use App\Tests\IlluminateTestCase;

class IpsApiTest extends IlluminateTestCase
{
    /**
     * @var \App\Services\IpsApi
     */
    private $ipsApi;

    public function setUp(): void
    {
        parent::setUp();
        $this->ipsApi = new IpsApi();
    }

    /**
     * @throws \Exception
     */
    public function testGetCalendarEvents(): void
    {
        $result = $this->ipsApi->getCalendarEvents();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }
}
