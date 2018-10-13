<?php

/**
 * Class RkwsUnit
 * @package api_web\module\integration\sync
 * @createdBy Basil A Konakov
 * @createdAt 2017-05-20
 * @author YYY
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\modules\integration\classes\sync;

use api_web\modules\integration\classes\SyncLog;
use common\models\OuterUnit;
use yii\web\BadRequestHttpException;

class RkwsUnit extends ServiceRkws
{

    /** @var string $index Символьный идентификатор справочника */
    public $index = 'unit';

    /** @var string $entityTableName Класс таблицы для записи данных */
    public $entityTableName = OuterUnit::class;

    /** @var string $OperDenom Поле Denom в таблице all_service_operation */
    public static $OperDenom = 'sh_get_munits';

    /** @var array $additionalXmlFields Поле во входящем xml -> поле в нашей модели данных */
    public $additionalXmlFields = ['name' => 'name', 'parent' => 'parent_outer_uid', 'ratio' => 'ratio'];

    public function makeArrayFromReceivedDictionaryXmlData(string $data = null): array
    {
        $myXML = simplexml_load_string($data);
        SyncLog::trace('XML data: ' . $data . PHP_EOL . ' ---------------- ' . PHP_EOL);
        if (!$myXML) {
            SyncLog::trace('Empty XML data!');
            throw new BadRequestHttpException("empty_result_xml_data");
        }
        $array = [];
        foreach ($myXML->ITEM as $unit_group) {
            $parent = $unit_group->attributes()['rid'];
            foreach ($unit_group->attributes() as $k => $v) {
                $array['_'.$parent][$k] = strval($v[0]);
            }
            $array['_'.$parent]['parent'] = '';
            foreach ($unit_group->MUNITS_LIST as $list) {
                foreach ($list->ITEM as $item) {
                    $i = $item->attributes()['rid'];
                    foreach ($item->attributes() as $k => $v) {
                        $array[(string)$parent.'_'.(string)$i][$k] = strval($v[0]);
                    }
                    $array[(string)$parent.'_'.(string)$i]['parent'] = (string)$parent;
                }
            }
        }
        if (!$array) {
            SyncLog::trace('Wrong XML data!');
            throw new BadRequestHttpException("wrong_xml_data");
        }
        return $array;
    }
}
