# README

## Notes

- This directory and its content should be located outside web root
- This directory and its content should NOT be accessible directly from web

## Directory Purposes

- `application/` holds PHP files
- `composer/` holds composer packages
- `custom/application/` holds configs and templates custom/overrides
- `tmp/application/` holds temporary files for plugins when they need it

## Directory Structure

```
application/
  config.php (put this in custom/application/configs/ instead)
  app/
  lib/
  plugin/
    core/
    feature/
    gateway/
    language/
    themes/

composer/
  vendor/
  autoload.php

custom/
  application/
    configs/
      config.php (put here instead of in application/)
      core/
      feature/
      gateway/
      language/
      themes/
    templates/
      core/
      feature/
      gateway/

tmp/
  application/
    plugin/
      core/
      feature/
      gateway/
      language/
      themes/
```
-