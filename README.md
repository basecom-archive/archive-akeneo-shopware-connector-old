# Akeneo To Shopware Connector
## Requirements

| ShopwareConnectorBundle | Akeneo PIM Community Edition |
|:--------------------:|:----------------------------:|
| v1.0.*               | v1.4.\*, v1.5.\*             |
| v2.*                 | v1.6.*                       |

## Installation
Enable the bundle in the `app/AppKernel.php` file in the `registerBundles()` method:

```php
    $bundles = [
        // ...
        new \Basecom\Bundle\ShopwareConnectorBundle\BasecomShopwareConnectorBundle(),
    ]
```

Clear you cache and update your database:

```bash
    php app/console cache:clear --env=prod
    php app/console doctrine:schema:update --force
```

## Documentation

### Attribute Setup
To use the exporter to its full extend you are required to install a Shopware extension which allows you to import all 
custom attributes into Akeneo.
After installing the extension on the Shopware side, you need to create the import job in Akeneo and run it once.

### Export Job
Be sure to fill the correct API details which Shopware is displaying in its user management tab. After the general API
information is filled, the attribute mapping has to be done by hand at which point you need to fill in the Akeneo attribute
names.