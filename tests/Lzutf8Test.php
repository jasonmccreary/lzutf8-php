<?php
use PHPUnit\Framework\TestCase;

use Knaya\Lzutf8\Lzutf8;
/**
 * @coversDefaultClass Knaya\Lzutf8\Lzutf8
 */
class Lzutf8Test extends TestCase
{
    private $lzutf8;

    protected function setUp(): void
    {
        $this->lzutf8 = new Lzutf8();
    }

    public function testDecompress()
    {
        $fileCompressed = __DIR__  . DIRECTORY_SEPARATOR . '256ko_compressed.txt';
        $fileUncompressed = __DIR__  . DIRECTORY_SEPARATOR . '256ko_uncompressed.txt';

        // opens compressed file
        $myfile = fopen($fileCompressed, "r") or die("Unable to open file!");
        $compressed = fread($myfile, filesize($fileCompressed));
        fclose($myfile);

        // decompress the file.
        $decompressed = $this->lzutf8->decompress($compressed);

        // open uncompressed file
        $myfile = fopen($fileUncompressed, "r") or die("Unable to open file!");
        $uncompressed = fread($myfile, filesize($fileUncompressed));
        fclose($myfile);

        // Asserting same result
        $this->assertSame($uncompressed, $decompressed);

        // codepoint sequence of 3 bytes
        // distance :: 511 = 255 + (1 * 256)
        // length :: 26 + 224 = 250
        $cp = [
            250, 1, 255
        ];
        $insertion = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

        // we have here + 32767 bytes
        // the last byte of the first window is part from codepoint sequence index::32766
        // 2 last bytes are in the next window
        $string = $this->createTestString(1, $cp, $insertion);

        $decompressed = $this->lzutf8->decompress($string);

        $this->assertSame($insertion, substr($decompressed, 32766));


        // Same CodePoint sequence
        $cp = [
            250, 1, 255
        ];

        // here only the last distance byte is the second window
        // two first bytes are in 32765 32766
        $string = $this->createTestString(2, $cp, $insertion);

        $decompressed = $this->lzutf8->decompress($string);

        // we moved the $index by one to the left
        // so we have 1 'a' in the beginning and we lose thz 'Z' at th end of string
        $insertionModified = substr($insertion, 0, -1);
        $insertionModified = 'a' . $insertionModified;
        $this->assertSame($insertionModified, substr($decompressed, 32765, 26));


        // here we have a fake codepoint sequence
        // the first distance byte has a 1 on his last bit > 128
        $cp = [
            250, 129, 255
        ];
        $string = $this->createTestString(2, $cp, $insertion);

        $decompressed = $this->lzutf8->decompress($string);
        // this codepoint sequence shouldn't be converted
        // the last three bytes are == $cp
        $lastThreeBytes = array_map('ord', str_split(substr($decompressed, 32765, 3)));
        $this->assertSame($cp, $lastThreeBytes);

        // the case with a very small string < 32767
        $decompressed = $this->lzutf8->decompress('s');
        $this->assertSame('s', $decompressed);
    }

    private function createTestString(int $locationCP, array $cp, string $insertion)
    {
        // Generate a string of 32765 bytes
        $string = str_repeat('a', (32767 - $locationCP));

        // covnert codepoint sequence to string
        $compressedStr = call_user_func_array("pack", array_merge(array("C*"), $cp));

        $string .= $compressedStr;

        $string = substr_replace($string, $insertion, 32767 - 512, 26);

        return $string;
    }
}
