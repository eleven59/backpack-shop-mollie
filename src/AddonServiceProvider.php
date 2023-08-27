<?php

namespace Eleven59\BackpackShopMollie;

use Illuminate\Support\ServiceProvider;

class AddonServiceProvider extends ServiceProvider
{
    use AutomaticServiceProvider;

    protected $vendorName = 'eleven59';
    protected $packageName = 'backpack-shop-mollie';
    protected $commands = [];
}
