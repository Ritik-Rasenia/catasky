const fs = require('fs');
const vm = require('vm');

try {
    const content = fs.readFileSync('resources/views/layouts/frontend.blade.php', 'utf8');

    // Extract all script contents
    const regex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
    let match;
    let count = 0;

    while ((match = regex.exec(content)) !== null) {
        const js = match[1];
        count++;
        // Skip Laravel blade curly brace echo statements or replace them temporarily with valid JS to prevent syntax errors
        const sanitizedJs = js
            .replace(/\{\{[\s\S]*?\}\}/g, '"blade_echo"')
            .replace(/@if[\s\S]*?@else[\s\S]*?@endif/g, '"blade_cond"')
            .replace(/@[\w]+/g, ''); // Remove other directives
            
        console.log(`Checking script block ${count} (length: ${sanitizedJs.length})...`);
        try {
            new vm.Script(sanitizedJs);
            console.log(`Script block ${count} is syntactically valid!`);
        } catch (e) {
            console.error(`Syntax error in script block ${count}:`);
            console.error(e.message);
            // Print surrounding lines of the syntax error if possible
            if (e.stack) {
                console.error(e.stack);
            }
        }
    }
} catch (globalErr) {
    console.error("Global script failure:", globalErr);
}
