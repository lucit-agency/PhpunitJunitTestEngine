## Phpunit Junit test engine for [Arcanist](https://github.com/phacility/arcanist) and [Phabricator](https://secure.phabricator.com)

A unit test engine that is essentially forked from the original phpunit test engine that is bundled with Phabricator.  This specific engine supports the latest version of phpunit and the cli option of `--log-junit`

## Background
`--log-json`, which is required by the existing phpunit test engine in Phabricator is not supported after PHPUnit 6. [See Background Here](https://github.com/sebastianbergmann/phpunit/issues/2499) and this version makes use of `--log-junit`

This new engine is a drop-in replacement that supports the latest versions of phpunit

## Prerequisites
This requires `phpunit` and `arcanist` to be installed locally

## Installing

1. Clone this repo somewhere in your path
2. In the repo you want to run unit tests from, edit the `.arcconfig` file with settings like the following

```
{
    ...
    "unit.engine": "PhpunitJunitTestEngine",
    "unit.phpunit.binary": "./vendor/bin/phpunit",
    "load": [ 
        ....
        "phpunitjunittestengine/src"
    ]
}
```

A note on these settings
- `"unit.engine": "PhpunitJunitTestEngine"` this is the new name of the engine
- `"unit.phpunit.binary": "./vendor/bin/phpunit",` we use composer, so phpunit is inside the vendor dir.  If it is somewhere else, then specify that
- `"load": [ 
        ....
        "phpunitjunittestengine/src"
    ]` - If this does not work, specify the full path e.g. `/usr/local/src/phpunitjunittestengine/src`


## License
All source code is licensed under the [Apache 2.0 license](LICENSE), the same license as for the Arcanist project.

## Lucit
Lucit is the company behind Layout : The application that connects big-ticket inventory applications (Automotive, Ag, Rec, Real Estate) to digital billboards, in real-time.

We stream inventory - direct, in real-time to digital billboards, anywhere. https://lucit.cc
