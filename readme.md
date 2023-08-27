# BackpackShopMollie

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![The Whole Fruit Manifesto](https://img.shields.io/badge/writing%20standard-the%20whole%20fruit-brightgreen)](https://github.com/the-whole-fruit/manifesto)

This package provides Mollie payment integrations for the [eleven59/backpack-shop package](https://github.com/eleven59/backpack-shop).


## Installation

Via Composer

``` bash
composer require eleven59/backpack-shop-mollie
```

## Usage

To use the paymentprovider, update config/backpack-shop.php with the following:

```php
'payment_provider' => \Eleven59\BackpackShopMollie\Models\PaymentProvider::class,
```

And make sure to add the following to your .env file:

```dotenv
MOLLIE_KEY="your-mollie-key"
```

The rest should work out of the box. Optionally, however, you can publish config file and edit the default currency, locale, and webhook url:

```shell
php artisan vendor:publish --provider="Eleven59\BackpackShop\AddonServiceProvider" --tag="config"
```


## Displaying Payment Methods

This is pretty straightforward. The [eleven59/backpack-shop](https://github.com/eleven59/backpack-shop) already provides the global shoppingcart() helper, which works out of the box with this payment method. So the same code works for the default no payment and this one:

```injectablephp
<select name="payment_method">
    @foreach(shoppingcart()->getPaymentMethods() as $method)
        <option value="{{ $method['id'] }}" {{ old('payment_method', 'ideal') === $method['id'] ? 'selected' : '' }}>{{ $method['description'] }}</option>
    @endforeach
</select>
```

This package automatically gets the enabled and active methods from Mollie, so whichever methods are active in your Mollie dashboard should automatically show up here and work perfectly.


## Displaying Issuers and other dependencies

This is completely optional, as Mollie allows you to just specify the payment method and will make you pick an issuer on their end when it is not specified here. The [eleven59/backpack-shop](https://github.com/eleven59/backpack-shop), however, also allows you to make customers pick the issuer within your site. This also skips the Mollie branded screen that would otherwise interrupt your customer's experience.

Here's how to:

**Step 1.** Add class to the payment method selector to indicate that it has dependencies

```injectablephp
<select name="payment_method" class="has-dependencies">
    @foreach(shoppingcart()->getPaymentMethods() as $method)
        <option value="{{ $method['id'] }}" {{ old('payment_method', 'ideal') === $method['id'] ? 'selected' : '' }}>{{ $method['description'] }}</option>
    @endforeach
</select>
```

**Step 2.** Add a field for the dependencies

This field is initially hidden – we will make sure it gets displayed below

```injectablephp
@foreach(shoppingcart()->getPaymentMethods() as $method)
    @if(!empty($method['dependencies']))
        @foreach($method['dependencies'] as $dependency)
            <select class="select2 payment-method-dependent payment-method-{{ $method['id'] }}-dependent" style="display: none;" name="{{ $dependency['name'] }}" id="{{ $dependency['id'] }}" data-minimum-results-for-search="-1">
                @foreach($dependency['values'] as $values)
                    <option value="{{ $values['id'] }}" {{ old($dependency['name'], 'ideal_ABNANL2A') === $values['id'] ? 'selected' : '' }}>{{ $values['description'] }}</option>
                @endforeach
            </select>
        @endforeach
    @endif
@endforeach
```

This code will automatically show the issuers with the correct field name for Mollie to process them when submitting the request.

**Step 3.** Add JS to show/hide the dependent fields dynamically

I'm using jQuery here, because I'm lazy like that.

```javascript
// Define onChange function
$(document).on('change', '.has-dependencies', function() {
    let parent = $(this).attr('id'),
        val = $(this).val();
    
    // First, hide all dependent fields
    $(`.${parent}-dependent`).css('display', 'none');
    
    // Then, show only those that match the current selection
    $(`.${parent}-${val}-dependent`).css('display', '');
});

// Trigger for initial selection
$('.has-dependencies').trigger('change');
```

## Change log

Changes are documented here on Github. Please browse the commit history.

Breaking changes will be listed here, however. None so far.

## Testing

This package provides no testing.

## Contributing

Please see [contributing.md](contributing.md) for a todolist and howtos.

## Security

If you discover any security related issues, please email info@eleven59.nl instead of using the issue tracker.

## Credits

- Author: [eleven59.nl][link-author]
- Built on top of [eleven59/backpack-shop package](https://github.com/eleven59/backpack-shop). Please find additional credits there.

## License

This project was released under MIT, so you can install it on top of any Backpack & Laravel project. Please see the [license file](license.md) for more information.

However, please note that you do need Backpack installed, so you need to also abide by its [YUMMY License](https://github.com/Laravel-Backpack/CRUD/blob/master/LICENSE.md). That means in production you'll need a Backpack license code. You can get a free one for non-commercial use (or a paid one for commercial use) on [backpackforlaravel.com](https://backpackforlaravel.com).


[ico-version]: https://img.shields.io/packagist/v/eleven59/backpack-shop-mollie.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/eleven59/backpack-shop-mollie.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/eleven59/backpack-shop-mollie
[link-downloads]: https://packagist.org/packages/eleven59/backpack-shop-mollie
[link-author]: https://eleven59.nl
