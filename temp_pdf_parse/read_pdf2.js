const { PdfReader } = require('pdfreader');

new PdfReader().parseFileItems("d:\\Project\\docs\\VAP-KĐT-ĐHB_363190_5701865547_VAP-KĐT-ĐHB-KQLVTMMTBBJYA-KKTSCBJYA_1.pdf", (err, item) => {
  if (err) console.error("error:", err);
  else if (!item) console.log("done");
  else if (item.text) console.log(item.text);
});
