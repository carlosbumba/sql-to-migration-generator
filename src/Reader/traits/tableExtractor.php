<?php

namespace Bumba\Sql2Migration\Reader\traits;


trait tableExtractor
{

  protected function extractCurrentTable(string $line)
  {
    $table = null;
    $text = str_replace('`', '', $line);
    $regex = "@\s?([a-zA-Z0-9_\.]+)\s+?\(@";

    if (preg_match($regex, $text, $matches)) {
      $table = $matches[1];

      if (strpos($table, '.')) {
        $table = explode('.', $table)[1];
      }
    }

    if (is_null($table)) {
      throw new \Exception("table name not found");
    }

    return $table;
  }
}
