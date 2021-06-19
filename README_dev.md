## Development Setup

I haven't done much effort here myself. Our Boundless Maps WP image has XDebug installed, so I usually use that for breakpoint support etc.

To test if the plugin works on the latest WP version, use the `test.docker-compose.yml` with the latest WP/MySQL images.

## Publishing

`svn` urggh.. 

1. `svn up` pull updates from WP.org
2. `svn rm/add` to add/remove files from `svn`
3. before overwriting `trunk` create a new tag from `trunk`: `svn cp trunk tags/xxx`
4. copy new files to trunk: `cp -arf documents-git/* dist/trunk`
5. check in the updates: `svn ci -m "message"`
