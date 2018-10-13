<?php
namespace api_web\classes;
use api_web\exceptions\ValidationException;
use api_web\modules\integration\classes\documents\EdiOrder;
use api_web\modules\integration\classes\documents\Order;
use api_web\modules\integration\classes\documents\OrderContent;
use api_web\modules\integration\classes\documents\OrderContentEmail;
use api_web\modules\integration\classes\documents\OrderEmail;
use api_web\modules\integration\classes\documents\Waybill;
use api_web\modules\integration\classes\documents\WaybillContent;
use common\helpers\DBNameHelper;
use yii\data\SqlDataProvider;
use yii\web\BadRequestHttpException;

/**
 * Class DocumentWebApi
 * @package api_web\modules\integration\classes
 */
class DocumentWebApi extends \api_web\components\WebApi
{

    const DOC_GROUP_STATUS_WAIT_SENDING = 'Ожидают выгрузки';
    const DOC_GROUP_STATUS_WAIT_FORMING = 'Ожидают формирования';
    const DOC_GROUP_STATUS_SENT = 'Выгружена';

    private static $doc_group_status = [
        1 => self::DOC_GROUP_STATUS_WAIT_SENDING,
        2 => self::DOC_GROUP_STATUS_WAIT_FORMING,
        3 => self::DOC_GROUP_STATUS_SENT,
    ];

    const DOC_WAYBILL_STATUS_COLLATED = 'Сопоставлена';
    const DOC_WAYBILL_STATUS_READY = 'Сформирована';
    const DOC_WAYBILL_STATUS_ERROR = 'Ошибка';
    const DOC_WAYBILL_STATUS_RESET = 'Сброшена';
    const DOC_WAYBILL_STATUS_SENT = 'Выгружена';

    private static $doc_waybill_status = [
        1 => self::DOC_WAYBILL_STATUS_COLLATED,
        2 => self::DOC_WAYBILL_STATUS_READY,
        3 => self::DOC_WAYBILL_STATUS_ERROR,
        4 => self::DOC_WAYBILL_STATUS_RESET,
        5 => self::DOC_WAYBILL_STATUS_SENT,
    ];

    /**константа типа документа - заказ*/
    const TYPE_ORDER = 'order';
    /**константа типа документа - накладная*/
    const TYPE_WAYBILL = 'waybill';
    /** накладная поставщика **/
    const TYPE_ORDER_EMAIL = 'order_email';
    /** заказ из EDI */
    const TYPE_ORDER_EDI = 'order_edi';

    /**статический список типов документов*/
    public static $TYPE_LIST = [self::TYPE_ORDER, self::TYPE_WAYBILL, self::TYPE_ORDER_EMAIL, self::TYPE_ORDER_EDI];

    private static $models = [
        self::TYPE_WAYBILL => Waybill::class,
        self::TYPE_ORDER => Order::class,
        self::TYPE_ORDER_EMAIL => OrderEmail::class,
        self::TYPE_ORDER_EDI => EdiOrder::class,
    ];

    private static $modelsContent = [
        self::TYPE_WAYBILL => WaybillContent::class,
        self::TYPE_ORDER => OrderContent::class,
        self::TYPE_ORDER_EMAIL => OrderContentEmail::class,
        self::TYPE_ORDER_EDI => EdiOrderContent::class,
    ];

