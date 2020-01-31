<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace VanillaTests\Library\Vanilla\Utility;

use Vanilla\Utility\UrlUtils;
use PHPUnit\Framework\TestCase;

/**
 * Class UrlUtilsTest Tests domainAsAscii() function.
 *
 * @package VanillaTests\Library\Vanilla\Utility
 */
class UrlUtilsTest extends TestCase {

    /**
     * @var array saves $_SERVER values before the test.
     */
    private static $originalServer;

    /**
     * Set $_SERVER values that are required for http_build_url().
     */
    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();
        self::$originalServer = $_SERVER;
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SERVER_NAME'] = 'vanilla.test';
        $_SERVER['REQUEST_URI'] = '/';
    }

    /**
     * Reset $_SERVER values.
     */
    public static function tearDownAfterClass(): void {
        parent::tearDownAfterClass();
        $_SERVER = self::$originalServer;
    }

    /**
     * Provide data for testing the testDomainAsAscii function.
     *
     * @return array of valid domains to test.
     */
    public function provideUnicodeDomains(): array {
        $result = [
            'Valid ASCII domain' => ['www.vanillaforums.com', 'http://www.vanillaforums.com'],
            'Valid Unicode domain' => ['http://www.goοgle.com/test', 'http://www.xn--gogle-sce.com/test'],
            'Valid ASCII domain with fragment' => ['https://www.google.com/path/to/page?query=string#fragment', 'https://www.google.com/path/to/page?query=string#fragment'],
            'Valid Unicode domain with fragment' => [
                'https://www.goοgle.com/path/to/page?query=string#fragment', 'https://www.xn--gogle-sce.com/path/to/page?query=string#fragment'
            ],
            'Valid url' => ['http://www.vanillaforums.com', 'http://www.vanillaforums.com'],
            'Valid punycoded url' => ['xn--gogle-sce.com', 'http://xn--gogle-sce.com'],
        ];
        return $result;
    }

    /**
     * Test the domainAsAscii() function.
     *
     * @param string $domain Test domain.
     * @param string $punyEncoded Domain converted to IDNA ASCII.
     * @dataProvider provideUnicodeDomains
     */
    public function testDomainAsAscii($domain, $punyEncoded) {
        $result = UrlUtils::domainAsAscii($domain);
        $this->assertEquals($result, $punyEncoded);
    }

    /**
     * Test the domainAsAscii() function using a domain with invalid character.
     */
    public function testInvalidDomainAsAscii() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Domain Invalid.');

        UrlUtils::domainAsAscii('//goo�gle.com/');
    }
}
