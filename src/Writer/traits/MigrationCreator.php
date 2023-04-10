<?php

namespace Bumba\Sql2Migration\Writer\traits;


trait MigrationCreator
{
  protected ?\DateTime $datetime = null;

  protected function createFileForTable(string $tablename)
  {
    if (empty($this->outputDir) || !is_dir($this->outputDir)) {
      throw new \Exception("Output Directory not set or not exists");
    }

    $filename = $this->getMigrationFilename($tablename);
    $path = $this->outputDir . DIRECTORY_SEPARATOR . $filename . '.php';

    fclose(fopen($path, 'w'));

    return $path;
  }


  protected function getMigrationFilename(string $table)
  {
    $format = $this->filename_template;
    $date = $this->datetime ?? (new \DateTime);

    $replacements = [
      '{table}' => $table,
      '{time}' => $date->format('H_i_s'),
      '{date}' => $date->format('Y_m_d'),
      '{datetime}' => $date->format('Y_m_d_His')
    ];

    $this->datetime = $date->add(new \DateInterval('PT1S'));

    return str_replace(
      array_keys($replacements),
      array_values($replacements),
      $format
    );
  }


  protected function getTemplateContent()
  {
    $ds = DIRECTORY_SEPARATOR;
    $templatesDirectory = __DIR__ . $ds . '..' . $ds . 'Frameworks' . $ds . 'templates' . $ds;
    $filename = $templatesDirectory . $this->template . '.template';

    if (!is_file($filename)) {
      throw new \Exception("Template file not exists");
    }

    return file_get_contents($filename);
  }


  protected function getStatments(array $items, string $parserMethod, string $commentText = 'columns')
  {
    $statments = '';
    $tabs = str_repeat("\t", $this->schema['content-tabs'] ?? 3);

    if (count($items)) $statments .= $tabs . "// table {$commentText}\n";

    foreach ($items as $item) {
      $statments .= $tabs . $this->$parserMethod($item) . "\n";
    }

    // remove the last new line :)
    $statments = substr($statments, 0, -1);

    return $statments;
  }


  protected function replaceTemplateVariables(string $template, string $table, string $content)
  {
    $replacements = ['{{ $table }}' => $table, '{{ $content }}' => $content];
    return trim(str_replace(array_keys($replacements), array_values($replacements), $template), "\r\n");
  }
}
