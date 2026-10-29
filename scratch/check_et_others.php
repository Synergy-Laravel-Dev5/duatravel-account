<?php
exec('git diff resources/views/expense_transaction/index.blade.php', $out_idx);
echo "=== expense_transaction/index diff ===\n" . implode("\n", array_slice($out_idx, 0, 40)) . "\n\n";

exec('git diff app/Http/Controllers/ExpenseTransactionController.php', $out_etc);
echo "=== ExpenseTransactionController diff (first 50 lines) ===\n" . implode("\n", array_slice($out_etc, 0, 50)) . "\n\n";
