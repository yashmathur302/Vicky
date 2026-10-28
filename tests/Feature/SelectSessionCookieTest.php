<?php

namespace Tests\Feature;

use App\Http\Middleware\SelectSessionCookie;
use Illuminate\Http\Request;
use Tests\TestCase;

class SelectSessionCookieTest extends TestCase
{
    public function test_it_selects_an_independent_cookie_for_admin_requests(): void
    {
        $middleware = app(SelectSessionCookie::class);
        $originalCookie = config('session.cookie');

        try {
            $adminResponse = $middleware->handle(
                Request::create('/admin/login'),
                fn () => response(config('session.cookie')),
            );

            $webResponse = $middleware->handle(
                Request::create('/login'),
                fn () => response(config('session.cookie')),
            );

            $adminCookie = $adminResponse->getContent();
            $webCookie = $webResponse->getContent();

            $this->assertSame(config('session.admin_cookie'), $adminCookie);
            $this->assertSame(config('session.web_cookie'), $webCookie);
            $this->assertNotSame($adminCookie, $webCookie);
        } finally {
            config(['session.cookie' => $originalCookie]);
        }
    }
}
