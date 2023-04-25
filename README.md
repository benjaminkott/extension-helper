# TYPO3 Extension Development Helper

## Install

```
$ composer require bk2k/extension-helper --dev
```

## Commands

### `release:create`

This is a wrapper command that calls different commands `version:set`,
`changelog:create` and `release:publish` in sequence.

```
$ php bin/extension-helper release:create <next version number>
$ php bin/extension-helper release:create 1.0.0
```

### `release:publish`

This command will commit all uncommitted files in the working directory,
and adds the version number as tag.

```
$ php bin/extension-helper release:publish <next version number>
$ php bin/extension-helper release:publish 1.0.0
```

### `changelog:create`

This command generates a changelog from your git log.

```
$ php bin/extension-helper changelog:create <next version number>
$ php bin/extension-helper changelog:create 1.0.0
```

### `version:set`

This command updates the version of your extension in the predefined files.
For now it will update files:

- `Documentation\Settings.cfg`
- `Documentation\Settings.yaml`
- `ext_emconf.php`

```
$ php bin/extension-helper version:set <next version number> --dev
```

```
# Set version to 1.0.0
$ php bin/extension-helper version:set 1.0.0
```

```
# Set version to 1.0.0-dev
$ php bin/extension-helper version:set 1.0.0 --dev
```

### `archive:create`

This will generate a zip archive for the current version.

With the optional parameter you can set a specific version number the archive
should be created for. The script will fail if you provide a version number
and the tag does not exist.

```
# packagename_<branch>-<revision>.zip
$ php bin/extension-helper archive:create

# packagename_1.0.0.zip
$ php bin/extension-helper archive:create 1.0.0
```
