# CHANGELOG

## develop branch

### New

* Added values to represent things on the filesystem
  - added `S3PathInfo`
  - added `S3FileInfo`
  - added `S3Filesystem`
  - added `S3FilesystemContents`
* Added support for converting to the local FileInfo type
  - added `TypeConverters\ToFileInfo`
* Added support for iterating this filesystem
  - added `GetContentsIterator`
* Added internal helpers
  - added `CallListObjectsV2'
  - added `GetFileInfoByPath`
  - added `GetFilesystemContents`
* Added support for DI containers
  - added `S3FilesystemFactory`
* Added basic filesystem operations
  - added `GetFileContents` operation
  - added `PutFileContents` operation
* Added key/value metadata support
  - added `GetFileMetadata` operation
  - added `PutFileMetadata` operation