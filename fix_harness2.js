const fs = require('fs');
let content = fs.readFileSync('./assets/js/arena.js', 'utf8');

// Replace the broken function with a simple working version
const newFunction = `    function buildBattleTestHarness(userCode, testCases) {
        // Build the test harness code
        let harness = userCode + '\\n\\n(function() {\\n';
        harness += '  const __tests = ' + JSON.stringify(testCases) + ';\\n';
        harness += '  const __start = Date.now();\\n';
        harness += '  if (typeof calculateMaxProfit !== \"function\") {\\n';
        harness += '    throw new Error(\"Fungsi calculateMaxProfit() tidak ditemukan.\");\\n';
        harness += '  }\\n';
        harness += '  const __results = __tests.map(test => ({\\n';
        harness += '    input: test.input,\\n';
        harness += '    expected: test.expected,\\n';
        harness += '    actual: calculateMaxProfit(test.input),\\n';
        harness += '    passed: calculateMaxProfit(test.input) === test.expected\\n';
        harness += '  }));\\n';
        harness += '  const __duration = Date.now() - __start;\\n';
        harness += '  console.log(JSON.stringify({ passed: __results.filter(t => t.passed).length, total: __results.length, duration: __duration, results: __results }));\\n';
        harness += '})();';
        return harness;
    }`;

// Find the start and end of the current broken function
const startIdx = content.indexOf('    function buildBattleTestHarness(userCode, testCases) {');
const endIdx = content.indexOf('    async function runUserCode', startIdx);

if (startIdx >= 0 && endIdx >= 0) {
  const before = content.substring(0, startIdx);
  const after = content.substring(endIdx);
  content = before + newFunction + '\n\n' + after;
  fs.writeFileSync('./assets/js/arena.js', content);
  console.log('✓ Fixed buildBattleTestHarness() with string concatenation version');
} else {
  console.log('✗ Could not find function boundaries');
}
