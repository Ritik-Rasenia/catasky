import fs from 'fs';
import path from 'path';
import vm from 'vm';

const filePath = 'c:/xampp/htdocs/catasky/resources/views/layouts/frontend.blade.php';
const content = fs.readFileSync(filePath, 'utf8');

const lines = content.split('\n');

// We want lines 1261 to 4800 (1-indexed, excluding the </script> tag on line 4801)
// 1261-1 = 1260 index. 4800 index (which is line 4800).
const jsLines = lines.slice(1261, 4800);
let jsContent = jsLines.join('\n');

// Replace Blade variables {{ ... }} with a simple variable name (we will declare it at the top)
jsContent = jsContent.replace(/\{\{\s*[^}]+\s*\}\}/g, 'BLADE_VAR');

// Replace Blade directive @if ... @else ... @endif or similar
jsContent = jsContent.replace(/@[a-z_]+/g, '// BLADE_DIRECTIVE');

// Prepend declaration of BLADE_VAR to make it compile
jsContent = `const BLADE_VAR = 'dummy';\n` + jsContent;

try {
    new vm.Script(jsContent);
    console.log("No SyntaxError found in the main JavaScript block!");
} catch (e) {
    console.error("SyntaxError found!");
    console.error(e.message);
    console.error("Stack trace:");
    console.error(e.stack);
}
