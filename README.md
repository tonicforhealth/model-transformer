Model Transformer
=================

Simple abstraction for object transformations. 

[![Build Status](https://scrutinizer-ci.com/g/tonicforhealth/model-transformer/badges/build.png?b=master)](https://scrutinizer-ci.com/g/tonicforhealth/model-transformer/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/tonicforhealth/model-transformer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/tonicforhealth/model-transformer/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/05f97462-af28-49db-92be-07f38f6a8e19/mini.png)](https://insight.sensiolabs.com/projects/05f97462-af28-49db-92be-07f38f6a8e19)
   
Installation
------------
   
### Require dependencies via composer: 

```
$ composer require tonicforhealth/model-transformer
```
 
Usage 
-----
   
Possible use cases: 

1. Separate domain model layer from view or presentation layer, but still keep objects.
2. Separate domain model from resource representations (in RESTful applications).

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

$priority = 0;
$modelTransformer->addTransformer($productToProductRepresentationModelTransformer, $priority = 0);
```

With an optional priority integer (higher equals more important and therefore that the transformer will be triggered earlier) 
that determines when a transformer is triggered versus other transformers (defaults to 0).

Use it anywhere: 

```php
<?php

$productRepresentation = $modelTransformer->transform($product, ProductRepresentation::class);
```

Specifications
--------------

All actual documentation is runnable library specifications at `/spec` directory. 

And to ensure library is not broken, run (under library directory):

```
bin/phpspec run
```