    /**
     * Метод получения шапки документа
     * @param $document_id
     * @param $type
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function getHeader(array $post)
    {
        if (!isset($post['type'])) {
            throw new BadRequestHttpException("empty_param|type");
        }
        if (empty($post['document_id'])) {
            throw new BadRequestHttpException("empty_param|document_id");
        }

        if (!in_array(strtolower($post['type']), self::$TYPE_LIST)) {
            throw new BadRequestHttpException('dont support this type');
        }

        $className = self::$models[$post['type']];
        return $className::prepareModel($post['document_id']);
    }

    /**
     * Метод получения детальной части документа
     * @param $document_id
     * @param $type
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function getContent(array $post)
    {
        if (!isset($post['type'])) {
            throw new BadRequestHttpException("empty_param|type");
        }
        if (empty($post['document_id'])) {
            throw new BadRequestHttpException("empty_param|document_id");
        }

        if (!in_array(strtolower($post['type']), self::$TYPE_LIST)) {
            throw new BadRequestHttpException('dont support this type');
        }

        $className = self::$modelsContent[$post['type']];
        return $className::prepareModel($post['document_id']);
    }

    /**
     * Получение состава документа
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getDocumentContents(array $post)
    {
        if (!isset($post['type'])) {
            throw new BadRequestHttpException("empty_param|type");
        }
        if (empty($post['document_id'])) {
            throw new BadRequestHttpException("empty_param|document_id");
        }

        if (!in_array(strtolower($post['type']), self::$TYPE_LIST)) {
            throw new BadRequestHttpException('dont support this type');
        }

        if (strtolower($post['type']) == self::TYPE_WAYBILL) {
            $modelClass = self::$modelsContent[self::TYPE_WAYBILL];
            return $modelClass::prepareModel($post['document_id']);
        }

        $return = [];

        $apiShema = DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db_api->dsn);
        $sql_waybill = "SELECT id, 'waybill' as type, acquirer_id as client_id, bill_status_id as waybill_status, null as order_date, doc_date as waybill_date, 
                            outer_number_code as waybill_number, null as doc_number, order_id 
                            FROM `$apiShema`.waybill";

        switch (strtolower($post['type'])) {
            case self::TYPE_ORDER :
                $sql = "
                                            SELECT * from (
                                                $sql_waybill
                                            UNION ALL
                                                SELECT id, '" . self::TYPE_ORDER_EMAIL . "' as type, organization_id as client_id, null as waybill_status, date as order_date, null as waybill_date,
                                                null as waybill_number, number as doc_number, order_id 
                                                FROM integration_invoice
                                            ) as c where c.order_id = " . $post['document_id'];
                $sql_positions = "
                                            select order_content.id, '" . self::TYPE_ORDER . "' as type from order_content 
                                            left join `$apiShema`.waybill_content as wc on wc.order_content_id = order_content.id
                                            where order_id = " . $post['document_id'] . " and order_content_id is null
                    ";
                break;
            case self::TYPE_ORDER_EMAIL:
                $sql = "$sql_waybill where order_id = " . $post['document_id'];
                break;
            default:
                return $return;
        }

        $result = \Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($result as $model) {
            $modelClass = self::$models[$model['type']];
            $return['documents'][] = $modelClass::prepareModel($model['id']);;
        }

        if (isset($sql_positions)) {
            $result = \Yii::$app->db->createCommand($sql_positions)->queryAll();

            foreach ($result as $model) {
                $modelClass = self::$modelsContent[$model['type']];
                $return['positions'][] = $modelClass::prepareModel($model['id']);;
            }
        }

        return $return;
    }

    /**
     * Получение списка документов
     * @param array $post
     * @return array
     */

