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
 * @link      http://ganbarodigital.github.io/php-mv-local-filesystem
 */

namespace GanbaroDigital\S3Filesystem\V1;

use GanbaroDigital\Filesystem\V1\FileInfo;

/**
 * represents a file inside an S3 bucket
 */
class S3FileInfo extends S3PathInfo implements FileInfo
{
    /**
     * the S3 result that we're wrapping
     *
     * @var ??
     */
    protected $fileInfo;

    /**
     * our constructor
     *
     * @param string $fullPath
     *        the full filesystem path
     * @param SplFileInfo|null $fileInfo
     *        a pre-existing SplFileInfo
     */
    public function __construct(string $fullPath, array $fileInfo)
    {
        parent::__construct($fullPath);
        $this->fileInfo = $fileInfo;
    }

    /**
     * what is the filename, without any parent folders?
     *
     * @return string
     */
    public function getBasename() : string
    {
        return basename($this->fileInfo['Key']);
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
        return dirname($this->fileInfo['Key']);
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
        return pathinfo($this->fileInfo['Key'], PATHINFO_EXTENSION);
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
        return $this->fileInfo['Key'];
    }

    /**
     * what is the real path to this file on the filesystem?
     *
     * @return string
     */
    public function getRealPath() : string
    {
        return $this->fileInfo['Key'];
    }

    /**
     * how big is this file?
     *
     * @return int
     */
    public function getSize() : int
    {
        return $this->fileInfo->getSize();
    }

    /**
     * can we execute this file?
     *
     * @return bool
     */
    public function isExecutable() : bool
    {
        return false;
    }

    /**
     * is this a real file on the filesystem?
     *
     * @return bool
     *         - `false` if this is a symlink
     *         - `false` if this is a folder
     *         - `true` otherwise
     */
    public function isFile() : bool
    {
        return !$this->isFolder();
    }

    /**
     * is this a folder on the filesystem?
     *
     * @return bool
     *         - `false` if this is a file
     *         - `false` if this is a symlink
     *         - `true` otherwise
     */
    public function isFolder() : bool
    {
        return $this instanceof S3FilesystemContents;
    }

    /**
     * is this a symlink on the filesystem?
     *
     * @return bool
     */
    public function isLink() : bool
    {
        return false;
    }

    /**
     * can we read this file?
     *
     * @return bool
     */
    public function isReadable() : bool
    {
        return true;
    }

    /**
     * can we write into this file?
     *
     * @return bool
     */
    public function isWritable() : bool
    {
        return true;
    }

    public function getETag() : string
    {
        return $this->fileInfo['ETag'] ?? '';
    }
}