const unitRegexStr = '(cái|cai|lít|lit|l|kg|kilogam|hộp|hop|chai|lon|vỉ|vi|cuộn|cuon|mét|met|m|bộ|bo|chiếc|chiec|bao|túi|tui|thùng|thung|hũ|hu|can|cặp|cap|tấn|tan|tạ|ta|yến|yen|g|gam|ml)';
const regex = new RegExp('\\b([A-Z]{2,}\\d+|\\w+-\\d+|\\d+[A-Z]{2,})\\b(.*?)\\b' + unitRegexStr + '\\b\\s*(\\d+([.,]\\d+)?)', 'i');

const lines = [
    '1 VAP02348 Mỡ bôi trơn Licas Grease No2 EP2 - Kg 3,400 3,400 - Mới Ñ',
    '2 VAP2179 Dầu hộp số Total Dynatrans ACX 30 - LIT 416 416 - Mới Ñ',
    'Phùng Anh Hảo 18/05/2026 - 08:38; XN: Đặng Hữu Hòa 18/05/2026 - 11:31; PD1: Nguyễn Sơn Hải 18/05/2026 - 22:20'
];

lines.forEach(line => {
    const match = line.match(regex);
    if (match) {
        console.log('--- MATCH ---');
        console.log('Code:', match[1]);
        console.log('RawName:', match[2]);
        console.log('Unit:', match[3]);
        console.log('Qty:', match[4]);
    } else {
        console.log('NO MATCH:', line);
    }
});
