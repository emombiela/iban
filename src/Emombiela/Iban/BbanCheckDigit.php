<?php
/**
 * Calculate BBAN check digit/s.
 *
 * @author  Eduard Mombiela <mombiela.eduard@gmail.com>
 * @version GIT: $Id$
 */

namespace Emombiela\Iban;

/**
 * Countries with BBAN calculation.
 *
 * Countries with the BBAN calculation algorithm implemented.
 *
 * @return array
 *
 * @todo   If you want to implement the algorithm for calculating the check digits
 * of the bank account for your country or for a country that you know,
 * you have to add to the array this function returns the ISO 3166 code
 * and the country name.
 */
function bbanCountries()
{
    return array('ES' => 'Spain',
                 // Put your country here...
           );
}

/**
 * Calculate BBAN check digit/s.
 *
 * @param  string $country
 * @param  string $bban
 * @return array
 * array[0]:boolean = True if exist calculation for the country,<br />
 * array[1]:boolean = True if right check digit/s,<br />
 * array[2]:string  = Check digit/s.
 *
 * @todo   If you want to implement the algorithm for calculating the check digits
 * of the bank account for your country or for a country that you know,
 * you have to add it like a switch/case option. Please put all your code
 * inside your case option including functions.
 */
function bbanCheckDigitTest($country, $bban)
{
    $bbanCheckDigit = null;

    switch ($country) {
    case "ES":
        /**
         * Validate and calculate Spain BBAN check digits.
         *
         * @author  Eduard Mombiela
         * @version GIT: $Id$
         * @link    http://es.wikipedia.org/wiki/C%C3%B3digo_cuenta_cliente#D.C3.ADgitos_de_control
         */

        /**
         * Calculate check digit.
         *
         * @param  string  $bbanSlice
         * @return integer
         */
        function test($bbanSlice) {
            $checkDigit = 0;

            for ($i = 0; $i < strlen($bbanSlice); $i++) {
                $checkDigit += substr($bbanSlice, $i , 1) * (pow(2, $i) % 11);
            }

            $checkDigit = 11 - $checkDigit % 11;

            if ($checkDigit == 11) {
                $checkDigit = 0;
            } else if ($checkDigit == 10) {
                $checkDigit = 1;
            }

            return $checkDigit;
        }

        $firstCheckDigit = test('00'.substr($bban,0,8));
        $secondCheckDigit = test(substr($bban,-10));

        if ($firstCheckDigit.$secondCheckDigit == substr($bban,8,2))
        {
            return array(true, true, $firstCheckDigit.$secondCheckDigit);
        } else {
            return array(true, false, $firstCheckDigit.$secondCheckDigit);
        }

        break;

    // Put your case option here...

    default:
        return array(false, false, $bbanCheckDigit);
        break;
    }
}
