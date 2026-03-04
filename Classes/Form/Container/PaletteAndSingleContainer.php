<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Form\Container;

class PaletteAndSingleContainer extends \TYPO3\CMS\Backend\Form\Container\PaletteAndSingleContainer
{

    protected function manipulateColClass(string &$colClass, array $element): void
    {
        if ($colClassFromTca = $this->data['processedTca']['columns'][$element['fieldName']]['colClass'] ?? false) {
            $colClass = $colClassFromTca;
        }
    }

    /**
     * IMPORTANT: This is a copy of the method in the parent class AND the inserted code below (look of @NOTE)
     *
     * Renders inner content of single elements of a palette and wrap it as needed
     *
     * @param array $elementArray Array of elements
     * @return string Wrapped content
     */
    protected function renderInnerPaletteContent(array $elementArray): string
    {
        // Group fields
        $groupedFields = [];
        $row = 0;
        $lastLineWasLinebreak = true;
        foreach ($elementArray['elements'] as $element) {
            if ($element['type'] === 'linebreak') {
                if (!$lastLineWasLinebreak) {
                    $row++;
                    $groupedFields[$row][] = $element;
                    $row++;
                    $lastLineWasLinebreak = true;
                }
            } else {
                $lastLineWasLinebreak = false;
                $groupedFields[$row][] = $element;
            }
        }

        $result = [];
        // Process fields
        foreach ($groupedFields as $fields) {
            $numberOfItems = count($fields);
            $colWidth = (int)floor(12 / $numberOfItems);
            // Column class calculation
            $colClass = 'col-md-12';
            $colClear = [];
            if ($colWidth == 6) {
                $colClass = 'col col-sm-6';
                $colClear = [
                    2 => 'd-sm-block d-md-none',
                ];
            } elseif ($colWidth === 4) {
                $colClass = 'col col-sm-4';
                $colClear = [
                    3 => 'd-sm-block d-md-none',
                ];
            } elseif ($colWidth === 3) {
                $colClass = 'col col-sm-6 col-md-3';
                $colClear = [
                    2 => 'd-sm-block d-md-none',
                    4 => 'd-sm-block d-md-block d-xl-none',
                ];
            } elseif ($colWidth <= 2) {
                $colClass = 'col col-sm-6 col-md-3 col-lg-2';
                $colClear = [
                    2 => 'd-sm-block',
                    4 => 'd-sm-block d-md-none',
                    6 => 'd-sm-block d-md-block d-lg-none',
                ];
            }


            // Render fields
            for ($counter = 0; $counter < $numberOfItems; $counter++) {
                $element = $fields[$counter];
                if ($element['type'] === 'linebreak') {
                    if ($counter !== $numberOfItems) {
                        $result[] = '<div class="clearfix"></div>';
                    }
                } else {



                    // @NOTE - this is the inserted code
                    $this->manipulateColClass($colClass, $element);



                    $result[] = '<div class="form-group t3js-formengine-validation-marker t3js-formengine-palette-field ' . $colClass . '">';
                    $result[] =     $element['fieldHtml'];
                    $result[] = '</div>';
                    // Breakpoints
                    if ($counter + 1 < $numberOfItems && !empty($colClear)) {
                        foreach ($colClear as $rowBreakAfter => $clearClass) {
                            if (($counter + 1) % $rowBreakAfter === 0) {
                                $result[] = '<div class="clearfix ' . $clearClass . '"></div>';
                            }
                        }
                    }
                }
            }
        }
        return implode(LF, $result);
    }

}
