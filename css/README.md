# Services catalog skin customization 

The Formcreator plugin allows to customize the services catalog interface with some extra CSS. This documentation file will help to understand how-to... 

The entity configuration parameter *Extra CSS file URI* allows to define the path to an extra CSS file. If this parameter is not empty, then the plugin will load the specified file as an extra stylesheet and it will consider that the provided path is relative to the plugin installation directory.

An example exists in the *css* directory of the plugin. You can use it : 
 - as is. Declare the extra CSS parameter as *css/extra-styles.css*
 - or copy the *extra-styles.css* and *_alternate-logo.png* to another directory under the plugin installation directory. Set the *Extra CSS file URI* parameter of the concerned entity accordingly to the files location.