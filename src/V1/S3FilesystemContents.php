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

use Aws\S3\S3Client;
use GanbaroDigital\Filesystem\V1\FileInfo;
use GanbaroDigital\Filesystem\V1\Filesystem;
use GanbaroDigital\Filesystem\V1\FilesystemContents;
use GanbaroDigital\Filesystem\V1\PathInfo;
use GanbaroDigital\Filesystem\V1\TypeConverters;
use GanbaroDigital\S3Filesystem\V1\Internal;
use GanbaroDigital\MissingBits\ErrorResponders\OnFatal;

/**
 * represents the (cached) contents of an S3 bucket
 *
 * we use this to cache S3 objects, because it's too expensive to go
 * and get them in real time all the time
 *
 * a big part of the problem is that we can't get a list of folders
 * in S3, because S3 doesn't really have folders
 */
class S3FilesystemContents extends S3FileInfo implements FilesystemContents
{
    /**
     * a list of things we have on the filesystem
     *
     * @var array
     */
    private $contents = [];

    // ==================================================================
    //
    // Self
    //
    // ------------------------------------------------------------------

    /**
     * return a list of the filenames at the current level
     *
     * @return string[]
     */
    public function getFilenames() : array
    {
        return array_keys($this->contents);
    }

    /**
     * get further information about a specific path at the current level
     *
     * @param  string $filename
     *         the filename you are interested in
     * @param  OnFatal $onFatal
     *         what do we do if we do not have the file?
     * @return FileInfo
     */
    public function getFileInfo(string $filename, OnFatal $onFatal) : FileInfo
    {
        // everything we store is already a FileInfo
        return $this->contents[$filename];
    }

    // ==================================================================
    //
    // Files
    //
    // ------------------------------------------------------------------

    /**
     * get further information about a specific file at the current level
     *
     * @param  string $filename
     *         the filename you are interested in
     * @param  OnFatal $onFatal
     *         what do we do if we do not have the file?
     * @return FileInfo
     */
    public function getFile(string $filename, OnFatal $onFatal) : FileInfo
    {
        if (!$this->hasFile($filename)) {
            throw $onFatal($filename, 'file not found');
        }
        return $this->contents[$filename];
    }

    /**
     * do we have a file called $filename at the current level?
     *
     * @param  string $filename
     *         what is the file we are looking for?
     * @return bool
     *         - `true` if $filename exists at the current level,
     *           and it is not a folder
     *         - `false` otherwise
     */
    public function hasFile(string $filename) : bool
    {
        return isset($this->contents[$filename]) && !$this->contents[$filename] instanceof S3FilesystemContents;
    }

    /**
     * add a child file to the list that we know about
     *
     * @param  string $filename
     *         what is the file that we are adding?
     * @param  mixed $fileDetails
     *         further information about the file, specific to the individual
     *         filesystem
     * @return void
     */
    public function trackFile(string $filename, $fileDetails)
    {
        $this->contents[$filename] = new S3FileInfo(
            $this->withChild($filename), $fileDetails
        );
    }

    // ==================================================================
    //
    // Folders
    //
    // ------------------------------------------------------------------

    /**
     * get the container for a child folder
     *
     * @param  string $filename
     *         what is the folder that we are looking for?
     * @param  OnFatal $onFatal
     *         what do we do if we do not have the folder?
     * @return FilesystemContents
     */
    public function getFolder(string $filename, OnFatal $onFatal) : FilesystemContents
    {
        if (!$this->hasFolder($filename)) {
            throw $onFatal($filename, 'folder not found');
        }

        return $this->contents[$filename];
    }

    /**
     * do we have a folder called $filename at the current level?
     *
     * @param  string $filename
     *         what is the folder we are looking for?
     * @return bool
     *         - `true` if $filename exists at the current level,
     *           and it is a folder
     *         - `false` otherwise
     */
    public function hasFolder(string $filename) : bool
    {
        return isset($this->contents[$filename]) && $this->contents[$filename] instanceof S3FilesystemContents;
    }

    /**
     * add a child folder to the list that we are tracking
     *
     * @param string $filename
     *        what is the folder that we are adding?
     * @param mixed $fileInfo
     *        further information about the folder, specific to the individual
     *        filesystem
     */
    public function trackFolder(string $filename, $fileInfo)
    {
        $fullPath = (string)$this->withChild($filename);

        $this->contents[$filename] = new S3FilesystemContents(
            $fullPath,
            [
                'Key' => $fullPath
            ]
        );
        return $this->contents[$filename];
    }
}