<?php

namespace Emombiela\Iban;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class IbanServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
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
     * Passing custom namespace to package method.
     */
    public function boot()
    {
        $this->package('emombiela/iban','iban');

        AliasLoader::getInstance()->alias('Iban', 'Emombiela\Iban\Iban');
    }
}
