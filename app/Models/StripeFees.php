<?php

namespace App\Models;

/**
 *
 * The Fees as of writing are 2.9%; however they're
 * maintained here as 1.029 for easily multiplication.
 *
 * Class StripeFees
 * @package App
 */

class StripeFees
{
    static $fixed = 40; // cents  ->  app fee: 10 cents, stripe fee : 30 cents
    static $percentage = 1.029;
    static $actual_percentage = 0.029;
}