    public function getDocumentsList(array $post)
    {
        $client = $this->user->organization;

        $sort = (isset($post['sort']) ? $post['sort'] : null);
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $documents = [];

        $params_sql = [];
        $where_all = '';

        if (isset($post['search']['business_id'])) {
            $where_all .= " AND client_id  = :business_id";
            $params_sql[':business_id'] = $post['search']['business_id'];
        }

        if (isset($post['search']['waybill_status'])) {
            $where_all .= " AND waybill_status = :waybill_status";
            $params_sql[':waybill_status'] = $post['search']['waybill_status'];
        }

        if (isset($post['search']['doc_number'])) {
            $where_all .= " AND doc_number = :doc_number";
            $params_sql[':doc_number'] = $post['search']['doc_number'];
        }

        if (isset($post['search']['waybill_date'])) {
            $where_all .= " AND waybill_date = :waybill_date";
            $params_sql[':waybill_date'] = $post['search']['waybill_date'];
        }

        if (isset($post['search']['waybill_date']) && !empty($post['search']['waybill_date'])) {
            if (isset($post['search']['waybill_date']['from']) && !empty($post['search']['waybill_date']['from'])) {
                $from = self::convertDate($post['search']['waybill_date']['from']);
            }

            if (isset($post['search']['waybill_date']['to']) && !empty($post['search']['waybill_date']['to'])) {
                $to = self::convertDate($post['search']['waybill_date']['to']);
            }

            if(isset($form) && isset($to)) {
                $where_all .= " AND waybill_date BETWEEN :waybill_date_from AND :waybill_date_to";
                $params_sql[':waybill_date_from'] = $from;
                $params_sql[':waybill_date_to'] = $to;
            }

        }

        $from = null;
        $to = null;
        if (isset($post['search']['order_date'])) {
            $where_all .= " AND order_date = :order_date";
            $params_sql[':order_date'] = $post['search']['order_date'];
        }

        if (isset($post['search']['order_date']) && !empty($post['search']['order_date'])) {
            if (isset($post['search']['order_date']['from']) && !empty($post['search']['order_date']['from'])) {
                $from = self::convertDate($post['search']['order_date']['from']);
            }

            if (isset($post['search']['order_date']['to']) && !empty($post['search']['order_date']['to'])) {
                $to = self::convertDate($post['search']['order_date']['to']);
            }

            if(isset($form) && isset($to)) {
                $where_all .= " AND order_date BETWEEN :order_date_from AND :order_date_to";
                $params_sql[':order_date_from'] = $from;
                $params_sql[':order_date_to'] = $to;
            }
        }


        if (isset($post['search']['vendor'])) {
            $where_all .= " AND vendor_id in (:vendors)";
            $vendors = implode("', '", $post['search']['vendor']);
            $params_sql[':vendors'] = "'" . $vendors . "'";
        }

        if (isset($post['search']['store'])) {
            $where_all .= " AND store_id in (:store)";
            $stories = implode(",", $post['search']['store']);
            $params_sql[':stories'] = $stories;
        }

        $sort_field = "";
        if ($sort) {
            $order = (preg_match('#^-(.+?)$#', $sort) ? SORT_DESC : SORT_ASC);
            $sort_field = str_replace('-', '', $sort);
            $where_all .= " AND $sort_field is not null ";
        }

        $params['client_id'] = $client->id;

        $apiShema = DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db_api->dsn);

        $sql = "
        select * from (
        SELECT * from (
            SELECT id, '" . self::TYPE_ORDER . "' as type, client_id, null as waybill_status, created_at as order_date, null as waybill_date, 
            null as waybill_number, id as doc_number, vendor_id as vendor, null as store 
            FROM `order`
            UNION ALL
            SELECT id, '" . self::TYPE_ORDER_EMAIL . "' as type, organization_id as client_id, null as waybill_status, date as order_date, null as waybill_date,
            null as waybill_number, number as doc_number, vendor_id as vendor, null as store   
            FROM .integration_invoice WHERE order_id is null
        ) as c
        UNION ALL
        SELECT id, '" . self::TYPE_WAYBILL . "' as type, acquirer_id as client_id, bill_status_id as waybill_status, null as order_date, doc_date as waybill_date, 
        outer_number_code as waybill_number, null as doc_number,  outer_contractor_uuid as vendor, outer_store_uuid as store   
        FROM `$apiShema`.waybill WHERE order_id is null ) as documents
        WHERE id is not null $where_all
       ";

        $query = \Yii::$app->db->createCommand($sql);

        $dataProvider = new SqlDataProvider([
            'sql' => $query->sql,
            'params' => $params_sql,
            'pagination' => [
                'page' => $page - 1,
                'pageSize' => $pageSize,
                /*'params' => [
                    'sort' => isset($params['sort']) ? $params['sort'] : 'product',
                ]*/
            ],
            'key' => 'id',
            'sort' => [
                'attributes' => [
                    'id',
                    'client_id',
                    'order_date',
                    'waybill_date',
                    'waybill_number',
                    'doc_number',
                ],
                /*'defaultOrder' => [
                    'product' => ,
                    // 'c_article_1' => SORT_ASC,
                    // 'c_article' => SORT_ASC
                ]*/
            ],
        ]);

