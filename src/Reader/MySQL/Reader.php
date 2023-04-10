<?php

namespace Bumba\Sql2Migration\Reader\MySQL;

use Bumba\Sql2Migration\Reader\SqlReader;


class Reader extends SqlReader
{

  protected function extractCurrentColumn(string $definition)
  {
    $info = parent::extractCurrentColumn($definition);

    if (stripos($definition, 'auto_increment') !== false) {
      $info['modifiers']['auto_increment'] = true;
    }

    return $info;
  }
}
