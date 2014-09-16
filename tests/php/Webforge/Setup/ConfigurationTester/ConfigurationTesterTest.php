<?php

namespace Webforge\Setup\ConfigurationTester;

use Webforge\Common\ArrayUtil as A;

class ConfigurationTesterTest extends \Webforge\Code\Test\Base {
  
  protected $t, $retriever;
  
  public function setUp() {
    $this->chainClass = 'Webforge\Setup\ConfigurationTester\ConfigurationTester';
    parent::setUp();
    $this->retriever = $this->getMock('Webforge\Setup\ConfigurationTester\ConfigurationRetriever', array('retrieveIni', '__toString','setAuthentication'));
    $this->t = new ConfigurationTester($this->retriever);
    
    $fakeIni = array(
      'mbstring.internal_encoding'=>'jp18',
      'post_max_size'=>'2M',
      'display_errors'=>'On',
      'register_globals'=>'0'
    );
    
    $this->retriever->expects($this->any())->method('retrieveIni')->will($this->returnCallback(function ($name) use ($fakeIni) {
      return $fakeIni[$name];
    }));
  }
  
  // tested ob der configurationTester lokal inis holen kann (sein Default)
  public function testAcceptance_ConfigurationTesterRetrievesLokalIniValues() {
    $t = new ConfigurationTester();
    $t->INI('post_max_size', ini_get('post_max_size'));
    
    $this->assertZeroDefects();
  }
  
  public function testWrongIniValuesAreGatheredWithinDefects() {
    $this->t->INI('mbstring.internal_encoding', 'utf-8');
    
    $defects = $this->t->getDefects();
    $this->assertCount(1, $defects);
    $this->assertInstanceOf('Webforge\Setup\ConfigurationTester\IniConfigurationDefect', $iniDefect = $defects[0]);
    $this->assertEquals('jp18', $iniDefect->getActualValue());
    $this->assertEquals('utf-8', $iniDefect->getExpectedValue());
  }

  public function testIniValuesAreSkippedIfForExtensionSkipp() {
    $this->t->skipExtension('mbstring');
    
    $this->t->INI('mbstring.internal_encoding', 'utf-8'); // this is normally wrong
    
    $this->assertZeroDefects();
  }
  
  public function testMegabytesGetNormalizedAndCanBeComparedToBytes() {
    $this->t->INI('post_max_size',2*1024*1024);
    $this->t->INI('post_max_size','2M');
    $this->t->INI('post_max_size','2M', '>=');
    $this->t->INI('post_max_size', 1024, '>'); // post_max_size > 1024 => true
    $this->t->INI('post_max_size', '3M', '<');
    $this->t->INI('post_max_size', '3M', '<=');
    
    $this->assertZeroDefects();
  }
  
  public function testThatNotAllOperatorsAreAllowed() {
    $this->setExpectedException('InvalidArgumentException');
    $this->t->INI('register_globals', 'wurst', '===');
  }
  
  public function testBooleansGetNormalized() {
    $this->t->INI('display_errors', TRUE);
    $this->t->INI('register_globals', FALSE);
    
    $this->assertDefects(0);
  }

  public function testBooleansGetNormalizedCmpInt() {
    $this->t->INI('display_errors', 1);
    $this->t->INI('register_globals', 0);
    
    $this->assertDefects(0);
  }
  
  public function testToStringReturnsOKResultFormatted() {
    $this->t->INI('register_globals', FALSE); // defects = 0
    $this->t->INI('display_errors', TRUE);   // defects = 0
    
    $msg = (string) $this->t;
    
    $this->assertContains('OK (2 checks)', $msg);
  }

  public function testToStringReturnsFailureResultFormatted() {
    $this->t->INI('register_globals', FALSE); // defects = 0
    $this->t->INI('display_errors', FALSE);   // defects = 1
    
    $msg = (string) $this->t;
    
    $this->assertContains('DEFECTS DETECTED', $msg);
    $this->assertContains('checks: 2', $msg);
    $this->assertContains('defects: 1', $msg);
  }
  
  /**
   * @dataProvider provideExpandIniValues
   */
  public function testIniValueExpanding($expectedOperator, $expectedValue, $iniValue) {
    list ($actualOperator, $actualValue) = $this->t->expandIniValue($iniValue);
    
    $msg = 'for iniValue: '.$iniValue;
    $this->assertEquals($expectedOperator, $actualOperator, $msg);
    $this->assertEquals($expectedValue, $actualValue, $msg);
  }
  
  public static function provideExpandIniValues() {
    $tests = array();
    
    $tests[] = array('>=', '20M', '>= 20M');
    $tests[] = array('==', 'true', '== true'); // this will break
    $tests[] = array('==', '9', '9');
    
    return $tests;
  }
  
  public function testHasDefectsIsFalseForZeroDefects() {
    $this->assertCount(0, $this->t->getDefects());
    $this->assertFalse($this->t->hasDefects());
  }
  
  protected function assertZeroDefects() {
    return $this->assertDefects(0);
  }
  
  protected function assertDefects($cnt) {
    $this->assertCount($cnt, $this->t->getDefects(), $cnt.' defects expected. Defects-List:'."\n".$this->debugDefects());
  }
  
  protected function debugDefects() {
    return A::join($this->t->getDefects(), "%s\n");
  }
}
?>