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
 * @package   S3Filesystem/V1
 * @author    Stuart Herbert <stuherbert@ganbarodigital.com>
 * @copyright 2017-present Ganbaro Digital Ltd www.ganbarodigital.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://ganbarodigital.github.io/php-mv-filesystem-plugin-s3-sdk3
 */

namespace GanbaroDigital\S3Filesystem\V1;

use GanbaroDigital\Filesystem\V1\Helpers as VfsHelpers;
use GanbaroDigital\Filesystem\V1\PathInfo;

/**
 * represents a path on a filesystem
 *
 * this is a lightweight value that only knows about the path itself,
 * and knows nothing about what the path points at
 */
class S3PathInfo implements PathInfo
{
    /**
     * what's the full filename on the filesystem?
     *
     * @var string
     */
    protected $fullPath;

    /**
     * our constructor
     *
     * @param string $fullPath
     *        the 'Key' from the S3 bucket object
     */
    public function __construct($fullPath)
    {
        $this->fullPath = $fullPath;
    }

    /**
     * what is the filename itself?
     *
     * this includes any parent folders, and the filename extension
     *
     * @return string
     */
    public function getFullPath() : string
    {
        return $this->fullPath;
    }

    /**
     * what is the filename, without any parent folders?
     *
     * @return string
     */
    public function getBasename() : string
    {
        // we only want to build this once
        static $basename = false;
        if (!$basename) {
            $basename = basename($this->fullPath);
        }

        // all done
        return $basename;
    }

    /**
     * what is the parent folder for this filename?
     *
     * returns '.' if there is no parent folder
     *
     * @return string
     */
    public function getDirname() : string
    {
        // we only want to build this once
        static $dirname = false;
        if (!$dirname) {
            $dirname = dirname($this->fullPath);
        }

        // all done
        return $dirname;
    }

    /**
     * what is the file extension of this path info?
     *
     * we return an empty string if the filename has no extension
     *
     * @return string
     */
    public function getExtension() : string
    {
        static $ext = false;

        if (!$ext) {
            $ext = pathinfo($this->fullPath, PATHINFO_EXTENSION);
        }

        return $ext;
    }

}