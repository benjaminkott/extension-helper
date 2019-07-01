# TYPO3 Extension Development Helper

## Install

```bash
composer require bk2k/extension-helper --dev
```

## Commands

### `release:create`

This is a wrapper command that calls different commands `version:set`,
`changelog:create`, `release:publish` and `archive:create` in sequence.

```bash
composer release:create <next version number>
composer release:create 1.0.0
```

### `release:publish`

This command will commit all uncommitted files in the working directory,
and adds the version number as tag.

```bash
composer release:publish <next version number>
composer release:publish 1.0.0
```

### `changelog:create`

This command generates a changelog from your git log.

```bash
composer changelog:create <next version number>
composer changelog:create 1.0.0
```

### `version:set`

This command updates the version of your extension in the predefined files.
For now it will update files:

- `Documentation\Settings.cfg`
- `Documentation\Settings.yaml`
- `ext_emconf.php`

```bash
composer version:set <next version number> --dev
```

```bash
# Set version to 1.0.0
composer version:set 1.0.0

# Set version to 1.0.0-dev
composer version:set 1.0.0 --dev
```

### `archive:create`

This will generate a zip archive for the current version.

With the optional parameter you can set a specific version number the archive
should be created for. The script will fail if you provide a version number
and the tag does not exist.

```bash
# packagename_<branch>-<revision>.zip
composer archive:create

# packagename_1.0.0.zip
composer archive:create 1.0.0
```

### Legacy usage

Old style usage is still working as before by calling `php bin/extension-helper`
instead of `composer`.

```bash
php bin/extension-helper release:create 1.0.0
```
