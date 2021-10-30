# Cart

[![swagger](https://img.shields.io/badge/documentation-swagger-green)](https://escolalms.github.io/Cart/)
[![codecov](https://codecov.io/gh/EscolaLMS/cart/branch/main/graph/badge.svg?token=NRAN4R8AGZ)](https://codecov.io/gh/EscolaLMS/cart)
[![phpunit](https://github.com/EscolaLMS/cart/actions/workflows/test.yml/badge.svg)](https://github.com/EscolaLMS/cart/actions/workflows/test.yml)
[![downloads](https://img.shields.io/packagist/dt/escolalms/cart)](https://packagist.org/packages/escolalms/cart)
[![downloads](https://img.shields.io/packagist/v/escolalms/cart)](https://packagist.org/packages/escolalms/cart)
[![downloads](https://img.shields.io/packagist/l/escolalms/cart)](https://packagist.org/packages/escolalms/cart)
[![Maintainability](https://api.codeclimate.com/v1/badges/b8c8aa16976961f670b4/maintainability)](https://codeclimate.com/github/EscolaLMS/Cart/maintainability)

## Usage

User model (or any other Model representing Authenticatable entity) must be extended with the trait CanOrder:

```php
use EscolaLms\Cart\Models\Traits\CanOrder;

class User extends EscolaLms\Core\Models\User
{
    use CanOrder;
    //...
}
```
