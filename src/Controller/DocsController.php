<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DocsController extends AbstractController
{
    #[Route('/api/docs', methods: ['GET'])]
    public function index(): Response
    {
        $html = <<<'HTML'
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NexoShop API Docs</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: #0f172a;
        }

        #swagger-ui {
            background: #fff;
            min-height: 100vh;
        }
    </style>
</head>
<body>
<div id="swagger-ui"></div>
<script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
<script>
    window.addEventListener('load', function () {
        window.ui = SwaggerUIBundle({
            url: '/api/docs.json',
            dom_id: '#swagger-ui',
            deepLinking: true,
            displayRequestDuration: true,
            presets: [
                SwaggerUIBundle.presets.apis
            ],
            layout: 'BaseLayout'
        });
    });
</script>
</body>
</html>
HTML;

        return new Response($html);
    }

    #[Route('/api/docs.json', methods: ['GET'])]
    public function spec(): JsonResponse
    {
        return new JsonResponse($this->buildSpec());
    }

    private function buildSpec(): array
    {
        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'NexoShop API',
                'version' => '1.0.0',
                'description' => 'API REST de catalogo, busqueda, checkout y pedidos para NexoShop Angular.',
            ],
            'servers' => [
                [
                    'url' => 'http://localhost:8000',
                    'description' => 'Servidor local',
                ],
            ],
            'tags' => [
                ['name' => 'Categories'],
                ['name' => 'Products'],
                ['name' => 'Catalog'],
                ['name' => 'Search'],
                ['name' => 'Shipping'],
                ['name' => 'Payments'],
                ['name' => 'Orders'],
                ['name' => 'Health'],
                ['name' => 'Docs'],
            ],
            'paths' => [
                '/api/categories' => $this->pathGet(
                    'Categories',
                    'Lista las categorias activas ordenadas.',
                    'CategoryListResponse'
                ),
                '/api/categories/tree' => $this->pathGet(
                    'Categories',
                    'Devuelve el arbol completo de categorias.',
                    'CategoryTreeResponse'
                ),
                '/api/categories/{id}' => $this->pathGetWithId(
                    'Categories',
                    'Devuelve una categoria por id.',
                    'CategoryDetailResponse'
                ),
                '/api/categories/slug/{slug}' => $this->pathGetWithSlug(
                    'Categories',
                    'Devuelve una categoria por slug.',
                    'CategoryDetailResponse'
                ),
                '/api/products' => $this->pathGet(
                    'Products',
                    'Lista productos con filtros de busqueda, categoria y atributos.',
                    'ProductPaginatedResponse',
                    $this->productListParameters()
                ),
                '/api/products/{id}' => $this->pathGetWithId(
                    'Products',
                    'Devuelve el detalle completo de un producto.',
                    'ProductDetailResponse'
                ),
                '/api/products/slug/{slug}' => $this->pathGetWithSlug(
                    'Products',
                    'Devuelve el detalle completo de un producto por slug.',
                    'ProductDetailResponse'
                ),
                '/api/catalog/facets' => $this->pathGet(
                    'Catalog',
                    'Calcula facetas disponibles para el contexto actual.',
                    'CatalogFacetsResponse',
                    [
                        [
                            'name' => 'category',
                            'in' => 'query',
                            'required' => false,
                            'schema' => ['type' => 'string'],
                        ],
                        [
                            'name' => 'subcategory',
                            'in' => 'query',
                            'required' => false,
                            'schema' => ['type' => 'string'],
                        ],
                    ]
                ),
                '/api/search/suggestions' => $this->pathGet(
                    'Search',
                    'Sugiere productos y atributos a partir del termino buscado.',
                    'SearchSuggestionsResponse',
                    [
                        [
                            'name' => 'q',
                            'in' => 'query',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                        ],
                    ]
                ),
                '/api/shipping-methods' => $this->pathGet(
                    'Shipping',
                    'Lista los metodos de envio habilitados.',
                    'ShippingMethodListResponse'
                ),
                '/api/payment-methods' => $this->pathGet(
                    'Payments',
                    'Lista los metodos de pago habilitados.',
                    'PaymentMethodListResponse'
                ),
                '/api/orders' => [
                    'get' => $this->operation(
                        'Orders',
                        'Lista los pedidos recientes.',
                        'OrderListResponse'
                    ),
                    'post' => [
                        'tags' => ['Orders'],
                        'summary' => 'Crear pedido',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/OrderCreateRequest',
                                    ],
                                    'examples' => [
                                        'default' => [
                                            'summary' => 'Pedido de ejemplo',
                                            'value' => [
                                                'customer' => [
                                                    'name' => 'Juan Perez',
                                                    'email' => 'juan@example.com',
                                                    'phone' => '1112345678',
                                                ],
                                                'shippingAddress' => [
                                                    'street' => 'Av. Siempre Viva 123',
                                                    'city' => 'Buenos Aires',
                                                    'province' => 'Buenos Aires',
                                                    'postcode' => '1000',
                                                    'reference' => 'Piso 2',
                                                ],
                                                'shippingMethod' => 'standard',
                                                'paymentMethod' => 'bank_transfer',
                                                'items' => [
                                                    [
                                                        'productId' => 1,
                                                        'quantity' => 1,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'Pedido creado',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/OrderDetail',
                                        ],
                                    ],
                                ],
                            ],
                            '400' => $this->errorResponse('Datos invalidos'),
                            '422' => $this->validationErrorResponse(),
                        ],
                    ],
                ],
                '/api/orders/{id}' => $this->pathGetWithId(
                    'Orders',
                    'Devuelve el detalle de un pedido.',
                    'OrderDetail'
                ),
                '/api/health' => $this->pathGet(
                    'Health',
                    'Verifica que la API este levantada.',
                    'HealthResponse'
                ),
                '/api/docs' => $this->pathGet(
                    'Docs',
                    'Muestra Swagger UI para explorar la API.',
                    'HtmlResponse'
                ),
                '/api/docs.json' => $this->pathGet(
                    'Docs',
                    'Devuelve el documento OpenAPI en JSON.',
                    'OpenApiDocument'
                ),
            ],
            'components' => [
                'schemas' => [
                    'CategoryListResponse' => $this->listResponseSchema('CategorySummary'),
                    'CategoryTreeResponse' => $this->listResponseSchema('CategoryTreeNode'),
                    'CategoryDetailResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'data' => ['$ref' => '#/components/schemas/CategoryDetailData'],
                        ],
                    ],
                    'CategoryDetailData' => [
                        'allOf' => [
                            ['$ref' => '#/components/schemas/CategorySummary'],
                            [
                                'type' => 'object',
                                'properties' => [
                                    'parent' => ['nullable' => true, 'type' => 'object'],
                                    'breadcrumbs' => [
                                        'type' => 'array',
                                        'items' => ['$ref' => '#/components/schemas/CategorySummary'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'CategorySummary' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'name' => ['type' => 'string'],
                            'slug' => ['type' => 'string'],
                            'description' => ['type' => 'string', 'nullable' => true],
                            'sortOrder' => ['type' => 'integer'],
                            'level' => ['type' => 'integer'],
                            'enabled' => ['type' => 'boolean'],
                        ],
                    ],
                    'CategoryTreeNode' => [
                        'allOf' => [
                            ['$ref' => '#/components/schemas/CategorySummary'],
                            [
                                'type' => 'object',
                                'properties' => [
                                    'children' => [
                                        'type' => 'array',
                                        'items' => ['$ref' => '#/components/schemas/CategoryTreeNode'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'ProductListResponse' => $this->listResponseSchema('ProductSummary'),
                    'ProductPaginatedResponse' => $this->paginatedListResponseSchema('ProductSummary'),
                    'ProductDetailResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'data' => ['$ref' => '#/components/schemas/ProductDetailData'],
                        ],
                    ],
                    'ProductDetailData' => [
                        'allOf' => [
                            ['$ref' => '#/components/schemas/ProductSummary'],
                            [
                                'type' => 'object',
                                'properties' => [
                                    'images' => [
                                        'type' => 'array',
                                        'items' => ['$ref' => '#/components/schemas/ProductImage'],
                                    ],
                                    'features' => [
                                        'type' => 'array',
                                        'items' => ['$ref' => '#/components/schemas/ProductFeature'],
                                    ],
                                    'attributes' => [
                                        'type' => 'array',
                                        'items' => ['$ref' => '#/components/schemas/ProductAttribute'],
                                    ],
                                    'variants' => [
                                        'type' => 'array',
                                        'items' => ['$ref' => '#/components/schemas/ProductVariant'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'ProductSummary' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'name' => ['type' => 'string'],
                            'slug' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'shortDescription' => ['type' => 'string'],
                            'category' => [
                                'nullable' => true,
                                'type' => 'object',
                                'properties' => [
                                    'id' => ['type' => 'integer'],
                                    'name' => ['type' => 'string'],
                                    'slug' => ['type' => 'string'],
                                ],
                            ],
                            'basePrice' => ['type' => 'integer'],
                            'originalPrice' => ['type' => 'integer', 'nullable' => true],
                            'discountPercentage' => ['type' => 'integer', 'nullable' => true],
                            'discountAmount' => ['type' => 'integer'],
                            'taxPercentage' => ['type' => 'integer'],
                            'taxLabel' => ['type' => 'string'],
                            'taxIncluded' => ['type' => 'boolean'],
                            'image' => ['type' => 'string', 'nullable' => true],
                            'enabled' => ['type' => 'boolean'],
                            'featured' => ['type' => 'boolean'],
                            'stock' => ['type' => 'integer'],
                            'createdAt' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                            'updatedAt' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                        ],
                    ],
                    'ProductImage' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'url' => ['type' => 'string'],
                            'alt' => ['type' => 'string', 'nullable' => true],
                            'position' => ['type' => 'integer'],
                            'main' => ['type' => 'boolean'],
                            'variantId' => ['type' => 'integer', 'nullable' => true],
                            'variantSku' => ['type' => 'string', 'nullable' => true],
                        ],
                    ],
                    'ProductFeature' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'name' => ['type' => 'string'],
                            'value' => ['type' => 'string', 'nullable' => true],
                            'position' => ['type' => 'integer'],
                        ],
                    ],
                    'ProductAttribute' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'name' => ['type' => 'string'],
                            'value' => ['type' => 'string'],
                            'filterable' => ['type' => 'boolean'],
                            'facetGroup' => ['type' => 'string', 'nullable' => true],
                            'sortOrder' => ['type' => 'integer'],
                        ],
                    ],
                    'ProductVariant' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'sku' => ['type' => 'string'],
                            'options' => ['type' => 'object'],
                            'stock' => ['type' => 'integer'],
                            'priceDelta' => ['type' => 'integer'],
                            'image' => ['type' => 'string', 'nullable' => true],
                            'enabled' => ['type' => 'boolean'],
                            'label' => ['type' => 'string'],
                        ],
                    ],
                    'CatalogFacetsResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'data' => ['$ref' => '#/components/schemas/CatalogFacetsData'],
                        ],
                    ],
                    'CatalogFacetsData' => [
                        'type' => 'object',
                        'properties' => [
                            'context' => [
                                'type' => 'object',
                                'properties' => [
                                    'category' => ['type' => 'string', 'nullable' => true],
                                    'subcategory' => ['type' => 'string', 'nullable' => true],
                                ],
                            ],
                            'price' => [
                                'type' => 'object',
                                'properties' => [
                                    'min' => ['type' => 'integer', 'nullable' => true],
                                    'max' => ['type' => 'integer', 'nullable' => true],
                                ],
                            ],
                            'attributes' => [
                                'type' => 'array',
                                'items' => ['type' => 'object'],
                            ],
                        ],
                    ],
                    'SearchSuggestionsResponse' => $this->listResponseSchema('SearchSuggestionItem'),
                    'SearchSuggestionItem' => [
                        'type' => 'object',
                        'properties' => [
                            'label' => ['type' => 'string'],
                            'type' => ['type' => 'string'],
                            'hint' => ['type' => 'string', 'nullable' => true],
                            'productId' => ['type' => 'integer', 'nullable' => true],
                            'categoryId' => ['type' => 'integer', 'nullable' => true],
                        ],
                    ],
                    'ShippingMethodListResponse' => $this->listResponseSchema('ShippingMethod'),
                    'PaymentMethodListResponse' => $this->listResponseSchema('PaymentMethod'),
                    'ShippingMethod' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'code' => ['type' => 'string'],
                            'label' => ['type' => 'string'],
                            'description' => ['type' => 'string', 'nullable' => true],
                            'cost' => ['type' => 'integer'],
                            'eta' => ['type' => 'string', 'nullable' => true],
                            'enabled' => ['type' => 'boolean'],
                            'sortOrder' => ['type' => 'integer'],
                        ],
                    ],
                    'PaymentMethod' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'code' => ['type' => 'string'],
                            'label' => ['type' => 'string'],
                            'description' => ['type' => 'string', 'nullable' => true],
                            'type' => ['type' => 'string'],
                            'requiresCardData' => ['type' => 'boolean'],
                            'enabled' => ['type' => 'boolean'],
                            'sortOrder' => ['type' => 'integer'],
                        ],
                    ],
                    'OrderListResponse' => $this->listResponseSchema('OrderSummary'),
                    'OrderSummary' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'orderNumber' => ['type' => 'string'],
                            'status' => ['type' => 'string'],
                            'customerName' => ['type' => 'string'],
                            'customerEmail' => ['type' => 'string'],
                            'subtotal' => ['type' => 'integer'],
                            'total' => ['type' => 'integer'],
                            'createdAt' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                        ],
                    ],
                    'OrderDetail' => [
                        'type' => 'object',
                        'properties' => [
                            'data' => ['$ref' => '#/components/schemas/OrderDetailData'],
                        ],
                    ],
                    'OrderDetailData' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'orderNumber' => ['type' => 'string'],
                            'status' => ['type' => 'string'],
                            'customer' => [
                                'type' => 'object',
                                'properties' => [
                                    'name' => ['type' => 'string'],
                                    'email' => ['type' => 'string'],
                                    'phone' => ['type' => 'string'],
                                ],
                            ],
                            'shippingAddress' => [
                                'type' => 'object',
                                'properties' => [
                                    'street' => ['type' => 'string'],
                                    'city' => ['type' => 'string'],
                                    'province' => ['type' => 'string'],
                                    'postcode' => ['type' => 'string'],
                                    'reference' => ['type' => 'string', 'nullable' => true],
                                ],
                            ],
                            'shippingMethod' => [
                                'type' => 'object',
                                'properties' => [
                                    'code' => ['type' => 'string'],
                                    'label' => ['type' => 'string'],
                                    'cost' => ['type' => 'integer'],
                                ],
                            ],
                            'paymentMethod' => [
                                'type' => 'object',
                                'properties' => [
                                    'code' => ['type' => 'string'],
                                    'label' => ['type' => 'string'],
                                ],
                            ],
                            'items' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id' => ['type' => 'integer'],
                                        'productName' => ['type' => 'string'],
                                        'variantDescription' => ['type' => 'string', 'nullable' => true],
                                        'quantity' => ['type' => 'integer'],
                                        'unitPrice' => ['type' => 'integer'],
                                        'originalUnitPrice' => ['type' => 'integer', 'nullable' => true],
                                        'discountPercentage' => ['type' => 'integer', 'nullable' => true],
                                        'taxPercentage' => ['type' => 'integer'],
                                        'discountTotal' => ['type' => 'integer'],
                                        'taxTotal' => ['type' => 'integer'],
                                        'subtotal' => ['type' => 'integer'],
                                    ],
                                ],
                            ],
                            'subtotal' => ['type' => 'integer'],
                            'discountTotal' => ['type' => 'integer'],
                            'taxTotal' => ['type' => 'integer'],
                            'shippingCost' => ['type' => 'integer'],
                            'total' => ['type' => 'integer'],
                            'createdAt' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                            'updatedAt' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                        ],
                    ],
                    'OrderCreateRequest' => [
                        'type' => 'object',
                        'required' => ['customer', 'shippingAddress', 'shippingMethod', 'paymentMethod', 'items'],
                        'properties' => [
                            'customer' => [
                                'type' => 'object',
                                'required' => ['name', 'email', 'phone'],
                                'properties' => [
                                    'name' => ['type' => 'string'],
                                    'email' => ['type' => 'string'],
                                    'phone' => ['type' => 'string'],
                                ],
                            ],
                            'shippingAddress' => [
                                'type' => 'object',
                                'required' => ['street', 'city', 'province', 'postcode'],
                                'properties' => [
                                    'street' => ['type' => 'string'],
                                    'city' => ['type' => 'string'],
                                    'province' => ['type' => 'string'],
                                    'postcode' => ['type' => 'string'],
                                    'reference' => ['type' => 'string', 'nullable' => true],
                                ],
                            ],
                            'shippingMethod' => ['type' => 'string'],
                            'paymentMethod' => ['type' => 'string'],
                            'paymentDetails' => [
                                'type' => 'object',
                                'nullable' => true,
                                'properties' => [
                                    'cardHolder' => ['type' => 'string'],
                                    'lastFourDigits' => ['type' => 'string'],
                                ],
                            ],
                            'items' => [
                                'type' => 'array',
                                'minItems' => 1,
                                'items' => [
                                    'type' => 'object',
                                    'required' => ['productId', 'quantity'],
                                    'properties' => [
                                        'productId' => ['type' => 'integer'],
                                        'variantId' => ['type' => 'integer', 'nullable' => true],
                                        'quantity' => ['type' => 'integer'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'ValidationErrorResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'errors' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'field' => ['type' => 'string'],
                                        'message' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'ErrorResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'error' => [
                                'type' => 'object',
                                'properties' => [
                                    'code' => ['type' => 'string'],
                                    'message' => ['type' => 'string'],
                                    'details' => ['type' => 'object'],
                                ],
                            ],
                        ],
                    ],
                    'HtmlResponse' => [
                        'type' => 'string',
                    ],
                    'HealthResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'properties' => [
                                    'status' => ['type' => 'string'],
                                    'service' => ['type' => 'string'],
                                    'timestamp' => ['type' => 'string', 'format' => 'date-time'],
                                ],
                            ],
                        ],
                    ],
                    'OpenApiDocument' => [
                        'type' => 'object',
                    ],
                ],
                'responses' => [
                    'ValidationError' => $this->validationErrorResponse(),
                    'GenericError' => $this->errorResponse('Error'),
                ],
            ],
        ];
    }

    private function pathGet(string $tag, string $summary, string $schemaName, array $parameters = []): array
    {
        return [
            'get' => $this->operation($tag, $summary, $schemaName, $parameters),
        ];
    }

    private function pathGetWithId(string $tag, string $summary, string $schemaName): array
    {
        return $this->pathGetWithParameter($tag, $summary, $schemaName, 'id', 'integer');
    }

    private function pathGetWithSlug(string $tag, string $summary, string $schemaName): array
    {
        return $this->pathGetWithParameter($tag, $summary, $schemaName, 'slug', 'string');
    }

    private function pathGetWithParameter(string $tag, string $summary, string $schemaName, string $parameterName, string $parameterType): array
    {
        return $this->pathGet(
            $tag,
            $summary,
            $schemaName,
            [
                [
                    'name' => $parameterName,
                    'in' => 'path',
                    'required' => true,
                    'schema' => ['type' => $parameterType],
                ],
            ]
        );
    }

    private function operation(string $tag, string $summary, string $schemaName, array $parameters = []): array
    {
        return [
            'tags' => [$tag],
            'summary' => $summary,
            'parameters' => $parameters,
            'responses' => [
                '200' => [
                    'description' => 'OK',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/'.$schemaName,
                            ],
                        ],
                    ],
                ],
                '404' => $this->errorResponse('No encontrado'),
            ],
        ];
    }

    private function productListParameters(): array
    {
        return [
            ['name' => 'search', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
            ['name' => 'category', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
            ['name' => 'subcategory', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
            ['name' => 'minPrice', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer']],
            ['name' => 'maxPrice', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer']],
            ['name' => 'featured', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'boolean']],
            ['name' => 'sort', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
            [
                'name' => 'attribute',
                'in' => 'query',
                'required' => false,
                'style' => 'deepObject',
                'explode' => true,
                'schema' => [
                    'type' => 'object',
                    'additionalProperties' => ['type' => 'string'],
                ],
            ],
        ];
    }

    private function listResponseSchema(string $itemSchema): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'data' => [
                    'type' => 'array',
                    'items' => [
                        '$ref' => '#/components/schemas/'.$itemSchema,
                    ],
                ],
                'meta' => [
                    'type' => 'object',
                    'properties' => [
                        'total' => ['type' => 'integer'],
                    ],
                ],
            ],
        ];
    }

    private function paginatedListResponseSchema(string $itemSchema): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'data' => [
                    'type' => 'array',
                    'items' => [
                        '$ref' => '#/components/schemas/'.$itemSchema,
                    ],
                ],
                'meta' => [
                    'type' => 'object',
                    'properties' => [
                        'page' => ['type' => 'integer'],
                        'limit' => ['type' => 'integer'],
                        'totalItems' => ['type' => 'integer'],
                        'totalPages' => ['type' => 'integer'],
                    ],
                ],
            ],
        ];
    }

    private function errorResponse(string $description): array
    {
        return [
            'description' => $description,
            'content' => [
                'application/json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ErrorResponse',
                    ],
                ],
            ],
        ];
    }

    private function validationErrorResponse(): array
    {
        return [
            'description' => 'Errores de validacion',
            'content' => [
                'application/json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ValidationErrorResponse',
                    ],
                ],
            ],
        ];
    }
}
