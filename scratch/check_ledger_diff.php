<?php
exec('git diff resources/views/transaction/ledger.blade.php', $t_diff);
echo "=== transaction/ledger.blade.php diff ===\n" . implode("\n", array_slice($t_diff, 0, 40)) . "\n";
