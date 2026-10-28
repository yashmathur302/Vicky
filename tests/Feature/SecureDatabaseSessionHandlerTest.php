<?php

namespace Tests\Feature;

use App\Extensions\SecureDatabaseSessionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use ReflectionMethod;
use Tests\TestCase;

class SecureDatabaseSessionHandlerTest extends TestCase
{
    public function test_it_encrypts_request_metadata_before_it_is_written(): void
    {
        app()->instance('request', Request::create('https://example.test', 'GET', [], [], [], [
            'REMOTE_ADDR' => '203.0.113.7',
            'HTTP_USER_AGENT' => 'Secure-session-handler-test',
        ]));

        $handler = app('session')->getHandler();

        $this->assertInstanceOf(SecureDatabaseSessionHandler::class, $handler);

        $method = new ReflectionMethod($handler, 'getDefaultPayload');
        $payload = $method->invoke($handler, 'test-session-payload');

        $this->assertNotSame('203.0.113.7', $payload['ip_address']);
        $this->assertNotSame('Secure-session-handler-test', $payload['user_agent']);
        $this->assertSame('203.0.113.7', Crypt::decryptString($payload['ip_address']));
        $this->assertSame('Secure-session-handler-test', Crypt::decryptString($payload['user_agent']));
    }
}
