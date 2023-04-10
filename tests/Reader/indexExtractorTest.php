<?php

use Bumba\Sql2Migration\Reader\traits\indexExtractor;
use PHPUnit\Framework\TestCase;


class indexExtractorTest extends TestCase
{
  use indexExtractor;

  public function test_all_info_as_array()
  {
    $expected = [
      'index' => '\'fk_a_idx\'',
      'column' => ['name' => '\'column_name\'', 'sortMode' => 'desc'],
      'visibility' => 'visible'
    ];

    $actual = $this->getAllIndexInformationAsArray('fk_a_idx', '`column_name` DESC');

    $this->assertSame($expected, $actual);
  }


  public function test_primary_key_index()
  {
    $input = 'PRIMARY KEY (`service2_id`)';
    $expected = 'service2_id';

    $this->assertSame($expected, $this->extractIfHasPrimaryKeyDeclaration($input)[0]);
  }


  public function test_basic_index_declaration()
  {
    $index = 'INDEX `fk_b_idx` (`column_name` ASC) INVISIBLE';

    $expected = [
      'index' => '\'fk_b_idx\'',
      'column' => ['name' => '\'column_name\'', 'sortMode' => 'asc'],
      'visibility' => 'invisible',
      'type' => 'basic'
    ];

    $this->assertSame($expected, $this->extractBasicIndex($index));
  }

  public function test_complex_index_declaration()
  {
    $index = 'UNIQUE INDEX `fk_c_idx` USING BTREE (`column_name`) INVISIBLE';

    $expected = [
      'index' => '\'fk_c_idx\'',
      'column' => ['name' => '\'column_name\'', 'sortMode' => 'asc'],
      'visibility' => 'invisible',
      'type' => 'unique'
    ];

    $this->assertSame($expected, $this->extractNonBasicIndex($index));

    //
    $expected['algorithm'] = '\'btree\'';

    $this->assertSame($expected, $this->extractIfHasIndexDeclaration($index));
  }
}
