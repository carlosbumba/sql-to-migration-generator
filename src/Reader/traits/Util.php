<?php

namespace Bumba\Sql2Migration\Reader\traits;


trait Util
{
  protected function extractQuotedValues(string $input)
  {
    $regex = "@\`([a-zA-Z0-9\_]+)\`?@";
    return preg_match_all($regex, $input, $matches) ? $matches[1] : '';
  }
}
