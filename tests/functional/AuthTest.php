<?php

use App\Security\Entity\RefreshToken;
use App\Tests\Helper\ApiTestCase;
use Gesdinet\JWTRefreshTokenBundle\Doctrine\RefreshTokenManager;

class AuthTest extends ApiTestCase
{
    const VALID_API_KEY = 'KEYyH9o18yK4PaBtzK7p9inagwR0PCoCfADbYHmHF5FId9kjwB';
    const VALID_API_SECRET = 'SECRETCB9UJnsGn5KpfhK1yHDR9rAsO1bQ3mRQwxJ7GJthhpkt';
    const VALID_REFRESH_TOKEN = 'VALIDREFRESHjalksjioqruioqureoiwfhjskldjfklsjfksldjflksdjfl';
    const EXPIRED_REFRESH_TOKEN = 'EXPIREDREFRESHjasjioqruioqureoiwfhjskldjfklsjfksldjflksdjfl';
    const TOKEN_ENDPOINT = '/api/v2/auth/token';
    const REFRESH_ENDPOINT = '/api/v2/auth/refresh';

    public function testAuthSuccess()
    {
        $credentials = [
            'api_key' => self::VALID_API_KEY,
            'api_secret' => self::VALID_API_SECRET,
        ];

        $response = $this->request('POST', self::TOKEN_ENDPOINT, $credentials);
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = \json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $responseData);

        $jwtPayload = $this->unpackJwt($responseData['access_token'])->payload;

        foreach (['iat', 'exp', 'roles', 'sub'] as $key) {
            $this->assertArrayHasKey($key, $jwtPayload);
        }

        $this->assertContains('GET_PRODUCT_OFFERING', $jwtPayload['roles']);
        $this->assertContains('GET_PRODUCT_OFFERINGS', $jwtPayload['roles']);
    }

    public function testAuthFailure()
    {
        $credentials = [
            'api_key' => 'api-key',
            'api_secret' => 'api-secret',
        ];

        $response = $this->request('POST', self::TOKEN_ENDPOINT, $credentials);
        $this->assertEquals(401, $response->getStatusCode());
        $responseData = \json_decode($response->getContent(), true);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('title', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('detail', $responseData);
        $this->assertArrayHasKey('instance', $responseData);
    }

    public function testRefreshTokenPersists()
    {
        $credentials = [
            'api_key' => self::VALID_API_KEY,
            'api_secret' => self::VALID_API_SECRET,
        ];

        $response = $this->request('POST', self::TOKEN_ENDPOINT, $credentials);
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = \json_decode($response->getContent(), true);

        $this->assertArrayHasKey('refresh_token', $responseData);
        $refreshTokenString = $responseData['refresh_token'];
        $this->assertTrue(strlen($refreshTokenString) > 20);
        $refreshTokenManager = self::$container->get('gesdinet.jwtrefreshtoken.refresh_token_manager');
        $this->assertInstanceOf(RefreshTokenManager::class, $refreshTokenManager);

        /** @var RefreshToken $refreshToken */
        $refreshToken = $refreshTokenManager->get($refreshTokenString);
        $this->assertInstanceOf(RefreshToken::class, $refreshToken, 'Ensure the refresh token is managed');

        // We can't actually use the token in a POST REFRESH operation here because it's present in a transaction
        // which isn't committed for another client to access, and instead is rolled back on test completion.
        // So we'll have to test REFRESH against a fixture token!
    }

    public function testUseValidRefreshToken()
    {
        $refreshTokenString = self::VALID_REFRESH_TOKEN;
        $refreshTokenManager = self::$container->get('gesdinet.jwtrefreshtoken.refresh_token_manager');
        /** @var RefreshToken $refreshToken */
        $refreshToken = $refreshTokenManager->get($refreshTokenString);
        $this->assertInstanceOf(RefreshToken::class, $refreshToken, 'Fixture token must be present');

        // use this refresh token
        $refreshResponse = $this->request('POST', self::REFRESH_ENDPOINT, ['refresh_token' => $refreshTokenString]);
        $this->assertEquals(200, $refreshResponse->getStatusCode()); // gets 401

        $data = \json_decode($refreshResponse->getContent(), true);
        $jwt = $this->unpackJwt($data['access_token'])->payload;

        $refreshTokenPermissions = $refreshToken->getPermissions();

        $this->assertTrue(
            count($refreshTokenPermissions) > 1,
            'Refresh token must have permissions as per its fixture'
        );

        $jwtRoles = $jwt['roles'];

        $this->assertTrue(is_array($jwtRoles));
        $this->assertTrue(count($jwtRoles) > 0);
        $this->assertEquals(
            count($refreshTokenPermissions),
            count($jwtRoles),
            'JWT token generated must have refresh token permissions, got ' . implode(',', $jwtRoles)
        );

        $this->assertContains('GET_PRODUCT_OFFERING', $jwtRoles);
        $this->assertContains('GET_PRODUCT_OFFERINGS', $jwtRoles);
        $this->assertNotContains('DELETE_GET_PRODUCT_OFFERING', $jwtRoles);
    }

    public function testUseInvalidRefreshToken()
    {
        $refreshTokenString = sha1(time()); // random rubbish
        $refreshTokenManager = self::$container->get('gesdinet.jwtrefreshtoken.refresh_token_manager');
        $refreshToken = $refreshTokenManager->get($refreshTokenString);
        $this->assertNull($refreshToken);

        $refreshResponse = $this->request('POST', self::REFRESH_ENDPOINT, ['refresh_token' => $refreshTokenString]);
        $this->assertEquals(401, $refreshResponse->getStatusCode()); // gets 401
    }

    public function testUseExpiredRefreshToken()
    {
        $refreshTokenString = self::EXPIRED_REFRESH_TOKEN;
        $refreshTokenManager = self::$container->get('gesdinet.jwtrefreshtoken.refresh_token_manager');
        $refreshToken = $refreshTokenManager->get($refreshTokenString);
        $this->assertInstanceOf(RefreshToken::class, $refreshToken, 'Fixture token must be present');

        $refreshResponse = $this->request('POST', self::REFRESH_ENDPOINT, ['refresh_token' => $refreshTokenString]);
        $this->assertEquals(401, $refreshResponse->getStatusCode()); // gets 401
    }

    protected function unpackJwt(string $jwt): stdClass
    {
        list($header, $payload, $signature) = explode('.', $jwt);
        return (object)[
            'header' => \json_decode(base64_decode($header), true),
            'payload' => \json_decode(base64_decode($payload), true),
            'signature' => base64_decode($signature),
        ];
    }
}
