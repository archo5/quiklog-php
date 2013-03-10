QuikLog
=======
-Simple logging system for PHP

Usage
-
* copy quiklog.php and quiklog.ini to your project
* modify quiklog.ini to configure the logger
* call `qlogconf( 'quiklog.ini' )` to initialize the logger
* log with qlog/qlog_* functions (example in test.php)

Usage (advanced)
-
* initialize a logger instance with one of the static functions (fromIniFile/fromIniString)
* handle initialization errors by checking if returned value `is_string`
* add calls to the logging methods (log/logInfo/logWarning/logError)

TODO
-
* filters
* more outputs/formats
