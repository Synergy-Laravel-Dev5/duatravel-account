function allocateExactAmount(totalAmount, count) {
    if (count <= 0) return [];
    const totalCents = Math.round(totalAmount * 100);
    const baseCents = Math.floor(totalCents / count);
    let remainder = totalCents - (baseCents * count);

    const amounts = [];
    for (let i = 0; i < count; i++) {
        const paxCents = baseCents + (i < remainder ? 1 : 0);
        amounts.push((paxCents / 100).toFixed(2));
    }
    return amounts;
}

const testCases = [
    { total: 100, count: 3 },
    { total: 200, count: 3 },
    { total: 100, count: 2 },
    { total: 100, count: 4 },
    { total: 100, count: 6 },
    { total: 100, count: 7 },
    { total: 250, count: 3 },
    { total: 333.33, count: 3 },
];

testCases.forEach(tc => {
    const allocated = allocateExactAmount(tc.total, tc.count);
    const sum = allocated.reduce((acc, v) => acc + parseFloat(v), 0);
    const sumFixed = (Math.round(sum * 100) / 100).toFixed(2);
    const expected = tc.total.toFixed(2);
    const pass = sumFixed === expected;
    console.log(`Total: ${tc.total}, Count: ${tc.count} => [${allocated.join(', ')}] => Sum: ${sumFixed} (${pass ? 'PASS' : 'FAIL'})`);
});
