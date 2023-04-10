<?php

use Bumba\Sql2Migration\Writer\traits\SignatureParams;
use PHPUnit\Framework\TestCase;



class SignatureParamsTest extends TestCase
{
  use SignatureParams;

  public static function SignaturesToGetParamsProvider()
  {
    return [
      ['fn()', []],
      ['fn($a,$b)', ['$a', '$b']],
      ['f($a,$b=5,$c)', ['$a', ['$b=5', '$b', 5], '$c']],
      ['fn($a,$b,$c=3)', ['$a', '$b', ['$c=3', '$c', 3]]],
      ['fn($a,$b=8,$c=3)', ['$a', ['$b=8', '$b', 8], ['$c=3', '$c', 3]]],
      ['fn($a="now",$b,$c=20)', [['$a="now"', '$a', '"now"'], '$b', ['$c=20', '$c', 20]]],
    ];
  }

  public static function SignaturesRemoveParamsProvider()
  {
    return [
      ['fn($a,$b)', '$a', 'fn($b)'],
      ['fn($a,$b,$c)', '$b', 'fn($a,$c)'],
      ['fn($a,$b,$c)', '$a', 'fn($b,$c)'],
    ];
  }

  public static function SignaturesToReplacementProvider()
  {
    return [
      ['f($a,$b,$c)', [12, 20, 16], 'f(12,20,16)'],
      ['f($a,$b,$c)', [12, 20], 'f(12,20,$c)'],
      ['f($a,$b=5)', [12, 20], 'f(12,20)'],
      ['f($a,$b=5,$c=20)', [12], 'f(12)'],
      ['f($a,$b=5)', [12], 'f(12)'],
      ['f($a,$b=5)', [12,5], 'f(12)'],
      ['f($a,$b)', [], 'f($a,$b)'],
      ['f()', [12, 20, 40], 'f()'],
      ['f($a=4,$b=5)', [], 'f()']
    ];
  }


  /**
   *
   * @dataProvider SignaturesToGetParamsProvider
   */
  public function test_signatures($signature, $expected)
  {
    $actual  = $this->getParams($signature);
    $this->assertSame($expected, $actual);
  }


  /**
   *
   * @dataProvider SignaturesRemoveParamsProvider
   */
  public function test_param_deletation(string $signature, string $param, string $expected)
  {
    $actual  = $this->removeParam($param, $signature);
    $this->assertSame($expected, $actual);
  }

  /**
   *
   * @dataProvider SignaturesToReplacementProvider
   */
  public function test_param_replacement(string $signature, array $data, string $expected)
  {
    $actual = $this->replaceParams($data, $signature);
    $this->assertSame($expected, $actual);
  }
}
