<?php

namespace Bumba\Sql2Migration\Reader\traits;


trait schemaExtractor
{
  protected function extractSchemaCharset(string $line)
  {
    // capture charset
    if (stripos($line, 'DEFAULT CHARACTER SET') !== false) {
      $regex = "@DEFAULT\sCHARACTER\sSET\s([a-zA-Z0-9\-\_]+)\s+?@i";
      if (preg_match($regex, $line, $matches)) {
        return $matches[1];
      }
    }

    return '';
  }

  protected function extractCurrentSchema(string $line)
  {
    $regex = "@\`([a-zA-Z0-9\_]+)\`@";
    $schema = null;

    // capture the schema name
    if (preg_match($regex, $line, $matches)) {
      $schema = $matches[1];
    }

    if (is_null($schema)) {
      throw new \Exception("Schema name is not inside ` `");
    }

    // capture charset if is present
    $charset = $this->extractSchemaCharset($line);

    return [
      'schema' => $schema,
      'charset' => $charset
    ];
  }
}
