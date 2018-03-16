<?php

use PHPUnit\Framework\TestCase;
use Sinesp\Placa;
use Sinesp\Exceptions\SinespException;

class SinespTest extends TestCase
{
    /**
     * @test
     */
    public function testInvalidLicensePlate()
    {
        try {
            $veiculo = (new Placa('000-9999'))->search();
        } catch (SinespException $ex) {
            $this->assertTrue($ex->getMessage() === 'Placa invalida.');
        }
    }

    /**
     * @test
     */
    public function testValid()
    {
        $veiculo = (new Placa('CQK-6061'))->search();
        $this->assertTrue($veiculo['modelo'] === 'GM/CORSA SUPER');
        $this->assertTrue($veiculo['cor'] === 'PRATA');
        $this->assertTrue($veiculo['ano'] === '1998');
        $this->assertTrue($veiculo['chassi'] === '************48150');
    }

    /**
     * @test
     */
    public function testReturnsArray()
    {
        $veiculo = new Placa('CQK-6061');
        $this->assertTrue(is_array(($veiculo->search())));
    }
}