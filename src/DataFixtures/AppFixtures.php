<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\ProductAttribute;
use App\Entity\ProductFeature;
use App\Entity\ProductImage;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Entity\PaymentMethod;
use App\Entity\ShippingMethod;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [];

        $categories['tecnologia'] = $this->createCategory($manager, 'Tecnologia', 'tecnologia', null, 1, 0);
        $categories['notebooks'] = $this->createCategory($manager, 'Notebooks', 'notebooks', $categories['tecnologia'], 10, 1);
        $categories['auriculares'] = $this->createCategory($manager, 'Auriculares', 'auriculares', $categories['tecnologia'], 20, 1);
        $categories['smartwatches'] = $this->createCategory($manager, 'Smartwatches', 'smartwatches', $categories['tecnologia'], 30, 1);

        $categories['hogar'] = $this->createCategory($manager, 'Hogar', 'hogar', null, 2, 0);
        $categories['iluminacion'] = $this->createCategory($manager, 'Iluminacion', 'iluminacion', $categories['hogar'], 10, 1);
        $categories['organizacion'] = $this->createCategory($manager, 'Organizacion', 'organizacion', $categories['hogar'], 20, 1);

        $categories['indumentaria'] = $this->createCategory($manager, 'Indumentaria', 'indumentaria', null, 3, 0);
        $categories['abrigos'] = $this->createCategory($manager, 'Abrigos', 'abrigos', $categories['indumentaria'], 10, 1);
        $categories['calzado'] = $this->createCategory($manager, 'Calzado', 'calzado', $categories['indumentaria'], 20, 1);

        $categories['accesorios'] = $this->createCategory($manager, 'Accesorios', 'accesorios', null, 4, 0);
        $categories['mochilas'] = $this->createCategory($manager, 'Mochilas', 'mochilas', $categories['accesorios'], 10, 1);
        $categories['cables'] = $this->createCategory($manager, 'Cables', 'cables', $categories['accesorios'], 20, 1);

        $products = [
            ['Campera Softshell', 'campera-softshell', 'Campera liviana con abrigo y resistencia al viento.', 'Ideal para uso urbano y outdoor.', 112500, 137500, 18, $categories['abrigos'], 'https://placehold.co/600x600?text=Campera+Softshell', 12, true],
            ['Zapatillas Flow', 'zapatillas-flow', 'Zapatillas versatiles para uso diario.', 'Comodidad y estilo para todos los dias.', 89900, 109900, 18, $categories['calzado'], 'https://placehold.co/600x600?text=Zapatillas+Flow', 20, false],
            ['Auriculares Pulse', 'auriculares-pulse', 'Auriculares in-ear con sonido equilibrado.', 'Conexion estable y estuche compacto.', 45900, null, null, $categories['auriculares'], 'https://placehold.co/600x600?text=Auriculares+Pulse', 35, true],
            ['Notebook Nova 14', 'notebook-nova-14', 'Notebook liviana de 14 pulgadas para trabajo y estudio.', 'Rendimiento solido en un formato compacto.', 749900, 829900, 10, $categories['notebooks'], 'https://placehold.co/600x600?text=Notebook+Nova+14', 5, true],
            ['Mochila Urbana', 'mochila-urbana', 'Mochila minimalista con compartimento para notebook.', 'Uso diario y organizacion simple.', 52900, null, null, $categories['mochilas'], 'https://placehold.co/600x600?text=Mochila+Urbana', 18, false],
            ['Smartwatch Fit', 'smartwatch-fit', 'Reloj inteligente con monitoreo basico de actividad.', 'Pantalla tactil y notificaciones.', 68900, 79900, 14, $categories['smartwatches'], 'https://placehold.co/600x600?text=Smartwatch+Fit', 22, true],
            ['Lampara Nordica', 'lampara-nordica', 'Lampara decorativa de linea simple y moderna.', 'Luz suave para ambientes interiores.', 37900, null, null, $categories['iluminacion'], 'https://placehold.co/600x600?text=Lampara+Nordica', 14, false],
            ['Organizador Modular', 'organizador-modular', 'Organizador apilable para escritorio o cocina.', 'Modular y facil de adaptar.', 28900, null, null, $categories['organizacion'], 'https://placehold.co/600x600?text=Organizador+Modular', 30, false],
            ['Cable USB-C Pro', 'cable-usb-c-pro', 'Cable reforzado de carga y datos USB-C.', 'Compatible con multiples dispositivos.', 15900, null, null, $categories['cables'], 'https://placehold.co/600x600?text=Cable+USB-C+Pro', 80, false],
            ['Botella Termica', 'botella-termica', 'Botella termica con cierre hermetico.', 'Conserva temperatura por mas tiempo.', 24900, 29900, 17, $categories['accesorios'], 'https://placehold.co/600x600?text=Botella+Termica', 42, false],
            ['Gorra Classic', 'gorra-classic', 'Gorra clasica con ajuste regulable.', 'Simple, ligera y combinable.', 19900, null, null, $categories['accesorios'], 'https://placehold.co/600x600?text=Gorra+Classic', 55, false],
            ['Set de Cocina Terra', 'set-de-cocina-terra', 'Set de utensilios esenciales para cocina diaria.', 'Un kit practico para el hogar.', 55900, 64900, 14, $categories['hogar'], 'https://placehold.co/600x600?text=Set+de+Cocina+Terra', 16, true],
        ];

        $createdProducts = [];

        foreach ($products as [$name, $slug, $description, $shortDescription, $basePrice, $originalPrice, $discountPercentage, $category, $image, $stock, $featured]) {
            $product = new Product();
            $product->setName($name);
            $product->setSlug($slug);
            $product->setDescription($description);
            $product->setShortDescription($shortDescription);
            $product->setBasePrice($basePrice);
            $product->setOriginalPrice($originalPrice);
            $product->setDiscountPercentage($discountPercentage);
            $product->setCategory($category);
            $product->setImage($image);
            $product->setStock($stock);
            $product->setFeatured($featured);
            $product->setEnabled(true);
            $product->setTaxPercentage(21);
            $product->setTaxLabel('IVA');
            $product->setTaxIncluded(false);
            $manager->persist($product);
            $createdProducts[$slug] = $product;
        }

        $this->addCatalogData($manager, $createdProducts);
        $this->addShippingMethods($manager);
        $this->addPaymentMethods($manager);

        $manager->flush();
    }

    private function createCategory(ObjectManager $manager, string $name, string $slug, ?Category $parent, int $sortOrder, int $level): Category
    {
        $category = new Category();
        $category->setName($name);
        $category->setSlug($slug);
        $category->setDescription($name);
        $category->setParent($parent);
        $category->setSortOrder($sortOrder);
        $category->setLevel($level);
        $category->setEnabled(true);
        $manager->persist($category);

        return $category;
    }

    /**
     * @param array<string, Product> $products
     */
    private function addCatalogData(ObjectManager $manager, array $products): void
    {
        $this->configureCamperaSoftshell($manager, $products['campera-softshell']);
        $this->configureZapatillasFlow($manager, $products['zapatillas-flow']);
        $this->configureAuricularesPulse($manager, $products['auriculares-pulse']);
        $this->configureNotebookNova($manager, $products['notebook-nova-14']);
        $this->configureSmartwatchFit($manager, $products['smartwatch-fit']);
        $this->configureMochilaUrbana($manager, $products['mochila-urbana']);
    }

    private function configureCamperaSoftshell(ObjectManager $manager, Product $product): void
    {
        $this->addFeature($manager, $product, 'Tela', 'Softshell respirable', 1);
        $this->addFeature($manager, $product, 'Cierre', 'Frontal reforzado', 2);
        $this->addFeature($manager, $product, 'Bolsillos', 'Laterales con cierre', 3);

        $this->addAttribute($manager, $product, 'color', 'Negro', true, 'Color', 1);
        $this->addAttribute($manager, $product, 'talle', 'L', true, 'Talle', 2);
        $this->addAttribute($manager, $product, 'material', 'Poliester', false, 'Material', 3);

        $variantNegroM = $this->addVariant($manager, $product, 'CSH-NG-M', ['color' => 'Negro', 'talle' => 'M'], 8, 0, 'https://placehold.co/600x600?text=Campera+Negro+M');
        $variantNegroL = $this->addVariant($manager, $product, 'CSH-NG-L', ['color' => 'Negro', 'talle' => 'L'], 4, 1500, 'https://placehold.co/600x600?text=Campera+Negro+L');
        $this->addImage($manager, $product, 'https://placehold.co/600x600?text=Campera+Softshell+Main', 'Campera Softshell', 1, true, null);
        $this->addImage($manager, $product, 'https://placehold.co/600x600?text=Campera+Detalle', 'Detalle de Campera Softshell', 2, false, null);
        $this->addImage($manager, $product, 'https://placehold.co/600x600?text=Campera+M', 'Campera Softshell Negro M', 3, false, $variantNegroM);
        $this->addImage($manager, $product, 'https://placehold.co/600x600?text=Campera+L', 'Campera Softshell Negro L', 4, false, $variantNegroL);
    }

    private function configureZapatillasFlow(ObjectManager $manager, Product $product): void
    {
        $this->addFeature($manager, $product, 'Suela', 'Antideslizante', 1);
        $this->addFeature($manager, $product, 'Plantilla', 'Espuma de alta densidad', 2);

        $this->addAttribute($manager, $product, 'color', 'Blanco', true, 'Color', 1);
        $this->addAttribute($manager, $product, 'talle', '42', true, 'Talle', 2);
        $this->addAttribute($manager, $product, 'uso', 'Diario', false, 'Uso', 3);

        $variant42 = $this->addVariant($manager, $product, 'ZFL-WH-42', ['color' => 'Blanco', 'talle' => '42'], 6, 0, 'https://placehold.co/600x600?text=Zapatillas+42');
        $variant43 = $this->addVariant($manager, $product, 'ZFL-WH-43', ['color' => 'Blanco', 'talle' => '43'], 5, 0, 'https://placehold.co/600x600?text=Zapatillas+43');
        $this->addImage($manager, $product, 'https://placehold.co/600x600?text=Zapatillas+Flow+Main', 'Zapatillas Flow', 1, true, null);
        $this->addImage($manager, $product, 'https://placehold.co/600x600?text=Zapatillas+Detalle', 'Detalle Zapatillas Flow', 2, false, null);
        $this->addImage($manager, $product, 'https://placehold.co/600x600?text=Zapatillas+42', 'Zapatillas Flow talle 42', 3, false, $variant42);
        $this->addImage($manager, $product, 'https://placehold.co/600x600?text=Zapatillas+43', 'Zapatillas Flow talle 43', 4, false, $variant43);
    }

    private function configureAuricularesPulse(ObjectManager $manager, Product $product): void
    {
        $this->addFeature($manager, $product, 'Sonido', 'Balanceado con graves definidos', 1);
        $this->addFeature($manager, $product, 'Conexion', 'Bluetooth estable', 2);

        $this->addAttribute($manager, $product, 'color', 'Negro', true, 'Color', 1);
        $this->addAttribute($manager, $product, 'autonomia', '20 hs', false, 'Autonomia', 2);

        $this->addVariant($manager, $product, 'AUP-BK-STD', ['color' => 'Negro'], 35, 0, 'https://placehold.co/600x600?text=Auriculares+Pulse');
        $this->addImage($manager, $product, 'https://placehold.co/600x600?text=Auriculares+Pulse+Main', 'Auriculares Pulse', 1, true, null);
        $this->addImage($manager, $product, 'https://placehold.co/600x600?text=Auriculares+Pulse+Case', 'Auriculares Pulse con estuche', 2, false, null);
    }

    private function configureNotebookNova(ObjectManager $manager, Product $product): void
    {
        $this->addFeature($manager, $product, 'Procesador', 'Intel Core i5', 1);
        $this->addFeature($manager, $product, 'Pantalla', '14 pulgadas Full HD', 2);
        $this->addFeature($manager, $product, 'Memoria', '16 GB RAM', 3);

        $this->addAttribute($manager, $product, 'almacenamiento', '512 GB', true, 'Capacidad', 1);
        $this->addAttribute($manager, $product, 'memoria', '16 GB', true, 'Memoria', 2);
        $this->addAttribute($manager, $product, 'color', 'Gris', false, 'Color', 3);

        $variant512 = $this->addVariant($manager, $product, 'NOVA14-512', ['almacenamiento' => '512 GB'], 3, 0, 'https://placehold.co/600x600?text=Nova+14+512');
        $variant1tb = $this->addVariant($manager, $product, 'NOVA14-1TB', ['almacenamiento' => '1 TB'], 2, 60000, 'https://placehold.co/600x600?text=Nova+14+1TB');
        $this->addImage($manager, $product, 'https://placehold.co/600x600?text=Notebook+Nova+14+Main', 'Notebook Nova 14', 1, true, null);
        $this->addImage($manager, $product, 'https://placehold.co/600x600?text=Notebook+Nova+14+Side', 'Notebook Nova 14 lateral', 2, false, null);
        $this->addImage($manager, $product, 'https://placehold.co/600x600?text=Nova+14+512', 'Notebook Nova 14 512 GB', 3, false, $variant512);
        $this->addImage($manager, $product, 'https://placehold.co/600x600?text=Nova+14+1TB', 'Notebook Nova 14 1 TB', 4, false, $variant1tb);
    }

    private function configureSmartwatchFit(ObjectManager $manager, Product $product): void
    {
        $this->addFeature($manager, $product, 'Pantalla', 'AMOLED 1.5"', 1);
        $this->addFeature($manager, $product, 'Sensores', 'Frecuencia cardiaca y pasos', 2);

        $this->addAttribute($manager, $product, 'color', 'Negro', true, 'Color', 1);
        $this->addAttribute($manager, $product, 'correa', 'Silicona', false, 'Correa', 2);

        $this->addVariant($manager, $product, 'SWF-BK', ['color' => 'Negro'], 22, 0, 'https://placehold.co/600x600?text=Smartwatch+Negro');
        $this->addImage($manager, $product, 'https://placehold.co/600x600?text=Smartwatch+Fit+Main', 'Smartwatch Fit', 1, true, null);
    }

    private function configureMochilaUrbana(ObjectManager $manager, Product $product): void
    {
        $this->addFeature($manager, $product, 'Compartimento', 'Notebook hasta 15"', 1);
        $this->addFeature($manager, $product, 'Bolsillos', 'Frontal y laterales', 2);

        $this->addAttribute($manager, $product, 'color', 'Grafito', true, 'Color', 1);
        $this->addAttribute($manager, $product, 'capacidad', '22 L', true, 'Capacidad', 2);

        $this->addVariant($manager, $product, 'MUR-GR-22', ['color' => 'Grafito'], 18, 0, 'https://placehold.co/600x600?text=Mochila+Grafito');
        $this->addImage($manager, $product, 'https://placehold.co/600x600?text=Mochila+Urbana+Main', 'Mochila Urbana', 1, true, null);
    }

    private function addFeature(ObjectManager $manager, Product $product, string $name, ?string $value, int $position): void
    {
        $feature = new ProductFeature();
        $feature->setProduct($product);
        $feature->setName($name);
        $feature->setValue($value);
        $feature->setPosition($position);
        $manager->persist($feature);
    }

    private function addAttribute(ObjectManager $manager, Product $product, string $name, string $value, bool $filterable, ?string $facetGroup, int $sortOrder): void
    {
        $attribute = new ProductAttribute();
        $attribute->setProduct($product);
        $attribute->setName($name);
        $attribute->setValue($value);
        $attribute->setFilterable($filterable);
        $attribute->setFacetGroup($facetGroup);
        $attribute->setSortOrder($sortOrder);
        $manager->persist($attribute);
    }

    private function addVariant(ObjectManager $manager, Product $product, string $sku, array $options, int $stock, int $priceDelta, ?string $image): ProductVariant
    {
        $variant = new ProductVariant();
        $variant->setProduct($product);
        $variant->setSku($sku);
        $variant->setOptions($options);
        $variant->setStock($stock);
        $variant->setPriceDelta($priceDelta);
        $variant->setImage($image);
        $variant->setEnabled(true);
        $manager->persist($variant);

        return $variant;
    }

    private function addImage(ObjectManager $manager, Product $product, string $url, ?string $alt, int $position, bool $main, ?ProductVariant $variant): void
    {
        $image = new ProductImage();
        $image->setProduct($product);
        $image->setUrl($url);
        $image->setAlt($alt);
        $image->setPosition($position);
        $image->setMain($main);
        $image->setVariant($variant);
        $manager->persist($image);
    }

    private function addShippingMethods(ObjectManager $manager): void
    {
        $methods = [
            ['standard', 'Envio estandar', 'Envio economico para entregas regulares.', 0, '3 a 5 dias habiles', 1],
            ['express', 'Envio express', 'Entrega prioritaria con mayor velocidad.', 4500, '24 a 48 hs', 2],
            ['pickup', 'Retiro en punto de entrega', 'Retiro sin costo en punto habilitado.', 0, 'Disponible en 24 hs', 3],
        ];

        foreach ($methods as [$code, $label, $description, $cost, $eta, $sortOrder]) {
            $method = new ShippingMethod();
            $method->setCode($code);
            $method->setLabel($label);
            $method->setDescription($description);
            $method->setCost($cost);
            $method->setEta($eta);
            $method->setEnabled(true);
            $method->setSortOrder($sortOrder);
            $manager->persist($method);
        }
    }

    private function addPaymentMethods(ObjectManager $manager): void
    {
        $methods = [
            ['credit_card', 'Tarjeta de credito', 'Pago con tarjeta de credito.', 'card', true, 1],
            ['debit_card', 'Tarjeta de debito', 'Pago con tarjeta de debito.', 'card', true, 2],
            ['bank_transfer', 'Transferencia bancaria', 'Transferencia bancaria manual.', 'bank_transfer', false, 3],
            ['cash_on_delivery', 'Pago contra entrega', 'Pago al recibir el pedido.', 'cash', false, 4],
        ];

        foreach ($methods as [$code, $label, $description, $type, $requiresCardData, $sortOrder]) {
            $method = new PaymentMethod();
            $method->setCode($code);
            $method->setLabel($label);
            $method->setDescription($description);
            $method->setType($type);
            $method->setRequiresCardData($requiresCardData);
            $method->setEnabled(true);
            $method->setSortOrder($sortOrder);
            $manager->persist($method);
        }
    }
}
