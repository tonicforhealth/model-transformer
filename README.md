Model Transformer
=================

Library is simple abstraction for object transformations. 
   
Use cases: 
    
1. Separate domain model layer from view or presentation layer, but still keep objects.
2. Separate domain model from resource representations (in RESTful applications).
   
Installation
------------
   
### Require dependencies via composer: 

```
$ composer require tonicforhealth/model-transformer
```
 
Specifications, Documentation & Examples
----------------------------------------

### Usage

Suppose, there are two *domain objects*:
 
```php
<?php 

class Product 
{
    /**
     * @var string
     */ 
    private $name; 
    
    /**
     * @var Category
     */
    private $category;
    
    // ... 
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }
}

class Category 
{
    /**
     * @var string
     */ 
    private $name; 
    
    // ... 
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
```

And one *presentation object* which can be used in presentation layer: 

```php
<?php 

class ProductPresentation
{
    /**
     * @var string
     */ 
    private $name;
     
    /**
     * @var string
     */
    private $categoryName;
    
    /**
     * @var int
     */
    private $purchasedCount;
    
    // ... 
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @return string
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }
    
    /**
     * @return string
     */
    public function getPurchasedCount()
    {
        return $this->purchased;
    }    
}
```

There are lot of solutions for transforming `Product` and `Category` objects to `ProductRepresentation`: 

- just create `ProductRepresentation` based `Product` and `Category` on at the needed place;
- create factory for `ProductRepresentation`;
- and so on. 

This library provides simple and concise solution for this problem: 

1. Create transformer for objects.
2. Register it in transformer manager or use it separately.
 
Possible transformer for `ProductRepresentation`: 

```php
<?php

class ProductToProductRepresentationModelTransformer implements ModelTransformerInterface
{
	/**
	 * @var ProductRepository
	 */
	private $productRepository;

	// ...

    /**
     * {@inheritdoc}
     */
    public function supports($object, $targetClass)
    {
        return ($object instanceof Product) && is_a($targetClass, ProductRepresentation::class, true);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($object, $targetClass)
    {
    	/** @var Product $product */
    	$product = $object;

    	$purchasedCount = $this->productRepository->computePurchasedCount($product);

    	return new ProductRepresentation(
    		$product->getName(),
    		$product->getCategory()->getName(), 
    		$purchasedCount
    	);
    }
}
```

Register it: 

```php
<?php

$modelTransformer->addTransformer($productToProductRepresentationModelTransformer);
```

Use it anywhere: 

```php
<?php

$productRepresentation = $modelTransformer->transform($product, ProductRepresentation::class);
```

### Specifications & Documentation

All actual documentation is runnable library specifications at `/spec` directory. 

To ensure library is not broken, run (under library directory):

```
bin/phpspec run
```




