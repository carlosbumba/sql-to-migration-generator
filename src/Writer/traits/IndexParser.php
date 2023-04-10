<?php

namespace Bumba\Sql2Migration\Writer\traits;


trait IndexParser
{


  protected function parseIndex(array $index)
  {
    $fn = $this->getSchemaIdxFunctionForReplacement($index['type']);
    $args = $this->getIndexArgsForReplacement($index);
    $signature = $this->replaceParams($args, $fn);
    return ($this->schema['prefix'] ?? '$table->') . $signature  . ';';
  }


  protected function getSchemaIdxFunctionForReplacement(string $type)
  {
    $fn = $this->schema['indexes'][$type] ?? null;

    if (is_null($fn)) {
      throw new \Exception("Index Type {$type} don't have a function in this schema");
    }

    return $fn;
  }
  

  protected function getIndexArgsForReplacement(array $index)
  {
    $args = [
      $index['column']['name'],
      $index['index'],
      $index['algorithm'] ??  null,
      $index['column']['sortMode'],
      $index['visibility']
    ];

    return $args;
  }
}
