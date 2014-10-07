<?php
/**
 * @author  Eduard Mombiela <mombiela.eduard@gmail.com>
 * @version GIT: $Id$
 */

require_once('Countries.php');

/**
 * Returns an array with the names of the countries
 * where the key of each element is the country code defined in ISO 3166.
 *
 * @return array
 */
function countries()
{
    $countriesList = array();

    foreach ($GLOBALS['countries'] as $countryKey => $countryData) {
        $countriesList = array_add($countriesList, $countryKey, $countryData['countryName']);
    }

    return $countriesList;
}

/**
 * Returns an array with the names of the SEPA countries
 * where the key of each element is the country code defined in ISO 3166.
 *
 * @return array
 */
function sepaCountries()
{
    $countriesList = array();

    foreach ($GLOBALS['countries'] as $countryKey => $countryData) {
        if ($countryData['sepaCountry']) {
            $countriesList = array_add($countriesList, $countryKey, $countryData['countryName']);
        }
    }

    return $countriesList;
}

/**
 * Returns an array with the structure of a given country.
 *
 * @param  string $sountryCode
 * @return array
 */
function getCountry($countryCode)
{
    //global $countries;
    return array_get($GLOBALS['countries'],$countryCode);
}

