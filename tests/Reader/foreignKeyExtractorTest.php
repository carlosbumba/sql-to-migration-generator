<?php

use Bumba\Sql2Migration\Reader\traits\foreignKeyExtractor;
use PHPUnit\Framework\TestCase;


class foreignKeyExtractorTest extends TestCase
{
  use foreignKeyExtractor;

  public function test_quoted_values_method()
  {
    $quoted = '`name` `name_b` `name_c`';

    $this->assertSame(3, count($this->extractQuotedValues($quoted)));

    $expected = ['name', 'name_b', 'name_c'];

    $this->assertSame($expected, $this->extractQuotedValues($quoted));
  }


  public function test_trigger_action()
  {
    $fk = 'CONSTRAINT `fk_a` FOREIGN KEY (`client_id`) REFERENCES `mydb`.`client` (`id`) ON DELETE CASCADE ON UPDATE SET NULL)';
    $expected = ['delete' => 'cascade', 'update' => 'set null'];

    $this->assertSame($expected, $this->extractTriggerActions($fk));
  }


  public function test_foreign_key_extraction()
  {
    $fk = 'CONSTRAINT `fk_a` FOREIGN KEY (`client_id`) REFERENCES `mydb`.`client` (`id`) ON DELETE CASCADE ON UPDATE SET NULL)';
    $expected = [
      'name' => '\'fk_a\'', 'field' => '\'client_id\'',
      'references' => ['table' => '\'client\'', 'field' => '\'id\''],
      'triggers' => ['delete' => 'cascade', 'update' => 'set null']
    ];

    $this->assertSame($expected, $this->extractSingleLineFKDefinition($fk));
  }

  public function test_multiline_foreign_key_extraction()
  {
    $fk = [
      'CONSTRAINT `fk_a`',
      'FOREIGN KEY (`client_id`)',
      'REFERENCES `mydb`.`client` (`id`)',
      'ON DELETE CASCADE',
      'ON UPDATE SET NULL)'
    ];

    $expected = [
      'name' => '\'fk_a\'', 'field' => '\'client_id\'',
      'references' => ['table' => '\'client\'', 'field' => '\'id\''],
      'triggers' => ['delete' => 'cascade', 'update' => 'set null']
    ];

    // first we pass the first line
    $actual = $this->extractIfHasForeignKeyDeclaration($fk, 0);

    $this->assertSame($actual, []);

    // now we pass the last line
    $actual = $this->extractIfHasForeignKeyDeclaration($fk, 4);

    $this->assertSame($expected, $actual);
  }
}
