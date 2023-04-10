<?php

namespace Bumba\Sql2Migration\Reader\PostgreSQL;

use Bumba\Sql2Migration\Reader\SqlReader;


class Reader extends SqlReader
{

  protected function extractCurrentColumn(string $definition)
  {
    $info = parent::extractCurrentColumn($definition);

    if (stripos($definition, 'serial') !== false) {
      $info['modifiers']['serial'] = true;
    }

    return $info;
  }

}
