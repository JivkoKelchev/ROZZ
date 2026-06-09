<?php

namespace RozzBundle\Services;

/**
 * Генерира истински .docx файл (Office Open XML) от HTML без външни библиотеки.
 *
 * .docx е ZIP с XML части; поддържаме подмножеството HTML, което се ползва в
 * шаблоните за договори: p, div, ol/ul/li, strong/b, em/i, u, span, br, текст
 * и подравняване чрез style="text-align:...". Това е достатъчно изтегленият
 * документ да съвпада с изгледа на екрана и да се отваря коректно в Word.
 */
class DocxGenerator
{
    const FONT = 'Times New Roman';
    const SIZE = 24; // 12pt в half-points

    /**
     * @param string $html HTML на договора (след заместване на токените)
     * @return string съдържание на .docx файла
     */
    public function generate($html)
    {
        $paragraphs = $this->htmlToParagraphs($html);
        $bodyXml = '';
        foreach ($paragraphs as $p) {
            $bodyXml .= $this->paragraphXml($p);
        }

        return $this->zipDocx($this->documentXml($bodyXml), $this->stylesXml());
    }

    // ---- HTML -> структура от абзаци -------------------------------------

    private function htmlToParagraphs($html)
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?><div id="__root">' . $html . '</div>');
        libxml_clear_errors();

        $root = $dom->getElementsByTagName('body')->item(0);
        $paragraphs = [];
        if ($root) {
            $this->walkContainer($root, $paragraphs);
        }

