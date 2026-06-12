// Quick fix script - read arena.js, fix the harness function, write back
const fs = require('fs');

const filePath = './assets/js/arena.js';
let content = fs.readFileSync(filePath, 'utf8');

// Find and replace the broken harness line
const broken = `    function buildBattleTestHarness(userCode, testCases) {
        const testsJsonRaw = JSON.stringify(testCases); const testsJson = testsJsonRaw.replace(/\\\\/g, " \\\\\\\\\).replace(/\/g, \\\\\).replace(/\\$/g, \\\\$\);`;

const fixed = `    function buildBattleTestHarness(userCode, testCases) {
        const testsJsonRaw = JSON.stringify(testCases);
        const testsJson = testsJsonRaw.replace(/\\\\/g, '\\\\\\\\').replace(/\`/g, '\\\`').replace(/\\$/g, '\\\\$');`;

if (content.includes(broken)) {
  content = content.replace(broken, fixed);
  fs.writeFileSync(filePath, content);
  console.log('✓ Fixed buildBattleTestHarness()');
} else {
  console.log('✗ Could not find broken pattern');
  // List first 500 chars of function for debugging
  const idx = content.indexOf('function buildBattleTestHarness');
  if (idx >= 0) {
    console.log('Found function at:', idx);
    console.log('Context:', content.substring(idx, idx + 300));
  }
}
