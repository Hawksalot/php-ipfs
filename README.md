# PHP-IPFS

## Getting Started
### Prerequisites
// TODO
### Installing
// TODO
### Testing
// TODO
## Examples
### Check for peers hosting a file you have a local copy of

```
use PhpIpfs\IpfsApi;
$getHashOnly = true;
$hashOfFile = IpfsApi::addLocalObjectFromPath($filePath, $getHashOnly);
$peersWithFile = IpfsApi::findRemoteProvidersByObjectHash($hashOfFile);
```