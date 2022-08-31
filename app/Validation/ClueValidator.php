<?php

namespace App\Validation;

class ClueValidator
{
    /*
     * Checks that the provided value only contains a mix of 'g', 'h', 's', or 't'
     *
     */
    public function checkDnaSequence($attribute, $value, $parameters, $validator)
    {
        $allowedChars = 'ghst';

        if (array_diff(array_unique(str_split($allowedChars)), array_unique(str_split($value)))) {
            return false;
        }

        return true;
    }
}
