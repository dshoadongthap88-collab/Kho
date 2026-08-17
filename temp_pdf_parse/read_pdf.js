const fs = require('fs');
const pdf = require('pdf-parse');

let dataBuffer = fs.readFileSync(process.argv[2]);

let pdfFunc = typeof pdf === 'function' ? pdf : (pdf.default || (typeof pdf === 'object' && pdf.pdf ? pdf.pdf : Object.keys(pdf)));
if (typeof pdfFunc !== 'function') {
  console.log("pdf-parse export:", pdfFunc);
} else {
  pdfFunc(dataBuffer).then(function(data) {
      console.log(data.text);
  }).catch(function(error) {
      console.error(error);
  });
}
