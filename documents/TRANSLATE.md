# Translate

playSMS is using a layered structure for the language translations. For each plugins there is its own translation files and folders.


## Create new translation

To create a NEW translation of playSMS, you will have to copy the translation template into your destination language.

For example, to translate from English (en_US) to catalan (ca_ES) you should do the following for every component you want to translate.

For common strings

```
cp -a plugin/language/en_US plugin/language/ca_ES
cp -a plugin/language/messages.pot plugin/language/ca_ES/LC_MESSAGES/messages.po
```

For plugin themes, gateway, feature and core:

```
cp -a plugin/type_of_plugin/name_of_plugin/language/en_US plugin/type_of_plugin/name_of_plugin/language/ca_ES
cp -a plugin/type_of_plugin/name_of_plugin/language/messages.pot plugin/type_of_plugin/name_of_plugin/language/ca_ES/LC_MESSAGES/messages.po
```

You will have to translate the .po file, you can use poedit, lokalize or any other software to translate.

IMPORTANT:

To ease you up there are scripts to help you with the translation:

- `backup-language-files.sh` creates a backup of existing language files
- `create-new-language.sh` creates a tgz that contains all the files to translate the whole playsms interface
- `restore-language-files.sh` restores the language files (po) from the backup or newly created files


## Update the translation

To improve/finish the translations into your language, you will have to go into every folder of the program to get the .po files to be translated.

Once you've translated the .po files with poedit, lokalize or any other tool you just have to replace them into the destination folder.

Go to the section REGENERATING THE MO FILES for the rest.


## Update .pot files

When new strings are added to the system, on any of the themes or plugins, updating the pot files will allow the users to update their translations.

The .pot files are the templates on with every translation is based.

There's a contrib script in `contrib/tools/language/` for this.

Update .pot files in your playSMS web root:

```
./1-update-pot-files.sh /var/www/playsms
```


## Merge .pot files with .po files

Once we have updated the pot files, we can merge them with the translations files or .po files so the translators have only to worry about translating.

There's a contrib script in `contrib/tools/language/` for this.

Update .po files in your playSMS web root:

```
./2-merge-existing-po-files.sh /var/www/playsms
```

## Regenerate .mo files

Until you regenerate the mo files, the translations won't be visible in the web interfaces.

To accomplish that, you will have to use `msgfmt`

For any time there are new .po files their correspondant .mo files need to be generated.

There's a contrib script in `contrib/tools/language/` for this.

Regenerate .mo files in your playSMS web root:

```
./3-regenerating-mo-files.sh /var/www/playsms
```

## Translators

Language | Maintainer
-------- | ----------
ca_ES    | Joan (aseques)
da_DK    | Jens Hyllegaard (Hyllegaard)
de_DE    | Andre Gronwald (andre)
es_VE    | Alfredo Hernandez (alfredo)
fr_FR    | Emmanuel Chanson (emmanuel), Elingui P. Uriel (elinguiuriel)
id_ID    | Anton Raharja (anton), Andry Rachmadany (hangsbreaker)
pt_BR    | Lucas Teixeira (lucastx), Gabriel Schanuel (gschanuel)
pt_PT    | Delio Gois (dmtg)
ru_RU    | Alexey Dvoryanchikov (dvoryanchikov)
zh_CN    | Frank Van Caenegem (fvancaen)
