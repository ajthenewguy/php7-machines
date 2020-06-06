<?php

namespace Tests\Unit;

use Machines\Regex;
use PHPUnit\Framework\TestCase;

class RegexTest extends TestCase {

    public function testGrep()
    {
        $array = ['2.0', '3', '4.1', 5, 6.2];
        $fl_array = preg_grep("/^(\d+)?\.\d+$/", $array);

        $r = new Regex('/^(\d+)?\.\d+$/');
        $expected = [
            0 => '2.0',
            2 => '4.1', 
            4 => 6.2
        ];

        $this->assertEquals($expected, $r->grep($array));
    }

    public function testMatch()
    {
        $r = new Regex('/[a-z]/i');
        $expected = [['a', 1]];

        $this->assertEquals($expected, $r->match('1a0F3', PREG_OFFSET_CAPTURE));

        $r = new Regex('/(foo)(bar)(baz)/');
        $expected = [
            'foobarbaz',
            'foo',
            'bar',
            'baz'
        ];

        $this->assertEquals($expected, $r->match('foobarbaz'));
    }

    public function testMatchAll()
    {
        $r = new Regex('/[a-z]/i');
        $expected = [
            [
                ['a', 1],
                ['F', 3]
            ]
        ];

        $this->assertEquals($expected, $r->matchAll('1a0F3', PREG_OFFSET_CAPTURE));

        $r = new Regex('/(foo)(bar)(baz)/');
        $expected = [
            ['foobarbaz'],
            ['foo'],
            ['bar'],
            ['baz']
        ];

        $this->assertEquals($expected, $r->matchAll('foobarbaz'));
    }

    public function testReplace()
    {
        $r = new Regex('/[a-z]/i');

        $this->assertEquals('103', $r->replace('', '1a0F3'));
    }

    public function testSplit()
    {
        $r = new Regex('/[\s,]+/');
        $expected = [
            'hypertext',
            'language',
            'programming'
        ];

        $this->assertEquals($expected, $r->split('hypertext language, programming'));
    }

    public function testTest()
    {
        $r = new Regex('/[^def]/');

        $this->assertTrue($r->test('abcdef'));
        $this->assertFalse($r->test('abcdef', 3));
    }
}