<?php

namespace api_web\helpers;

/**
 *
 * @author elbabuino
 */
use PhpOffice\PhpSpreadsheet\IOFactory;
use common\models\CatalogTempContent;

/**
 * Class Excel
 *
 * @package api_web\helpers
 */
class Excel
{

    const excelTempFolder = "excelTemp";

    /**
     * @param string $excelFile
     *
     * @return array
     */
    public static function get20Rows($excelFile)
    {
        $spreadsheet = \PHPExcel_IOFactory::load($excelFile);

        $worksheet = $spreadsheet->getActiveSheet();

        $rows = [];
        $rowsCount = 0;
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $cells = [];
            $i = 0;
            foreach ($cellIterator as $cell) {
                if ($i > 5) {
                    break;
                }
                $cells[] = htmlspecialchars($cell->getValue(), ENT_QUOTES);
                $i++;
            }
            $rows[] = $cells;
            $rowsCount++;
            if ($rowsCount == 20) {
                return $rows;
            }
        }

        return $rows;
    }

    /**
     *
     * @param \common\models\CatalogTemp $tempCatalog
     * @return array
     */
    public static function get20RowsFromTempUploaded($tempCatalog)
    {
        if (empty($tempCatalog)) {
            return [];
        }
        $url = \Yii::$app->get('resourceManager')->getUrl(self::excelTempFolder . DIRECTORY_SEPARATOR . $tempCatalog->excel_file);
        $file = File::getFromUrl($url);
        return self::get20Rows($file->tempName);
    }

    /**
     * @param string $excelFile
     * @param integer $tmpCatId
     * @param array $mapping ['article', 'price', 'units', 'note', 'ed', 'product', 'other'] - в указанном при добавлении каталога порядке
     * @param string $index
     * @return bool
     */
    public static function writeToTempTable($excelFile, $tmpCatId, $mapping, $index = 'article')
    {
        $spreadsheet = IOFactory::load($excelFile);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $cellsCount = 1;
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $attributes = [];
            $attributes['temp_id'] = $tmpCatId;
            $write = true;
            foreach ($cellIterator as $cell) {
                if (!array_key_exists($cellsCount, $mapping) || $cellsCount > count($mapping)) {
                    break;
                }
                $value = $cell->getValue();
                if ($mapping[$cellsCount] == 'article' && $value == 'Артикул') {
                    $write = false;
                    break;
                }

                if ($mapping[$cellsCount] == 'units' && !empty($value)){
                    $value = (float)(str_replace(',', '.', $value));
                }

                if ($mapping[$cellsCount] == $index && empty($value)) {
                    $write = false;
                    break;
                }

                $attributes[$mapping[$cellsCount]] = $value;
                $cellsCount++;
            }

            if ($write === true) {
                $rows[] = $attributes;
            }
        }

        if (empty($rows)) {
            return false;
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            CatalogTempContent::deleteAll(['temp_id' => $tmpCatId]);
            $attributes = array_keys($rows[0]);
            \Yii::$app->db->createCommand()->batchInsert(CatalogTempContent::tableName(), $attributes, $rows)->execute();
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

}
