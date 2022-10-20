<?php

namespace Fredrik\Dtbook;


class Front
{
    static function process(\DOMElement $orgDoc, $metadata)
    {
        $frontmatterList = $orgDoc->getElementsByTagName('frontmatter');

        foreach ($frontmatterList as $frontmatter) {
            $frontMatterLevel1List = $frontmatter->getElementsByTagName('level1');
            foreach ($frontMatterLevel1List as $level1) {
                $resDoc = XMLUtil::newDocument($metadata);
                $id = $level1->attributes->getNamedItem("id");
                switch ($id->nodeValue) {
                    case 'level1_1':
                        Cover::process($level1, $resDoc);
                        break;
                    case 'level1_2':
                        FrontMatter::process($level1, $resDoc);
                        break;
                    case 'level1_3':
                        HalftitlePage::process($level1, $resDoc);
                        break;
                    case 'level1_4':
                        TitlePage::process($level1, $resDoc);
                        break;
                    case 'level1_5':
                        Colophon::process($level1, $resDoc);
                        break;
                    case 'level1_6':
                        Toc::process($level1, $resDoc);
                        break;
                    case 'level1_7':
                        Preface::process($level1, $resDoc);
                        break;
                }
            }
        }
    }
}
