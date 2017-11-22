# CHANGELOG

## develop branch

### New

* Added values to represent things on the filesystem
  - added `S3PathInfo`
  - added `S3FileInfo`
  - added `S3Filesystem`
  - added `S3FilesystemContents`
* Added support for iterating this filesystem
  - added `GetContentsIterator`
* Added internal helpers
  - added `CallListObjectsV2'
  - added `GetFileInfoByPath`
  - added `GetFilesystemContents`