const fs = require('fs');
const path = require('path');
const vm = require('vm');

const filePath = path.join(__dirname, '..', 'resources', 'views', 'layouts', 'frontend.blade.php');
const content = fs.readFileSync(filePath, 'utf8');

// Find all script blocks
const regex = /<script>([\s\S]*?)<\/script>/g;
let match;
let blockCount = 0;

while ((match = regex.exec(content)) !== null) {
    blockCount++;
    let jsCode = match[1];
    
    // Replace Blade template expressions with dummy JS values to make it valid JS
    // Replace {{ ... }} with a dummy string
    jsCode = jsCode.replace(/\{\{[\s\S]*?\}\}/g, '"blade_val"');
    // Replace @if ... @else ... @endif with comments or simple blocks
    jsCode = jsCode.replace(/@if\([\s\S]*?\)/g, 'if(true) {');
    jsCode = jsCode.replace(/@else/g, '} else {');
    jsCode = jsCode.replace(/@endif/g, '}');
    jsCode = jsCode.replace(/@csrf/g, '');
    jsCode = jsCode.replace(/@stack\([\s\S]*?\)/g, '');
    
    console.log(`Checking block ${blockCount} (approx ${jsCode.split('\n').length} lines)...`);
    
    try {
        new vm.Script(jsCode);
        console.log(`Block ${blockCount}: Syntax is OK!`);
    } catch (err) {
        console.error(`Block ${blockCount}: Syntax Error:`, err.message);
        
        // Find line numbers
        const lines = jsCode.split('\n');
        const errLine = err.stack.split('\n')[0].match(/:(\d+)/);
        if (errLine) {
            const lineNum = parseInt(errLine[1], 10);
            console.log(`Error near line ${lineNum}:`);
            for (let i = Math.max(0, lineNum - 5); i < Math.min(lines.length, lineNum + 5); i++) {
                console.log(`${i + 1}: ${lines[i]}`);
            }
        } else {
            console.error(err.stack);
        }
    }
}