        $dataProvider->sort->defaultOrder = [$sort_field => $order];

        $result = $dataProvider->getModels();
        foreach ($result as $model) {
            $modelClass = self::$models[$model['type']];
            $documents[] = $modelClass::prepareModel($model['id']);
        }

        $return = [
            'documents' => $documents,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ],
            'sort' => $sort_field
        ];

        return $return;
    }

    /**
     * Накладная - Сброс позиций
     * @param array $post
     * @return bool
     * @throws BadRequestHttpException
     */
    public function waybillResetPositions(array $post)
    {
        if (!isset($post['waybill_id'])) {
            throw new BadRequestHttpException("empty_param|waybill_id");
        }

        $waybill = Waybill::findOne(['id' => $post['waybill_id']]);

        if(!isset($waybill))
        {
            throw new BadRequestHttpException("Waybill not found");
        }

        if($waybill->bill_status_id == 3)
        {
            throw new BadRequestHttpException("Waybill in the state of \"reset\" or \"unloaded\"");
        }

        $waybill->resetPositions();
        return ['result' => true];
    }
      
     /**
     * Накладная - Детальная информация
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getWaybillDetail (array $post)
    {
        if (empty($post['waybill_id'])) {
            throw new BadRequestHttpException("empty_param|document_id");
        }

        return Waybill::prepareDetail($post['waybill_id']);
    }

    /**
     * Накладная - Обновление детальной информации
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function editWaybillDetail(array $post)
    {
        if (empty($post['id'])) {
            throw new BadRequestHttpException("EDIT CANCELED product id empty");
        }

        $waybill = Waybill::findOne(['id' => $post['id']]);

        if(!isset($waybill)) {
            throw new BadRequestHttpException("EDIT CANCELED the waybill - waybill not found");
        }

        if (!empty($post['agent_uid'])) {
            $waybill->outer_contractor_uuid = $post['agent_uid'];
        }

        if (!empty($post['store_uid'])) {
            $waybill->outer_store_uuid = $post['store_uid'];
        }

        if (!empty($post['doc_date'])) {
            $waybill->doc_date = date("Y-m-d H:i:s", strtotime($post['doc_date']));
        }

        if (!empty($post['outer_number_additional'])) {
            $waybill->outer_number_additional = $post['outer_number_additional'];
        }

        if (!empty($post['outer_number_code'])) {
            $waybill->outer_number_code = $post['outer_number_code'];
        }

        if (!empty($post['outer_note'])) {
            $waybill->outer_note = $post['outer_note'];
        }

        if ($waybill->validate() && $waybill->save()) {
            return $this->getWaybillDetail(['waybill_id' => $waybill->id]);
        } else {
            throw new ValidationException($waybill->getFirstErrors());
        }
    }

    private static function convertDate($date)
    {
        $result = \DateTime::createFromFormat('d.m.Y H:i:s', $date . " 00:00:00");
        if ($result) {
            return  $result->format('Y-m-d H:i:s');
        }
      
        return "";
    }

    /**
     * Накладная - Сопоставление с заказом
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */

    public function mapWaybillOrder (array $post)
    {
        if (empty($post['order_id'])) {
            throw new BadRequestHttpException("empty_param|order_id");
        }

        if (empty($post['document_id'])) {
            throw new BadRequestHttpException("empty_param|document_id");
        }

        $waybill = Waybill::findOne(['id' => $post['document_id']]);

        if (!isset($waybill)) {
            throw new BadRequestHttpException("waybill not found");
        }

        $waybill->mapWaybill($post['order_id']);
        return ['result' => true];
    }

    public function getDocumentStatus () {

        return self::$doc_group_status;
    }

    public function getWaybillStatus () {

        return self::$doc_waybill_status;
    }

}