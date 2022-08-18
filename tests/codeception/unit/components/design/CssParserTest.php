<?php

namespace unit\components\design;

use skewer\components\design\CssParser;

class CssParserTest extends \Codeception\Test\Unit
{
    private $sTestCss = 'test.css';
    private $sExampeCss = 'example.css';
    private $sPathCssTest;
    private $sPathCssExample;

    /* @var \skewer\components\design\CssParser $oCSSParser   */
    protected $oCSSParser;

    private $sErrorCssString = 'Ошибочно задан рузельтат соответствия для providerMath';

    protected function setUp()
    {
        $this->sPathCssTest = ROOTPATH . "tests/codeception/unit/components/design/css/{$this->sTestCss}";
        $this->sPathCssExample = ROOTPATH . "tests/codeception/unit/components/design/css/{$this->sExampeCss}";
        $this->oCSSParser = new CssParser();
    }

    /**
     * данные для теста.
     */
    public function providerMath()
    {
        return [
            ['width: 24px+5px;', 'width: 29px;', false],
            ['width: 24px+5px', 'width: 29px', false],
            ['width: 24px + 5px;', 'width: 29px;', true],
            ['width: 24px * 5px;', 'width: 120px;', true],
            ['width: 24px*5px;', 'width: 120px;', false],
            ['/*width: 24px + 5;*/', '/*width: 29px;*/', true],
            ['/* width: 24px + 5; */', '/* width: 29px; */', true],
            ['/* width: 24px + 5 */', '/* width: 29px */', true],
            ['/* width: cvn */', '/* width: cvn */', true],
        ];
    }

    /**
     * @dataProvider providerMath
     * @covers \skewer\components\design\CssParser::calcMathString()
     *
     * @param mixed $sTestCss
     * @param mixed $sResultCss
     * @param mixed $bResult
     */
    public function testCalcMathString($sTestCss, $sResultCss, $bResult)
    {
        $sTestCssRes = $this->oCSSParser->calcMathString($sTestCss);
        $sResultCssRes = $this->oCSSParser->calcMathString($sResultCss);
        $this->assertEquals($bResult, $sTestCssRes == $sResultCssRes, $this->sErrorCssString);
    }

    /**
     * @covers \skewer\components\design\CssParser::parseFile()
     */
    public function testParseFile()
    {
        $sResultTest = $this->oCSSParser->parseFile($this->sPathCssTest);
        $sResultExample = $this->oCSSParser->parseFile($this->sPathCssExample);
        $this->assertTrue($sResultTest == $sResultExample, 'Общая проверка файлов');
    }
}
