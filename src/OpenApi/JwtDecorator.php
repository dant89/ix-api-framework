<?php

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\OpenApi;
use ApiPlatform\Core\OpenApi\Model;

final class JwtDecorator implements OpenApiFactoryInterface
{
    private OpenApiFactoryInterface $decorated;
    protected array $tags = ['Authentication'];

    public function __construct(OpenApiFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $schemas = $openApi->getComponents()->getSchemas();

        $authPathItem = $this->addToken($schemas);
        $refreshPathItem = $this->addRefresh($schemas);

        $openApi->getPaths()->addPath('/api/v2/auth/token', $authPathItem);
        $openApi->getPaths()->addPath('/api/v2/auth/refresh', $refreshPathItem);
        return $openApi;
    }

    protected function addToken(\ArrayObject $schemas): Model\PathItem
    {
        $schemas['AuthRequest'] = [
            'type' => 'object',
            'description' => 'An authentication request.',
            'properties' => [
                'api_key' => [
                    'description' => 'Your API key.',
                    'type' => 'string',
                    'required' => true,
                ],
                'api_secret' => [
                    'description' => 'Your API secret.',
                    'type' => 'string',
                    'required' => true,
                ],
            ],
        ];
        $schemas['AuthRequestSuccess'] = [
            'type' => 'object',
            'description' => 'A successful authentication response.',
            'properties' => [
                'access_token' => [
                    'description' => 'Your JWT token.',
                    'type' => 'string',
                ],
                'refresh_token' => [
                    'description' => 'Your refresh token.',
                    'type' => 'string',
                ],
            ],
        ];

        $pathItem = new Model\PathItem(
            'JWT Token',
            'Generates a new JWT.',
            null,
            null,
            null,
            new Model\Operation(
                'postAuth',
                $this->tags,
                [
                    '200' => [
                        'description' => 'Successfully authenticated',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/AuthRequestSuccess',
                                ]
                            ]
                        ],
                    ],
                    '401' => [
                        'description' => 'Authentication failed',
                    ],
                ],
                'Generates a new JWT.',
                'Get JWT token to login.',
                null,
                [],
                new Model\RequestBody(
                    'Generate new JWT Token',
                    new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/AuthRequest',
                            ],
                        ],
                    ])
                )
            )
        );

        return $pathItem;
    }

    protected function addRefresh(\ArrayObject $schemas): Model\PathItem
    {
        $schemas['AuthRefreshRequest'] = [
            'type' => 'object',
            'description' => 'A token refresh request.',
            'properties' => [
                'refresh_token' => [
                    'description' => 'Your refresh token.',
                    'type' => 'string',
                    'required' => true,
                ],
            ],
        ];

        $pathItem = new Model\PathItem(
            'JWT Token Refresh',
            'Refresh your JWT.',
            null,
            null,
            null,
            new Model\Operation(
                'postAuthRefresh',
                $this->tags,
                [
                    '200' => [
                        'description' => 'Successfully authenticated',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/AuthRequest',
                                ]
                            ]
                        ],
                    ],
                    '401' => [
                        'description' => 'Authentication failed',
                    ],
                ],
                'Refresh your JWT.',
                'Refresh your JWT token to login.',
                null,
                [],
                new Model\RequestBody(
                    'Refresh your JWT.',
                    new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/AuthRefreshRequest',
                            ],
                        ],
                    ])
                )
            )
        );

        return $pathItem;
    }
}
