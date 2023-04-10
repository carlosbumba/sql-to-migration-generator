<?php

namespace Bumba\Sql2Migration\Reader\MSSQL;

use Bumba\Sql2Migration\Reader\SqlReader;


class Reader extends SqlReader
{

  protected function extractCurrentColumn(string $definition)
  {
    $info = parent::extractCurrentColumn($definition);

    if (stripos($definition, 'identity(') !== false) {
      $regex = '@identity\(([1-9]+),([1-9]+)\)\s@';
      $args = [1, 1];

      if (preg_match($regex, $definition, $matches)) {
        if ($matches[1] != 1 or $matches[2] != 1) {
          $args = array_map('intval',  array_slice($matches, 1));
        }
      }

      $info['modifiers']['identity'] = $args;
      $info['datatype'] = 'integer';
    }

    return $info;
  }


  protected function extractDataType(string $definition): string|array
  {

    if (stripos($definition, 'identity(') !== false) {
      return explode(' ', $definition, 2)[0];
    }

    return parent::extractDataType($definition);
  }
}
