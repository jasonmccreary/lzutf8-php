<?php
/**
 * Lzutf8.php
 *
 * Lzutf8 decompressor based on string.
 *
 *
 * @package    Lzutf8
 * @author     KNY
 * @copyright  2020 <kny.contact@gmail.com>
 * @license    https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt  GPLv2
 */
namespace Knaya\Lzutf8;

/**
 * Class Lzutf8
 * @package Lzutf8
 */
class Lzutf8
{
    public function decompress(string $text): string
    {
        $totalStrLen = strlen($text);
        if ($totalStrLen > 0x7FFF) {
            $utfTank = str_split($text, 0x7FFF);
            $utfString = $utfTank[0];

            array_shift($utfTank);

            $stringLen = 0x7FFF;
        } else {
            $utfTank = [];
            $utfString = $text;
            $stringLen = $totalStrLen;
        }

        $utfCacheArr = [];
        $utfCache = '';

        $firstIndex = 0;

        do {
            for ($index = $firstIndex; $index < $stringLen; $index += 1) {
                if (isset($utfCacheArr[$utfString[$index]])) {
                    if ($utfCacheArr[$utfString[$index]] === false) {
                        continue;
                    }
                    $binaryValue = $utfCacheArr[$utfString[$index]];
                } else {
                    $binaryValue = ord($utfString[$index]);
                    $utfCacheArr[$utfString[$index]] = $binaryValue;
                }

                if ($binaryValue < 0xC0) {
                    $utfCacheArr[$utfString[$index]] = false;
                    continue;
                }

                $distanceByteNum = 1;
                $replaceSecLen = $binaryValue - 0xC0;

                if ($binaryValue > 0xE0) {
                    $distanceByteNum += 1;
                    $replaceSecLen -= 0x20;
                }

                if (!isset($utfString[$index + 1])) {
                    break;
                }

                $firstByte = ord($utfString[$index + 1]);

                if ($firstByte >= 0x80) {
                    if ($firstByte < 0xC0) {
                        $index += 1;
                    }
                    continue;
                }

                if ($distanceByteNum === 1) {
                    $distance = $firstByte;
                } else {
                    if (!isset($utfString[$index + 2])) {
                        break;
                    }
                    $secondDisByte = $utfString[$index + 2];
                    $distance = ($firstByte * 0x100) + ord($secondDisByte);
                }

                $indexRepSection = $index - $distance;

                $distanceByteNum += 1;

                $strToInsert = substr($utfString, $indexRepSection, $replaceSecLen);

                if ($replaceSecLen > $distance) {
                    $strToInsert = substr($strToInsert, 0, $distance);

                    $strToInsert = str_repeat($strToInsert, ceil($replaceSecLen/$distance));

                    $strToInsert = substr($strToInsert, 0, $replaceSecLen);
                }

                $utfString = substr_replace($utfString, $strToInsert, $index, $distanceByteNum);

                $stringLen = $stringLen + ($replaceSecLen - $distanceByteNum);
                $index = $index + ($replaceSecLen - $distanceByteNum);

                if ($index > 0xFFFE) {
                    $utfCache .= substr($utfString, 0, 0x7FFF);

                    $utfString = substr($utfString, 0x7FFF);
                    $stringLen = strlen($utfString);
                    $index -= 0x7FFF;
                }
            }

            if (isset($utfTank[0])) {
                $utfString .= $utfTank[0];
                array_shift($utfTank);

                $stringLen = strlen($utfString);
                $firstIndex = $index;
                continue;
            }
            break;
        } while (true);

        $utfCache .= $utfString;

        return $utfCache;
    }
}

