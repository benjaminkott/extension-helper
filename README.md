# TYPO3 Extension Development Helper

## Install

```
$ composer require bk2k/extension-helper --dev
```

## Commands

### `release:create`

This is a wrapper command that calls different commands `version:set` and
`changelog:create` in sequence.

```
$ php bin/extension-helper release:create <next version number>
$ php bin/extension-helper release:create 1.0.0
```

### `changelog:create`

This command generates a changelog from your git log.

```
$ php bin/extension-helper changelog:create <next version number>
$ php bin/extension-helper changelog:create 1.0.0
```

### `version:set`

This command updates the version of your extension in the predefined files.
For now it will update the `ext_emconf.php` and `Documentation\Settings.yaml`

```
$ php bin/extension-helper version:set <next version number>
$ php bin/extension-helper version:set 1.0.0
```
