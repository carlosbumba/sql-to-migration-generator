<?php

namespace Bumba\Sql2Migration\Writer\Frameworks\Laravel;

use Bumba\Sql2Migration\Writer\MigrationWriter;

class Writer extends MigrationWriter
{

  public function __construct()
  {
    $this->filename_template = '{datetime}_create_{table}_table';
    $this->template = 'laravel';
    $this->schema = require_once('schema.php');
  }

}
