<?php

/**
 * @see       https://github.com/mezzio/mezzio-helpers for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-helpers/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-helpers/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Helper\BodyParams;

use Mezzio\Helper\BodyParams\FormUrlEncodedStrategy;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class FormUrlEncodedStrategyTest extends TestCase
{
    public function setUp()
    {
        $this->strategy = new FormUrlEncodedStrategy();
    }

    public function formContentTypes()
    {
        return [
            ['application/x-www-form-urlencoded'],
            ['application/x-www-form-urlencoded; charset=utf-8'],
            ['application/x-www-form-urlencoded;charset=utf-8'],
            ['application/x-www-form-urlencoded;Charset="utf-8"'],
        ];
    }

    /**
     * @dataProvider formContentTypes
     */
    public function testMatchesFormUrlencodedTypes($contentType)
    {
        $this->assertTrue($this->strategy->match($contentType));
    }

    public function invalidContentTypes()
    {
        return [
            ['application/x-www-form-urlencoded2'],
            ['application/x-www-form-urlencoded-too'],
            ['form/multipart'],
            ['application/json'],
        ];
    }

    /**
     * @dataProvider invalidContentTypes
     */
    public function testDoesNotMatchNonFormUrlencodedTypes($contentType)
    {
        $this->assertFalse($this->strategy->match($contentType));
    }

    public function testParseReturnsOriginalRequest()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getParsedBody()->willReturn(['test' => 'value']);

        $this->assertSame($request->reveal(), $this->strategy->parse($request->reveal()));
    }

    public function testParseReturnsOriginalRequestIfBodyIsEmpty()
    {
        $stream = $this->prophesize(StreamInterface::class);
        $stream->__toString()->willReturn('');

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getParsedBody()->willReturn(null);
        $request->getBody()->willReturn($stream);

        $this->assertSame($request->reveal(), $this->strategy->parse($request->reveal()));
    }

    public function testParseReturnsNewRequest()
    {
        $body = 'foo=bar&bar=foo';

        $stream = $this->prophesize(StreamInterface::class);
        $stream->__toString()->shouldBeCalled()->willReturn($body);

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getParsedBody()->willReturn(null);
        $request->getBody()->willReturn($stream->reveal());
        $request->withParsedBody(['foo' => 'bar', 'bar' => 'foo'])->shouldBeCalled()->willReturn($request->reveal());
        $this->assertSame($request->reveal(), $this->strategy->parse($request->reveal()));
    }
}
