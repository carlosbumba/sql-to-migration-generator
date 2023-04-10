<?php

namespace Bumba\Sql2Migration\Writer\traits;


trait SignatureParams
{

  protected function getParams(string $signature): array
  {
    $params = [];

    $regex = '@(\$[a-z]+(?:\=[a-z0-9\"\']+)?)@';

    if (preg_match_all($regex, $signature, $matches)) {
      foreach ($matches[1] as $match) {
        if (str_contains($match, '=')) {
          $parts = explode('=', $match);
          if (ctype_digit($parts[1])) $parts[1] = intval($parts[1]);
          $params[] = [$match, ...$parts];
        } else {
          $params[] = $match;
        }
      }
    }

    return $params;
  }

  protected function removeParam(string $key, string $signature): string
  {
    $replaces = [",{$key}", ", {$key}", $key];

    if (str_contains($signature, "({$key}")) {
      array_unshift($replaces, "{$key},");
    }

    return str_replace($replaces, '', $signature);
  }


  protected function replaceParams(array $values, string $signature): string
  {
    $params = $this->getParams($signature);
    $valuesCount = count($values);
    $paramsCount = count($params);

    foreach ($params as $index => $param) {
      $key = is_array($param) ? $param[0] : $param;
      $default = is_array($param) ? $param[2] : null;
      $value = $values[$index] ?? null;
      $nextDefault = (isset($params[$index + 1])) ? $params[$index + 1][2] ?? null : null;
      $nextValue = $values[$index + 1] ?? -1;

      if ($valuesCount == 0 && $default) {
        $signature = $this->removeParam($key, $signature);
        continue;
      }

      if ($value != null) {
        // 1: test if is the last param
        // 2: test if there is not next value
        // 3: test if the next parameter will be removed
        $tests = ((($index + 1) == $paramsCount) or !isset($nextValue) or ($nextValue == $nextDefault));
        $signature = ($default == $value and ($tests)) ? $this->removeParam($key, $signature) : str_replace($key, $value, $signature);
      } elseif ($default !== null) {
        $signature = $this->removeParam($key, $signature);
      }
    }

    return $signature;
  }
}
