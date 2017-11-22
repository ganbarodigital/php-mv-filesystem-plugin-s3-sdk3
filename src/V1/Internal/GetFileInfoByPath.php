<?php

/**
 * Copyright (c) 2017-present Ganbaro Digital Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Libraries
 * @package   S3Filesystem\V1\Internal
 * @author    Stuart Herbert <stuherbert@ganbarodigital.com>
 * @copyright 2017-present Ganbaro Digital Ltd www.ganbarodigital.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://ganbarodigital.github.io/php-mv-filesystem-plugin-s3-sdk3
 */

namespace GanbaroDigital\S3Filesystem\V1\Internal;

use GanbaroDigital\MissingBits\ErrorResponders\OnFatal;
use GanbaroDigital\Filesystem\V1\PathInfo;
use GanbaroDigital\Filesystem\V1\TypeConverters;
use GanbaroDigital\S3Filesystem\V1\S3FileInfo;
use GanbaroDigital\S3Filesystem\V1\S3Filesystem;
use GanbaroDigital\S3Filesystem\V1\S3FilesystemContents;

/**
 * find a file or folder by its path
 */
class GetFileInfoByPath
{
    /**
     * find a file or folder by its path
     *
     * @param  S3FilesystemContents $contents
     *         what's in the S3 bucket
     * @param  string|PathInfo $path
     *         the path to search
     * @param  OnFatal $onFatal
     *         what do we do if we cannot create the iterator?
     * @return S3FileInfo
     *         the file or folder at that path
     */
    public static function from(S3FilesystemContents $contents, $path, OnFatal $onFatal) : S3FileInfo
    {
        // what are we looking at?
        $pathInfo = TypeConverters\ToPathInfo::from($path);
        $fullPath = $pathInfo->getFullPath();

        // special case
        if ($fullPath == '/') {
            return $contents;
        }

        // general case
        $parts = explode("/", $fullPath);
        if ($parts[0] == '') {
            array_shift($parts);
        }

        $retval = $contents;
        $seen = $pathInfo->getFilesystemPrefix();
        foreach ($parts as $part) {
            if (!$retval->hasFolder($part)) {
                throw $onFatal("{$seen}/{$part}", "path not found");
            }
            $retval = $retval->getFolder($part, $onFatal);
            $seen .= "/{$part}";
        }

        // all done
        return $retval;
    }
}