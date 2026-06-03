import fs from 'fs';
import path from 'path';
import vm from 'vm';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const filePath = path.join(__dirname, '..', 'resources', 'views', 'layouts', 'frontend.blade.php');
const content = fs.readFileSync(filePath, 'utf8');

// Find script tags by simple scanning instead of regex
const scriptBlocks = [];
let pos = 0;
while (true) {
    const startIdx = content.indexOf('<script>', pos);
    if (startIdx === -1) break;
    const endIdx = content.indexOf('</script>', startIdx);
    if (endIdx === -1) break;
    
    scriptBlocks.push({
        start: startIdx,
        end: endIdx,
        code: content.substring(startIdx + 8, endIdx)
    });
    pos = endIdx + 9;
}

scriptBlocks.forEach((block, index) => {
    let jsCode = block.code;
    
    // Replace Blade {{ ... }} with a simple string
    const lines = jsCode.split('\n');
    const cleanLines = lines.map(line => {
        let l = line;
        
        // Skip lines that have external script src attributes, though we only check text within <script>
        // Replace {{ ... }} with 'dummy' (and strip any surrounding quotes first to avoid ''dummy'')
        l = l.replace(/['"]?\{\{[\s\S]*?\}\}['"]?/g, "'dummy'");
        
        // Replace @if/else/endif and other blade directives
        const trimmed = l.trim();
        if (trimmed.startsWith('@if')) {
            l = 'if(true) {';
        } else if (trimmed.startsWith('@else')) {
            l = '} else {';
        } else if (trimmed.startsWith('@endif')) {
            l = '}';
        } else if (trimmed.startsWith('@csrf')) {
            l = '';
        } else if (trimmed.startsWith('@stack')) {
            l = '';
        } else if (trimmed.startsWith('@')) {
            // Remove comments/directives starting with @
            l = '// ' + l;
        }
        return l;
    });
    
    const cleanJs = cleanLines.join('\n');
    console.log(`Checking block ${index + 1} (approx ${cleanLines.length} lines)...`);
    
    try {
        new vm.Script(cleanJs);
        console.log(`Block ${index + 1}: Syntax is OK!`);
    } catch (err) {
        console.error(`Block ${index + 1}: Syntax Error:`, err.message);
        
        // Output context
        if (err.stack) {
            const errLineMatch = err.stack.match(/evalmachine\.<anonymous>:(\d+)/);
            if (errLineMatch) {
                const lineNum = parseInt(errLineMatch[1], 10);
                console.log(`Error near line ${lineNum}:`);
                for (let i = Math.max(0, lineNum - 5); i < Math.min(cleanLines.length, lineNum + 5); i++) {
                    console.log(`${i + 1}: ${cleanLines[i]}`);
                }
            } else {
                console.error(err.stack);
            }
        }
    }
});
