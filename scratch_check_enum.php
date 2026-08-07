<?php
echo DB::select("SHOW COLUMNS FROM users LIKE 'account_type'")[0]->Type;
