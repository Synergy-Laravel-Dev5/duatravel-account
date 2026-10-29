<?php
$createFile = __DIR__ . '/../resources/views/booking/create.blade.php';
$content = file_get_contents($createFile);

echo "1. Adding mealPlansData & buildMealPlansOptions & buildHotelsOptions(filterCity) to create.blade.php...\n";

// Target around let vehiclesData
$targetVehicles = "        let vehiclesData    = @json(\$vehicles ?? []);";
$replaceVehicles = "        let vehiclesData    = @json(\$vehicles ?? []);\r\n        const mealPlansData = @json(\$mealPlans ?? []);";

if (strpos($content, "const mealPlansData =") === false) {
    if (strpos($content, $targetVehicles) !== false) {
        $content = str_replace($targetVehicles, $replaceVehicles, $content);
        echo "✓ mealPlansData added.\n";
    } else {
        $targetLF = str_replace("\r\n", "\n", $targetVehicles);
        $replaceLF = str_replace("\r\n", "\n", $replaceVehicles);
        $content = str_replace($targetLF, $replaceLF, $content);
        echo "✓ mealPlansData added (LF).\n";
    }
}

// Target buildHotelsOptions
$targetOldBuildHotels = "        function buildHotelsOptions() {\r\n            return `<option value=\"\">Select Hotel</option>` +\r\n                (Array.isArray(hotelsData) ? hotelsData.map(h => `<option value=\"\${h.name}\">\${h.name} (\${h.city || h.place || ''})</option>`).join('') : '');\r\n        }";

$newBuildHotelsAndMeals = "        function buildHotelsOptions(filterCity = null) {\r\n            let list = Array.isArray(hotelsData) ? hotelsData : [];\r\n            if (filterCity) {\r\n                const fc = filterCity.toLowerCase();\r\n                list = list.filter(h => {\r\n                    const p = (h.city || (typeof h.place === 'object' ? h.place?.value : h.place) || '').toLowerCase();\r\n                    return p.includes(fc);\r\n                });\r\n            }\r\n            return `<option value=\"\">Select Hotel</option>` +\r\n                list.map(h => {\r\n                    const cityStr = h.city || (typeof h.place === 'object' ? h.place?.value : h.place) || '';\r\n                    return `<option value=\"\${h.name}\">\${h.name} (\${cityStr})</option>`;\r\n                }).join('');\r\n        }\r\n\r\n        function buildMealPlansOptions(selectedCode = 'BB') {\r\n            if (!Array.isArray(mealPlansData) || mealPlansData.length === 0) {\r\n                return `\r\n                    <option value=\"RO\" \${selectedCode === 'RO' ? 'selected' : ''}>Room Only (RO)</option>\r\n                    <option value=\"BB\" \${selectedCode === 'BB' ? 'selected' : ''}>Bed & Breakfast (BB)</option>\r\n                    <option value=\"HB\" \${selectedCode === 'HB' ? 'selected' : ''}>Half Board (HB)</option>\r\n                    <option value=\"FB\" \${selectedCode === 'FB' ? 'selected' : ''}>Full Board (FB)</option>\r\n                    <option value=\"SO\" \${selectedCode === 'SO' ? 'selected' : ''}>Suhoor Only (SO)</option>\r\n                    <option value=\"IO\" \${selectedCode === 'IO' ? 'selected' : ''}>Iftar Only (IO)</option>\r\n                `;\r\n            }\r\n            return mealPlansData.map(mp => `\r\n                <option value=\"\${mp.code}\" \${selectedCode === mp.code ? 'selected' : ''}>\${mp.name} (\${mp.code})</option>\r\n            `).join('');\r\n        }\r\n\r\n        function refreshHotelsDropdown(idx = null) {\r\n            refreshMasterData(true);\r\n        }";

if (strpos($content, $targetOldBuildHotels) !== false) {
    $content = str_replace($targetOldBuildHotels, $newBuildHotelsAndMeals, $content);
    echo "✓ buildHotelsOptions & buildMealPlansOptions replaced (CRLF).\n";
} else {
    $targetLF = str_replace("\r\n", "\n", $targetOldBuildHotels);
    $replaceLF = str_replace("\r\n", "\n", $newBuildHotelsAndMeals);
    if (strpos($content, $targetLF) !== false) {
        $content = str_replace($targetLF, $replaceLF, $content);
        echo "✓ buildHotelsOptions & buildMealPlansOptions replaced (LF).\n";
    } else {
        echo "✗ targetOldBuildHotels not found in create.blade.php!\n";
    }
}

file_put_contents($createFile, $content);
echo "Done first pass on create.blade.php.\n";
