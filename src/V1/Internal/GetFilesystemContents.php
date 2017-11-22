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

use GanbaroDigital\Filesystem\V1\PathInfo;
use GanbaroDigital\Filesystem\V1\TypeConverters;
use GanbaroDigital\MissingBits\ErrorResponders\OnFatal;
use GanbaroDigital\S3Filesystem\V1\Internal;
use GanbaroDigital\S3Filesystem\V1\S3FileInfo;
use GanbaroDigital\S3Filesystem\V1\S3Filesystem;
use GanbaroDigital\S3Filesystem\V1\S3FilesystemContents;

/**
 * get a listing of the contents of an S3 bucket
 */
class GetFilesystemContents
{
    /**
     * get a listing of the contents of an S3 bucket
     *
     * this will create a local cache of *everything* up in the bucket!
     *
     * @param  S3Filesystem $fs
     *         the filesystem to search
     * @param  string|PathInfo $path
     *         the path to search for
     * @return S3FilesystemContents
     */
    public static function from(S3Filesystem $fs, $path) : S3FilesystemContents
    {
        // shorthand
        $fsPrefix = $fs->getFilesystemPrefix();
        $pathInfo = TypeConverters\ToPathInfo::from($path);

        $retval = new S3FilesystemContents($pathInfo, [ 'Key' => '' ]);
        $continuationToken = null;

        do  {
            $result = Internal\CallListObjectsV2::using($fs, $pathInfo->getFullPath(), $continuationToken);
            $continuationToken = $result['NextContinuationToken'] ?? null;

            foreach ($result['Contents'] ?? [] as $bucketObject) {
                static::addContent($retval, $bucketObject);
            }
        } while ($continuationToken !== null);

        // all done
        return $retval;
    }

    /**
     * add a single S3Result 'Content' item to our filesystem of contents
     *
     * @param S3FilesystemContents $contents
     *        the contents we are adding to
     * @param array $bucketObject
     *        the item we want to add
     */
    protected static function addContent(S3FilesystemContents $contents, array $bucketObject)
    {
        $dirname = dirname($bucketObject['Key']);
        $parts = explode("/", $dirname);
        if ($parts[0] == '') {
            array_shift($parts);
        }

        // what do we do if we can't find the folder we're looking for?
        $onFatal = new OnFatal(function($path, $reason) {
            throw new \RuntimeException("Cannot find $path: $reason");
        });

        // start from the top of the contents tree
        $dest = $contents;

        // make sure all the parent folders exist
        foreach ($parts as $part) {
            if (!$dest->hasFolder($part)) {
                $pathSoFar = $contents->getFullPath() . "/{$part}";
                $dest->trackFolder($part, ['Key' => $pathSoFar ]);
            }
            $dest = $dest->getFolder($part, $onFatal);
        }

        // now that the parent folders exist, we can safely
        // track the file itself
        $dest->trackFile(basename($bucketObject['Key']), $bucketObject);
    }
}