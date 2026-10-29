<?php
// 1. In create.blade.php
$createFile = __DIR__ . '/../resources/views/booking/create.blade.php';
$content = file_get_contents($createFile);

$fnDef = "        function buildRoomViewsOptions(selected = 'Standard View') {\r\n            const views = [\r\n                'Standard View',\r\n                'City View',\r\n                'Haram View',\r\n                'Kaaba View',\r\n                'Partial Haram View',\r\n                'Sea / Mountain View'\r\n            ];\r\n            return views.map(v => `<option value=\"\${v}\" \${selected === v ? 'selected' : ''}>\${v}</option>`).join('');\r\n        }\r\n\r\n        function refreshHotelsDropdown(idx = null) {\r\n            refreshMasterData(true);\r\n        }";

if (strpos($content, 'function buildRoomViewsOptions') === false) {
    $anchor = "function refreshHotelsDropdown(idx = null) {\r\n            refreshMasterData(true);\r\n        }";
    if (strpos($content, $anchor) !== false) {
        $content = str_replace($anchor, $fnDef, $content);
        file_put_contents($createFile, $content);
        echo "✓ buildRoomViewsOptions added to create.blade.php (CRLF)\n";
    } else {
        $anchorLF = str_replace("\r\n", "\n", $anchor);
        $fnDefLF = str_replace("\r\n", "\n", $fnDef);
        $content = str_replace($anchorLF, $fnDefLF, $content);
        file_put_contents($createFile, $content);
        echo "✓ buildRoomViewsOptions added to create.blade.php (LF)\n";
    }
}

// 2. In edit.blade.php
$editFile = __DIR__ . '/../resources/views/booking/edit.blade.php';
$contentEdit = file_get_contents($editFile);
if (strpos($contentEdit, 'function refreshHotelsDropdown') === false) {
    $anchorEdit = "return views.map(v => `<option value=\"\${v}\" \${selected === v ? 'selected' : ''}>\${v}</option>`).join('');\r\n        }";
    $replaceEdit = "return views.map(v => `<option value=\"\${v}\" \${selected === v ? 'selected' : ''}>\${v}</option>`).join('');\r\n        }\r\n\r\n        function refreshHotelsDropdown(idx = null) {\r\n            refreshMasterData(true);\r\n        }";
    if (strpos($contentEdit, $anchorEdit) !== false) {
        $contentEdit = str_replace($anchorEdit, $replaceEdit, $contentEdit);
        file_put_contents($editFile, $contentEdit);
        echo "✓ refreshHotelsDropdown added to edit.blade.php (CRLF)\n";
    } else {
        $anchorLF = str_replace("\r\n", "\n", $anchorEdit);
        $replaceLF = str_replace("\r\n", "\n", $replaceEdit);
        $contentEdit = str_replace($anchorLF, $replaceLF, $contentEdit);
        file_put_contents($editFile, $contentEdit);
        echo "✓ refreshHotelsDropdown added to edit.blade.php (LF)\n";
    }
}
