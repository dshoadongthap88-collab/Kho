const fs = require('fs');
const PDFParser = require("pdf2json");

const pdfParser = new PDFParser(this, 1);

pdfParser.on("pdfParser_dataError", errData => console.error(errData.parserError) );
pdfParser.on("pdfParser_dataReady", pdfData => {
    fs.writeFileSync("output.txt", pdfParser.getRawTextContent());
    console.log("Extracted text saved to output.txt");
});

pdfParser.loadPDF("d:\\Project\\docs\\VAP-KĐT-ĐHB_363190_5701865547_VAP-KĐT-ĐHB-KQLVTMMTBBJYA-KKTSCBJYA_1.pdf");
