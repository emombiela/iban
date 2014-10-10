<?php
/**
 * Calculate BBAN check digit/s.
 *
 * @author  Eduard Mombiela <mombiela.eduard@gmail.com>
 * @version GIT: $Id$
 */

/**
 * Calculate BBAN check digit/s.
 *
 * @param  string $country
 * @param  string $bban
 * @return array           (boolean: Exist calculation for the country,
 *                          boolean: Right check digit/s,
 *                          string:  Check digit/s)
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
         * @see     http://es.wikipedia.org/wiki/C%C3%B3digo_cuenta_cliente#D.C3.ADgitos_de_control
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

    default:
        return array(false, false, $bbanCheckDigit);
        break;
    }
}
