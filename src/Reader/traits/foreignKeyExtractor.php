<?php

namespace Bumba\Sql2Migration\Reader\traits;


trait foreignKeyExtractor
{
  use Util;
  
  private ?int $fkFistIndex = null;

  protected function getStringBeforeChars(array $chars, string $text)
  {
    foreach ($chars as $char) {
      if (str_contains($text, $char)) {
        $text = explode($char, $text)[0];
      }
    }

    return $text;
  }

  protected function extractTriggerActions(string $definition)
  {
    $triggers = [];

    $definition = preg_replace('@\s+@', ' ', $definition);

    $pos = stripos($definition, ' ON ');

    if ($pos !== false) {
      $text = trim(substr($definition, $pos, -1));
      // start after the first ON
      $afterOn = substr($text, 3);
      $items = array_map('strtolower', explode(' ON ', $afterOn));

      foreach ($items as $item) {
        list($key, $value) = explode(' ', $item, 2);

        $value = $this->getStringBeforeChars([',', ')', ';'], $value);

        $triggers[$key] = $value;
      }
    }

    return $triggers;
  }

  protected function extractSingleLineFKDefinition(string $definition)
  {
    $quotedValues = $this->extractQuotedValues($definition);

    if (count($quotedValues) == 5) {
      $table = $quotedValues[3];
      $field = $quotedValues[4];
    } else {
      list($table, $field) = $quotedValues;
    }

    return [
      'name' => "'{$quotedValues[0]}'",
      'field' => "'{$quotedValues[1]}'",
      'references' => ['table' =>  "'{$table}'", 'field' => "'{$field}'"],
      'triggers' => $this->extractTriggerActions($definition)
    ];
  }


  protected function extractIfHasForeignKeyDeclaration(array $lines, int $currentIteration)
  {
    $line = $lines[$currentIteration];

    /**
     * extract single line foreign key definition
     */
    if (stripos($line, 'constraint') !== false and stripos($line, 'foreign key') !== false) {
      return $this->extractSingleLineFKDefinition($line);
    }

    // multiline foreign key definition
    if (stripos($line, 'constraint') !== false and stripos($line, 'foreign key') === false) {
      /**
       * first we store the current index 
       *indicating the start of multine definition
       */
      $this->fkFistIndex = $currentIteration;
    }

    /**
     * if a comma or close brackets was found in end of line, we call the method to extract fk information
     * from `fkFirstIndex` to current index `i`
     */
    $regex = '@(on|references)\s([a-z0-9\s\`\.\)\,\(\_]+)\s?([a-z]|\))(\,|\))@i';

    if (!is_null($this->fkFistIndex) and preg_match($regex, $line)) {

      $sliced = array_slice(
        $lines,
        $this->fkFistIndex,
        $currentIteration - $this->fkFistIndex + 1
      );

      $this->fkFistIndex = 0;

      return $this->extractSingleLineFKDefinition(implode(' ', $sliced));
    }

    return [];
  }
}
