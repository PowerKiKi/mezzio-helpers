<?php

/**
 * @see       https://github.com/mezzio/mezzio-helpers for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-helpers/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-helpers/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Helper\BodyParams;

use Mezzio\Helper\BodyParams\JsonStrategy;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class JsonStrategyTest extends TestCase
{
    public function setUp()
    {
        $this->strategy = new JsonStrategy();
    }

    public function jsonContentTypes()
    {
        return [
            ['application/json'],
            ['application/hal+json'],
            ['application/vnd.resource.v2+json'],
            ['application/json;charset=utf-8'],
            ['application/hal+json;charset=utf-8'],
            ['application/vnd.resource.v2+json;charset=utf-8'],
        ];
    }

    /**
     * @dataProvider jsonContentTypes
     */
    public function testMatchesJsonTypes($contentType)
    {
        $this->assertTrue($this->strategy->match($contentType));
    }

    public function invalidContentTypes()
    {
        return [
            ['application/json+xml'],
            ['text/javascript'],
            ['form/multipart'],
            ['application/x-www-form-urlencoded'],
        ];
    }

    /**
     * @dataProvider invalidContentTypes
     */
    public function testDoesNotMatchNonJsonTypes($contentType)
    {
        $this->assertFalse($this->strategy->match($contentType));
    }

    public function testParseReturnsNewRequest()
    {
        $body = '{"foo":"bar"}';
        $stream = $this->prophesize(StreamInterface::class);
        $stream->getContents()->willReturn($body);
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getBody()->willReturn($stream->reveal());
        $request->withAttribute('rawBody', $body)->will(function () use ($request) {
            return $request->reveal();
        });
        $request->withParsedBody(['foo' => 'bar'])->will(function () use ($request) {
            return $request->reveal();
        });

        $this->assertSame($request->reveal(), $this->strategy->parse($request->reveal()));
    }
}