        return $paragraphs;
    }

    /**
     * Обхожда контейнер: групира съседните "инлайн" възли в абзац, а блоковите
     * елементи обработва отделно.
     */
    private function walkContainer(\DOMNode $node, array &$paragraphs)
    {
        $pending = [];
        foreach ($node->childNodes as $child) {
            if ($this->isBlock($child)) {
                $this->flushPending($pending, $paragraphs, 'both');
                $this->processBlock($child, $paragraphs);
            } else {
                $this->collectRuns($child, $pending, ['b' => false, 'i' => false, 'u' => false]);
            }
        }
        $this->flushPending($pending, $paragraphs, 'both');
    }

    private function processBlock(\DOMElement $el, array &$paragraphs)
    {
        $tag = strtolower($el->nodeName);

        if ($tag === 'div') {
            $this->walkContainer($el, $paragraphs);

            return;
        }

        if ($tag === 'ol' || $tag === 'ul') {
            $index = 1;
            foreach ($el->childNodes as $li) {
                if ($li->nodeType === XML_ELEMENT_NODE && strtolower($li->nodeName) === 'li') {
                    $runs = [];
                    $this->collectRuns($li, $runs, ['b' => false, 'i' => false, 'u' => false]);
                    $prefix = ($tag === 'ol') ? ($index . '. ') : '• ';
                    array_unshift($runs, ['text' => $prefix, 'b' => false, 'i' => false, 'u' => false]);
                    $paragraphs[] = ['align' => 'both', 'runs' => $runs];
                    $index++;
                }
            }

            return;
        }

        // p, h1-h6 и др. -> един абзац
        $runs = [];
        $this->collectRuns($el, $runs, ['b' => false, 'i' => false, 'u' => false]);
        $paragraphs[] = ['align' => $this->alignFromStyle($el), 'runs' => $runs];
    }

    /**
     * Събира текстови "рънове" от поддървото, носейки форматирането (b/i/u).
     */
    private function collectRuns(\DOMNode $node, array &$runs, array $fmt)
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            $text = $this->normalizeText($node->nodeValue);
            if ($text !== '') {
                $runs[] = ['text' => $text, 'b' => $fmt['b'], 'i' => $fmt['i'], 'u' => $fmt['u']];
            }

            return;
        }

        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return;
        }

        $tag = strtolower($node->nodeName);
        if ($tag === 'br') {
            $runs[] = ['break' => true];

            return;
        }
        if ($tag === 'b' || $tag === 'strong') {
            $fmt['b'] = true;
        } elseif ($tag === 'i' || $tag === 'em') {
            $fmt['i'] = true;
        } elseif ($tag === 'u') {
            $fmt['u'] = true;
        }

        foreach ($node->childNodes as $child) {
            $this->collectRuns($child, $runs, $fmt);
        }
    }

    private function flushPending(array &$pending, array &$paragraphs, $align)
    {
        if (empty($pending)) {
            return;
        }
        // пропусни абзаци само от празно пространство
        $hasText = false;
        foreach ($pending as $run) {
            if (isset($run['text']) && trim($run['text']) !== '') {
                $hasText = true;
                break;
            }
        }
        if ($hasText) {
            $paragraphs[] = ['align' => $align, 'runs' => $pending];
        }
        $pending = [];
    }

    private function isBlock(\DOMNode $node)
    {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return false;
        }
        $tag = strtolower($node->nodeName);

        return in_array($tag, ['p', 'div', 'ol', 'ul', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'table'], true);
    }

    private function alignFromStyle(\DOMElement $el)
    {
        $style = strtolower($el->getAttribute('style'));
        if (strpos($style, 'text-align:center') !== false || strpos($style, 'text-align: center') !== false) {
            return 'center';
        }
        if (strpos($style, 'text-align:right') !== false || strpos($style, 'text-align: right') !== false) {
            return 'right';
        }

        return 'both';
    }

    /**
     * Свива поредици от обикновени интервали/нови редове до един интервал, като
     * запазва твърдия интервал (&nbsp; -> \xC2\xA0).
     */
    private function normalizeText($text)
    {
        return preg_replace('/[ \t\r\n]+/u', ' ', $text);
    }

    // ---- Изграждане на WordprocessingML ----------------------------------

    private function paragraphXml(array $p)
    {
        $runsXml = '';
        foreach ($p['runs'] as $run) {
            if (!empty($run['break'])) {
                $runsXml .= '<w:r><w:br/></w:r>';
                continue;
            }
            $rPr = '';
            if (!empty($run['b'])) { $rPr .= '<w:b/>'; }
            if (!empty($run['i'])) { $rPr .= '<w:i/>'; }
            if (!empty($run['u'])) { $rPr .= '<w:u w:val="single"/>'; }
            $rPrXml = $rPr !== '' ? '<w:rPr>' . $rPr . '</w:rPr>' : '';
            $runsXml .= '<w:r>' . $rPrXml . '<w:t xml:space="preserve">' . $this->esc($run['text']) . '</w:t></w:r>';
        }

        return '<w:p><w:pPr><w:jc w:val="' . $p['align'] . '"/></w:pPr>' . $runsXml . '</w:p>';
    }

    private function documentXml($bodyXml)
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:body>' . $bodyXml
            . '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/>'
            . '<w:pgMar w:top="1134" w:right="850" w:bottom="1134" w:left="1134" w:header="708" w:footer="708" w:gutter="0"/>'
            . '</w:sectPr></w:body></w:document>';
    }

    private function stylesXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:docDefaults><w:rPrDefault><w:rPr>'
            . '<w:rFonts w:ascii="' . self::FONT . '" w:hAnsi="' . self::FONT . '" w:cs="' . self::FONT . '"/>'
            . '<w:sz w:val="' . self::SIZE . '"/><w:szCs w:val="' . self::SIZE . '"/>'
            . '</w:rPr></w:rPrDefault></w:docDefaults></w:styles>';
    }

    private function esc($text)
    {
        return str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $text);
    }

    private function zipDocx($documentXml, $stylesXml)
    {
        $tmp = tempnam(sys_get_temp_dir(), 'docx');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            . '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
            . '</Types>');

        $zip->addFromString('_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            . '</Relationships>');

        $zip->addFromString('word/_rels/document.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>');

        $zip->addFromString('word/document.xml', $documentXml);
        $zip->addFromString('word/styles.xml', $stylesXml);
        $zip->close();

        $content = file_get_contents($tmp);
        unlink($tmp);

        return $content;
    }
}
