QuikLog
=======
-logging system for PHP

Features
-
* PHP error / file / mail outputs
* type / text matching filters
* simple, yet powerful configuration capabilities
* a lightweight system that is quickly loaded and adds next to no weight to your project
* seamlessly supports custom extensions

Usage
-
* copy quiklog.php and quiklog.ini to your project
* modify quiklog.ini to configure the logger
* include the quiklog.php file somewhere
* call `qlogconf( 'quiklog.ini' )` to initialize the logger
* log with qlog/qlog_* functions (example in test.php)

Usage (advanced)
-
* initialize a logger instance with one of the static functions (fromIniFile/fromIniString)
* handle initialization errors by checking if returned value `is_string`
* add calls to the logging methods (log/logInfo/logWarning/logError)
