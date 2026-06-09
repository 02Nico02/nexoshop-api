# NexoShop API

Backend Symfony para el mini e-commerce NexoShop Angular.

## Estado actual

Implementado hasta Etapa 7:

- base Symfony
- Docker
- MySQL
- entidades `Category` y `Product`
- migracion inicial
- fixtures con categorias y 12 productos
- endpoints GET de categorias y productos con filtros basicos
- categorias jerarquicas con `level`
- endpoint `GET /api/categories/tree`
- breadcrumbs en el detalle de categoria
- variantes de producto
- imagenes de producto
- atributos filtrables
- caracteristicas de producto
- metodos de envio
- metodos de pago
- pedidos simulados

Todavia no estan implementados CORS para Angular ni la documentacion final.

## Tecnologias

- PHP 8.3
- Symfony 7.1
- Doctrine ORM
- Doctrine Migrations
- Doctrine Fixtures
- MySQL 8
- Docker

## Requisitos

- Docker
- Docker Compose

## Levantar el proyecto

```bash
docker compose up -d --build
```

## Variables de entorno

Copiar `.env.example` a `.env` si hace falta ajustar valores locales.

## Migraciones

```bash
docker compose exec app php bin/console doctrine:migrations:migrate
```

## Fixtures

```bash
docker compose exec app php bin/console doctrine:fixtures:load
```

## Endpoints disponibles

### Categorias

- `GET /api/categories`
- `GET /api/categories/tree`
- `GET /api/categories/{id}`
- `GET /api/categories/slug/{slug}`

### Productos

- `GET /api/products`
- `GET /api/products/{id}`
- `GET /api/products/slug/{slug}`

El detalle de producto devuelve tambien:

- `images`
- `features`
- `attributes`
- `variants`

El listado acepta filtros adicionales:

- `subcategory`
- `attribute[color]=Negro`

### Catalogo

- `GET /api/catalog/facets`
- `GET /api/catalog/facets?category=indumentaria`
- `GET /api/catalog/facets?category=indumentaria&subcategory=abrigos`

### Busqueda

- `GET /api/search/suggestions?q=cam`

### Envios

- `GET /api/shipping-methods`

### Pagos

- `GET /api/payment-methods`

### Pedidos

- `GET /api/orders`
- `GET /api/orders/{id}`
- `POST /api/orders`

## Filtros de productos

`GET /api/products` acepta estos query params:

- `search`
- `category`
- `minPrice`
- `maxPrice`
- `featured`
- `sort`

Ejemplo:

```txt
/api/products?search=campera&category=abrigos&minPrice=10000&maxPrice=200000&featured=true&sort=price_asc
```

## Estructura principal

- `src/Controller` rutas JSON
- `src/Entity` entidades Doctrine
- `src/Repository` consultas de catalogo
- `src/DataFixtures` datos iniciales
- `migrations` esquema inicial

## Docker

- `app`: PHP + Apache + Symfony
- `db`: MySQL 8

## Notas tecnicas

- Los precios se manejan como enteros para simplificar el catalogo inicial.
- `basePrice` representa el precio de venta actual.
- `originalPrice` se usa como precio tachado cuando existe descuento.
- `discountAmount` se deriva automaticamente si `originalPrice` y `basePrice` estan presentes.
- `level` indica la profundidad de la categoria en el arbol.
- `breadcrumbs` se devuelven en el detalle de categoria.
- los productos pueden tener variantes, imagenes, atributos y caracteristicas en la respuesta de detalle.
- `facets` se calculan desde los productos visibles en el contexto de categoria/subcategoria.

## Proximos pasos

- CORS para Angular
- documentacion final
