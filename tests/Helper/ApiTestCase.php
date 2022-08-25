<?php

namespace App\Tests\Helper;

use App\Kernel;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS;
use Namshi\JOSE\JWS;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiTestCase extends WebTestCase
{
    protected const VALID_API_COMPANY = '12345';
    protected bool $dumpFailedResponses = true;
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    protected function request(string $method, string $uri, $content = null, array $headers = []): Response
    {
        $server = ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json, application/json'];
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'content-type') {
                $server['CONTENT_TYPE'] = $value;
                continue;
            }

            $server['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
        }

        if (is_array($content) && false !== preg_match('#^application/(?:.+\+)?json$#', $server['CONTENT_TYPE'])) {
            $content = json_encode($content);
        }

        $this->client->request($method, $uri, [], [], $server, $content);

        return $this->client->getResponse();
    }

    protected static function getKernelClass()
    {
        return Kernel::class;
    }

    protected function getValidJwtForUserWithPermission(string $customerId, string $permission): string
    {
        $jwtHeader = [
            'typ' => 'JWT',
            'alg' => 'RS256'
        ];

        $jwtPayload = [
            'iat' => time(),
            'exp' => time() + 3600,
            'sub' => $customerId,
            'roles' => [
                $permission
            ]
        ];

        $kernelDir = self::$container->getParameter('kernel.project_dir');
        $jwtPassphrase = $_ENV['JWT_PASSPHRASE'];

        $jwtSecretKey = str_replace('%kernel.project_dir%', $kernelDir, $_ENV['JWT_SECRET_KEY']);

        $jws = new JWS($jwtHeader);

        $jws->setPayload($jwtPayload);
        $jws->sign(file_get_contents($jwtSecretKey), $jwtPassphrase);

        $jws = new CreatedJWS($jws->getTokenString(), $jws->isSigned());

        return $jws->getToken();
    }

    protected function dumpResponseIfCodeNotExpected(
        Response $response,
        int $expectedCode,
        ?string $message = null
    ): void {
        $gotCode = $response->getStatusCode();
        if ($this->dumpFailedResponses && ($gotCode !== $expectedCode)) {
            $stack = debug_backtrace(0, 2);
            $callingPointSourceLine = $stack[0]['line'];
            $callingPointSourceClass = $stack[1]['class'];
            $callingPointSourceMethod = $stack[1]['function'];
            try {
                $content = json_encode(\json_decode($response->getContent()), JSON_PRETTY_PRINT);
            } catch (\Exception $e) {
                $content = $response->getContent();
            }
            $this->fail(
                ($message ? "$message:\n" : '') .
                "Got response code $gotCode instead of $expectedCode at line $callingPointSourceLine" .
                " of $callingPointSourceClass::$callingPointSourceMethod\n" .
                "\nResponse:\n" . $content
            );
        }
    }
}
