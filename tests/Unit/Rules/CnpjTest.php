<?php

namespace Tests\Unit\Rules;

use App\Rules\Cnpj;
use Tests\TestCase;

class CnpjTest extends TestCase
{
    public function testCnpj()
    {
        $validator = new Cnpj();

        $values = [
            '16206981000168' => true,
            '0' => false,
            'abcdedaszxcasd' => false,
            '16206981000108' => false,
            '00000000000000' => false,
            '16206981000169' => false,
        ];

        array_walk($values, function ($expected, $cnpj) use ($validator) {
            $this->assertEquals($expected, $validator->passes('cnpj', $cnpj));
        });
    }
}
