<?php
$zip = new ZipArchive;
if ($zip->open('GEL_Cahier_des_Charges_Maitre_Unifie b.docx') === TRUE) {
    $content = $zip->getFromName('word/document.xml');
    $zip->close();
    file_put_contents('extracted_docx.txt', $content);
    echo "Raw XML extracted.\n";
} else {
    echo "Failed to open docx file.\n";
}
