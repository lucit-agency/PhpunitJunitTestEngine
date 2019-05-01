<?php

/**
 * PHPUnit Result Parsing utility that can read junit output
 *
 * For an example on how to integrate with your test engine, see
 * @{class:PhpunitTestEngine}.
 */
final class ArcanistPhpunitJunitTestResultParser extends ArcanistTestResultParser {

  /**
   * Parse test results from phpunit json report
   *
   * @param string $path Path to test
   * @param string $test_results String containing phpunit junit xml report
   *
   * @return array
   */
  public function parseTestResults($path, $test_results) {
    if (!$test_results) {
      $result = id(new ArcanistUnitTestResult())
        ->setName($path)
        ->setUserData($this->stderr)
        ->setResult(ArcanistUnitTestResult::RESULT_BROKEN);
      return array($result);
    }

    $report = $this->getJunitReport($test_results);

    // coverage is for all testcases in the executed $path
    $coverage = array();
    if ($this->enableCoverage !== false) {
      $coverage = $this->readCoverage();
    }

    $last_test_finished = true;

    $results = array();
    foreach ($report as $event) {
      switch (idx($event, 'event')) {
      case 'test':
        break;
      case 'testStart':
        $last_test_finished = false;
        // fall through
        default:
          continue 2; // switch + loop
      }

      $status = ArcanistUnitTestResult::RESULT_PASS;
      $user_data = '';

      if ('fail' == idx($event, 'status')) {
        $status = ArcanistUnitTestResult::RESULT_FAIL;
        $user_data  .= idx($event, 'message')."\n";
        foreach (idx($event, 'trace') as $trace) {
          $user_data .= sprintf(
            "\n%s:%s",
            idx($trace, 'file'),
            idx($trace, 'line'));
        }
      } else if ('error' == idx($event, 'status')) {
        if (strpos(idx($event, 'message'), 'Skipped Test') !== false) {
          $status = ArcanistUnitTestResult::RESULT_SKIP;
          $user_data .= idx($event, 'message');
        } else if (strpos(
            idx($event, 'message'),
            'Incomplete Test') !== false) {
          $status = ArcanistUnitTestResult::RESULT_SKIP;
          $user_data .= idx($event, 'message');
        } else {
          $status = ArcanistUnitTestResult::RESULT_BROKEN;
          $user_data  .= idx($event, 'message');
          foreach (idx($event, 'trace') as $trace) {
            $user_data .= sprintf(
              "\n%s:%s",
              idx($trace, 'file'),
              idx($trace, 'line'));
          }
        }
      }

      $name = preg_replace('/ \(.*\)/s', '', idx($event, 'test'));

      $result = new ArcanistUnitTestResult();
      $result->setName($name);
      $result->setResult($status);
      $result->setDuration(idx($event, 'time'));
      $result->setCoverage($coverage);
      $result->setUserData($user_data);

      $results[] = $result;
      $last_test_finished = true;
    }

    if (!$last_test_finished) {
      $results[] = id(new ArcanistUnitTestResult())
        ->setName(idx($event, 'test')) // use last event
        ->setUserData($this->stderr)
        ->setResult(ArcanistUnitTestResult::RESULT_BROKEN);
    }
    return $results;
  }

  /**
   * Read the coverage from phpunit generated clover report
   *
   * @return array
   */
  private function readCoverage() {
    $test_results = Filesystem::readFile($this->coverageFile);
    if (empty($test_results)) {
      return array();
    }

    $coverage_dom = new DOMDocument();
    $coverage_dom->loadXML($test_results);

    $reports = array();
    $files = $coverage_dom->getElementsByTagName('file');

    foreach ($files as $file) {
      $class_path = $file->getAttribute('name');
      if (empty($this->affectedTests[$class_path])) {
        continue;
      }
      $test_path = $this->affectedTests[$file->getAttribute('name')];
      // get total line count in file
      $line_count = count(file($class_path));

      $coverage = '';
      $any_line_covered = false;
      $start_line = 1;
      $lines = $file->getElementsByTagName('line');

      $coverage = str_repeat('N', $line_count);
      foreach ($lines as $line) {
        if ($line->getAttribute('type') != 'stmt') {
          continue;
        }
        if ((int)$line->getAttribute('count') > 0) {
          $is_covered = 'C';
          $any_line_covered = true;
        } else {
          $is_covered = 'U';
        }
        $line_no = (int)$line->getAttribute('num');
        $coverage[$line_no - 1] = $is_covered;
      }

      // Sometimes the Clover coverage gives false positives on uncovered lines
      // when the file wasn't actually part of the test. This filters out files
      // with no coverage which helps give more accurate overall results.
      if ($any_line_covered) {
        $len = strlen($this->projectRoot.DIRECTORY_SEPARATOR);
        $class_path = substr($class_path, $len);
        $reports[$class_path] = $coverage;
      }
    }

    return $reports;
  }

  /**
   * This methods transforms junit xml data into an array of results
   *
   * @param string $xml_string String containing JUNIT report
   * @return array Array of unit test
   */
  private function getJunitReport($xml_string) {

    if (empty($xml_string)) {
      throw new Exception(
        pht(
          'JUNIT report file is empty, it probably means that phpunit '.
          'failed to run tests. Try running %s with %s option and then run '.
          'generated phpunit command yourself, you might get the answer.',
          'arc unit',
          '--trace'));
    }

    $doc = new DOMDocument();
    $doc->loadXML($xml_string);

    $elements = $doc->getElementsByTagName("testcase");

    $result = array();

    foreach($elements as $element)
    {
      $testResult = array();
      $testResult["test"] = $element->getAttribute("name");
      $testResult["event"] = "test";
      $testResult["status"] = "pass";
      $testResult["time"] = (float) $element->getAttribute("time");
      $testResult["message"] = "";
      $testResult["trace"] = array();

      if( $element->hasChildNodes() )
      {
        foreach($element->childNodes as $childNode )
        {                  
          switch( $childNode->nodeName )
          {
            case "error" :
              $testResult["status"] = "error";
              $testResult["message"] = $childNode->textContent;
              break;
            case "failure" :
              $testResult["status"] = "fail";
              $testResult["message"] = $childNode->textContent;
              break;
          }
        }       
      }

      $result[] = $testResult;

    }

    return( $result );
    
  }

}