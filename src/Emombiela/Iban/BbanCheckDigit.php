<?php
/*
 * @author  Eduard Mombiela <mombiela.eduard@gmail.com>
 * @version GIT: $Id$
 */

/*
 * Calculate BBAN check digitit/s.
 *
 * @param  string $country
 * @param  string $bban
 * @return array  (boolean, $bbanCheckDigit)
 */
function bbanCheckDigitTest($country, $bban)
{
    $bbanCheckDigit = null;

    switch ($country) {
    case "ES":
        return array(false, $bbanCheckDigit);

    default:
        return array(false, $bbanCheckDigit);

    }
}
