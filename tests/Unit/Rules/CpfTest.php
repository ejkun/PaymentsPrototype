<?php


namespace Tests\Unit\Rules;


use App\Rules\Cpf;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CpfTest extends TestCase
{
    use WithFaker;

    public function testCpf()
    {
        $validator = new Cpf();

        $values = [
            $this->faker->cpf(false) => true,
            '0' => false,
            '00000000000' => false,
            '11111111111' => false,
            '50623257060' => false,
            '40623257068' => false,
        ];

        array_walk($values, function ($expected, $cpf) use ($validator) {
            $this->assertEquals($expected, $validator->passes('cpf', $cpf));
        });
    }
}
