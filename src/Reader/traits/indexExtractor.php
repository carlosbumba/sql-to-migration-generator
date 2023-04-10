<?php

namespace Bumba\Sql2Migration\Reader\traits;


trait indexExtractor
{
  use Util;

  protected function getAllIndexInformationAsArray(string $index, string $field, string $visibility = 'visible')
  {
    $indexedColumn = $this->getIndexedColumn($field);

    return [
      'index' => "'{$index}'",
      'column' => $indexedColumn,
      'visibility' => strtolower($visibility)
    ];
  }

  protected function getIndexedColumn(string $input)
  {
    $results = [];

    //  multi column
    if (strpos($input, ',') !== false) {
      $regex = '@\`([a-z0-9\_]+)\`\s(asc|desc)@i';

      if (preg_match_all($regex, $input, $secondMatch)) {
        foreach ($secondMatch[1] as $key => $value) {
          $results[] = ['name' => "'{$value}'", 'sortMode' => strtolower($secondMatch[2][$key])];
        }
      }
    } else {
      //  single column
      $column = $this->extractQuotedValues($input)[0] ?? $input;
      $sortMode = strtolower(trim(explode(' ', $input)[1] ?? 'ASC'));
      $results['name'] = "'{$column}'";
      $results['sortMode'] = $sortMode;
    }

    return $results;
  }

  protected function extractIndexAlgorithm(string $line)
  {
    $regex = '@ using ([a-z]+)@i';
    $algorithm = '';

    if (preg_match($regex, $line, $matches)) {
      $algorithm = strtolower($matches[1]);
    }

    return "'{$algorithm}'";
  }


  protected function extractNonBasicIndex(string $line)
  {
    $regex = '@([a-z]+)\sindex\s\`([a-z0-9\_]+)\`\s?(?:using [a-z]+)?\s\((.+)\)\s?(visible|invisible)?@i';
    $regex2 = '@([a-z]+)\sindex\s\`([a-z0-9\_]+)\`\s?\(`([a-z0-9\_]+)\`\s?(asc|desc)\)@i';
    // special case, example: SPATIAL INDEX `index_name`(`column`) USING (algorithm)
    $regex3 = '@([a-z]+)\sindex\s\`([a-z0-9\_]+)\`\s?\(`([a-z0-9\_]+)\`\)\s?(?:using [a-z]+)?@i';

    $info = [];

    // first regex test
    if (preg_match($regex, $line, $matches)) {

      $info = $this->getAllIndexInformationAsArray(
        $matches[2],
        $matches[3],
        $matches[4] ?? 'visible'
      );
    } else {

      // second regex test
      if (preg_match($regex2, $line, $matches) || preg_match($regex3, $line, $matches)) {

        $info = $this->getAllIndexInformationAsArray(
          $matches[2],
          $matches[3]
        );
      } else {
        return [];
      }
    }

    $info['type'] = strtolower($matches[1]);

    return $info;
  }


  protected function extractBasicIndex(string $line)
  {
    $regex = '@\s\`([a-z0-9\_]+)\`\s\((.+)\)\s(visible|invisible)@i';
    $info = [];

    if (preg_match($regex, $line, $matches)) {

      $info = $this->getAllIndexInformationAsArray(
        $matches[1],
        $matches[2],
        $matches[3]
      );

      $info['type'] = 'basic';
    }

    return $info;
  }

  protected function extractIndexByType(string $line, bool $isBasic = true)
  {
    $info = [];

    if ($isBasic) {
      $info = $this->extractBasicIndex($line);
    } else {
      $info = $this->extractNonBasicIndex($line);
    }

    if (count($info) and stripos($line, ' using ') !== false) {
      $info['algorithm'] = $this->extractIndexAlgorithm($line);
    }

    return $info;
  }


  protected function extractIfHasIndexDeclaration(string $line)
  {
    $regex = '@\s?index\s\`@i';

    if (preg_match($regex, $line)) {
      $index = (stripos($line, 'index') === 0) ? $this->extractIndexByType($line) : $this->extractIndexByType($line, false);
      if (count($index)) return $index;
    }

    return [];
  }

  protected function extractIfHasPrimaryKeyDeclaration(string $line)
  {
    $value = '';

    if (stripos($line, 'primary key') === 0) {
      $value = $this->extractQuotedValues($line);
    }

    return $value;
  }
}
