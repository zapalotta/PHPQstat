#!/bin/bash
# Import accounting file into database
# ATTN: runs quite long and truncates table on start...

# FIXME: source config file and take paths form there...
/usr/bin/php /path/to/phpqstat/util/import_accountingdata.php < /path/to/sge/cell/common/accounting 
