<?php

namespace App\Tests\Integration\Services;

use App\Services\IpsApi;
use App\Tests\IlluminateTestCase;

class IpsApiTest extends IlluminateTestCase
{
    private IpsApi $ipsApi;

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
        static::assertIsArray($result);
        static::assertNotEmpty($result);
    }
}
