<?php
/**
 * Bootstrap classes for Iban package.
 *
 * @author  Eduard Mombiela <mombiela.eduard@gmail.com>
 * @version GIT: $Id$
 */

namespace Emombiela\Iban;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

/**
 * Bootstrap classes for Iban package.
 *
 * @author  Eduard Mombiela <mombiela.eduard@gmail.com>
 * @version GIT: $Id$
 * @package Iban
 */
class IbanServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var    boolean
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

    /**
     * Pass custom namespace to package method.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('emombiela/iban','iban');

        AliasLoader::getInstance()->alias('Iban', 'Emombiela\Iban\Iban');
    }
}
