<?php
$headCreate = file_get_contents('scratch/head_create.blade.php');
echo "Has JV in HEAD? " . (strpos($headCreate, 'payee_jv') !== false ? 'YES' : 'NO') . "\n";
echo "Has Cash Account in HEAD? " . (strpos($headCreate, 'cashAccounts') !== false ? 'YES' : 'NO') . "\n";
echo "Has Supplier in HEAD? " . (strpos($headCreate, 'payee_supplier') !== false ? 'YES' : 'NO') . "\n";
echo "Has Client in HEAD? " . (strpos($headCreate, 'payee_client') !== false ? 'YES' : 'NO') . "\n";
echo "Has Staff in HEAD? " . (strpos($headCreate, 'payee_staff') !== false ? 'YES' : 'NO') . "\n";
