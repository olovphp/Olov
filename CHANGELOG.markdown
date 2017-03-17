# Change Log
All notable (and not so notable) changes to this project from this day (Thu May 5th, 2016)  
will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added
- More filters: `title`, `upper`, `lower`, `round`, `num_format`, `join`, `default`, `keys`, `reverse`, `sort`

## [1.1.0] - 2016-05-05
### Added
- Support for multiple template folder paths added.
- `Olov\Engine::addPath()` method added.
- `esc` filter now takes `context` argument (html|attr|js|css)
- Olov now escapes array vars recursively.
- Added `Olov\Encoder`; a helper class for the `esc` filter.
- Added `Olov` class. You can now create a new instance of the Olov\Engine like this:
```
$o = Olov::o($folder_path);
```

### Changed
- `Olov\Engine::setPath()` method now takes array|string $path argument in addition to original behaviour. You can 
now set one folder path (as string) or multiple folder paths with an array argument.
 
*NOTE: `Olov\Engine::setPath()` always resets the engine's base paths (out with the old, in with new).*


## [1.1.1] - 2016-05-07
### Bug Fix
- Fixed bug that was causing Olov to escape non-alphanum chars in href & src attribs. Olov now uses 
`filter_var` with the `FILTER_VALIDATE_URL` flag. If an `href|src` attribute value  is invalid, it will not be set. Ok.



