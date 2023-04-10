<?php

use Bumba\Sql2Migration\Reader\traits\ColumnExtractor;
use PHPUnit\Framework\TestCase;


class columnExtractorTest extends TestCase
{
  use ColumnExtractor;

  public function testExtractDataTypeWithArg()
  {
    // the definition in this case may include the field name
    $definition = 'id_servico CHAR(36) NOT NULL';
    $expected = ['char', 36];

    $actual = $this->extractDataType($definition);

    $this->assertSame($expected, $actual);
  }

  public function testExtractDataTypeWithoutArg()
  {
    // the definition in this case must no include the field name
    $definition = 'datetime NOT NULL';
    $expected = ['datetime', null];

    $actual = $this->extractDataType($definition);

    $this->assertSame($expected, $actual);
  }


  public function testExtractColumnModifier()
  {
    $definition = "user_level enum('1','2','3') not null default '2'";
    $expected = [
      'unsigned' => false, 'nullable' => false,
      'default' => "'2'", 'primary' => false
    ];

    $actual = $this->extractColumnModifier($definition);

    $this->assertSame($expected, $actual);
  }


  public function test_simple_column_definition()
  {
    $definition = '`user_id` char(36) default "n/a"';

    $expected = [
      'name' => 'user_id', 'str-name'  => "'user_id'",
      'datatype' => 'char', 'datatype-arg' => 36,
      'modifiers' => [
        'unsigned' => false, 'nullable' => true,
        'default' => '"n/a"', 'primary' => false
      ]
    ];

    $this->assertSame(
      $expected,
      $this->extractCurrentColumn($definition)
    );
  }

  public function test_column_definition_with_default_value()
  {
    $definition = '`type` ENUM(1,2,3) NOT NULL DEFAULT(1)';

    $expected = [
      'name' => 'type', 'str-name'  => "'type'",
      'datatype' => 'enum', 'datatype-arg' => [1, 2, 3],
      'modifiers' => [
        'unsigned' => false, 'nullable' => false,
        'default' => '\'1\'', 'primary' => false
      ]
    ];

    $this->assertSame(
      $expected,
      $this->extractCurrentColumn($definition)
    );
  }

  public function test_using_a_function_call_as_default_value()
  {
    $definition = '`type` ENUM(1,2,3) NOT NULL DEFAULT(getdate())';
   
    $expected = [
      'name' => 'type', 'str-name'  => "'type'",
      'datatype' => 'enum', 'datatype-arg' => [1, 2, 3],
      'modifiers' => [
        'unsigned' => false, 'nullable' => false,
        'default' => 'getdate()', 'primary' => false
      ]
    ];

    $this->assertSame(
      $expected,
      $this->extractCurrentColumn($definition)
    );
  }
}
