<?php

namespace Tests\Pages\Intranet;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\LoginFirstUser;
use Tests\TestCase;

class BaseTestName extends TestCase
{
    use DatabaseTransactions, LoginFirstUser, WithoutMiddleware;

    public const ROUTE = 'ProjectRoute';

    public function test_success_response()
    {
        $this->get(self::ROUTE)->assertSuccessful();
    }
}
