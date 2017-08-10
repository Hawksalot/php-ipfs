# PHP-IPFS
WARNING: This is a WIP, and figuring out how the API works or even which commands/flags work is quite the journey. Use at your own peril.
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