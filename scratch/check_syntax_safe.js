const fs = require('fs');
const path = require('path');
const vm = require('vm');

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
    
    // Extremely safe replacements:
    // Replace Blade {{ ... }} with a simple string
    // Let's replace any "{{ ... }}" with a dummy string
    // Note: blade values don't span lines usually, let's just do a simple non-greedy search or simple string replace if possible
    // To be absolutely safe against catastrophic backtracking, we can do a line-by-line replace
    const lines = jsCode.split('\n');
    const cleanLines = lines.map(line => {
        let l = line;
        // Replace {{ ... }} with "blade_val"
        while (l.includes('{{') && l.includes('}}')) {
            const start = l.indexOf('{{');
            const end = l.indexOf('}}', start);
            if (end === -1) break;
            l = l.substring(0, start) + '"blade_val"' + l.substring(end + 2);
        }
        // Replace @if/else/endif
        if (l.trim().startsWith('@if')) {
            l = 'if(true) {';
        } else if (l.trim().startsWith('@else')) {
            l = '} else {';
        } else if (l.trim().startsWith('@endif')) {
            l = '}';
        } else if (l.trim().startsWith('@csrf')) {
            l = '';
        } else if (l.trim().startsWith('@stack')) {
            l = '';
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
});
