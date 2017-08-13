# PHP-IPFS
WARNING: This is a WIP. Code duplication and untested code abounds
## Getting Started
### Installing
// TODO
### How to Use
To use an API function, call it using camel case:
log/level becomes logLevel.

```Ipfs::logLevel();```

### Testing
// TODO
## Examples
### Check for peers hosting a file you have a local copy of

```
use PhpIpfs\Ipfs;
$filePath = '/path/to/local/file';
$getHashOnly = true;
$hashOfFile = Ipfs::add($filePath, $getHashOnly);
$peersWithFile = Ipfs::dhtFindprovs($hashOfFile);
```