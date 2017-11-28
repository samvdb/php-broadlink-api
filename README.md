# PHP control for Broadlink RM2 IR controllers

## note:

This is a copy/paste solution from [https://github.com/tasict/broadlink-device-php](https://github.com/tasict/broadlink-device-php).

It adds composer support and namespacing.

A quick and dirty discovery command is also available:

```php
php bin/console broadlink:discover
```


A simple PHP class for controlling IR controllers from [Broadlink](http://www.ibroadlink.com/rm/). RM (RM2, RM3 Mini referred to as RM in the codebas), A1 sensor, S1 and S2 Socket platform devices are supported. There is currently no support for the cloud API.

The protocol refer to [mjg59/python-broadlink](https://github.com/mjg59/python-broadlink/blob/master/README.md)
