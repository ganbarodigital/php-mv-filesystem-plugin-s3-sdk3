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
use GanbaroDigital\S3Filesystem\V1\Internal;
use GanbaroDigital\MissingBits\ErrorResponders\OnFatal;

/**
 * represents an S3 bucket
 */
class S3Filesystem implements Filesystem
{
    /**
     * our AWS SDK client
     * @var S3Client
     */
    protected $s3Client;

    /**
     * which bucket are we searching?
     * @var string
     */
    protected $bucketName;

    /**
     * what's in the S3 bucket?
     *
     * we cache this here to avoid downloading it all the time
     *
     * @var S3FilesystemContents
     */
    protected $contents;

    /**
     * our constructor
     *
     * @param S3Client $s3Client
     *        how we will talk to S3
     * @param string $bucketName
     *        which bucket are we representing?
     */
    public function __construct(S3Client $s3Client, string $bucketName)
    {
        $this->s3Client = $s3Client;
        $this->bucketName = $bucketName;

        // we go and get the contents straight away
        $this->contents = Helpers\GetFilesystemContents::from($this);
    }

    /**
     * get access to our S3Client
     *
     * @return S3Client
     */
    public function getClient() : S3Client
    {
        return $this->s3Client;
    }

    public function getBucketName() : string
    {
        return $this->bucketName;
    }

    // ==================================================================
    //
    // Filesystem API
    //
    // ------------------------------------------------------------------

    /**
     * retrieve a folder from the filesystem
     *
     * @param  string $fullPath
     *         path to the folder
     * @param  OnFatal $onFailure
     *         what do we do if we do not have the folder?
     * @return FilesystemContents
     */
    public function getFolder(string $fullPath, OnFatal $onFatal) : FilesystemContents
    {
        return Internal\GetFileInfoByPath::from($this->contents, $fullPath, $onFatal);
    }

    /**
     * get detailed information about something on the filesystem
     *
     * @param  string $fullPath
     *         the full path to the thing you are interested in
     * @param  OnFatal $onFatal
     *         what do we do if we do not have it?
     * @return FileInfo
     */
    public function getFileInfo(string $fullPath, OnFatal $onFatal) : FileInfo
    {
        return Internal\GetFileInfoByPath::from($this->contents, $fullPath, $onFatal);
    }

    // ==================================================================
    //
    // PluginProvider interface
    //
    // ------------------------------------------------------------------

    /**
     * return the __NAMESPACE__ for classes provided by this plugin
     *
     * @return string
     *         the __NAMESPACE__ constant
     */
    public function getPluginNamespace() : string
    {
        return __NAMESPACE__;
    }
}