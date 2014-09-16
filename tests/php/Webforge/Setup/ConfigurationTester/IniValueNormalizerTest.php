<?php

namespace Webforge\Setup\ConfigurationTester;

class IniValueNormalizerTest extends \Webforge\Code\Test\Base {
  
  protected $iniValueNormalizer;
  
  public function setUp() {
    $this->chainClass = 'Webforge\Setup\IniValueNormalizer';
    parent::setUp();
    $this->iniValueNormalizer = new IniValueNormalizer();
  }
  
  /**
   * @dataProvider provideNormalizations
   */
  public function testNormalization($expectedIniValue, $iniValue, $iniName = NULL) {
    $actualIniValue = $this->iniValueNormalizer->normalize($iniValue, $iniName);
    
    $this->assertEquals($expectedIniValue, $actualIniValue, 'Normalization für '.$iniName);
  }
  
  public static function provideNormalizations() {
    $tests = array();
    $n = function ($value, $normalized, $name = NULL) use (&$tests) {
      $tests[] = array($normalized, $value, $name);
    };
    
    $n('On', TRUE, 'xdebug.profiler_enable_trigger');
    $n('Off', FALSE, 'xdebug.profiler_enable_trigger');
    $n('20M', 20 * 1024 * 1024, 'post_max_size');
    $n('1G', 1 * 1024 * 1024 * 1024, 'post_max_size');
    $n('2', 2, 'post_max_size');
    $n('2B', '2B', 'post_max_size');
    $n('0', '0', 'register_globals');
    $n(0, 0, 'register_globals');
    $n('20kb', 20 * 1024, 'post_max_size');
    
    $n('E_ALL | E_STRICT', E_ALL | E_STRICT, 'error_reporting');
    
    return $tests;
  }
  
  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage Parse Error
   */
  public function testErrorReportingNormalizationFailure() {
    $this->iniValueNormalizer->normalize('E_ALL | "blubb"', 'error_reporting');
  }
}
