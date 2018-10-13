<?php
/**
 * Created by PhpStorm.
 * Date: 8/29/2017
 * Time: 1:11 PM
 */

namespace api_web\helpers;

use api_web\exceptions\ValidationException;
use common\helpers\DBNameHelper;
use common\models\Order;
use common\models\OrderContent;
use common\models\OuterAgent;
use common\models\OuterStore;
use common\models\Waybill;
use common\models\WaybillContent;
use yii\web\BadRequestHttpException;

/**
 * Waybills class for generate\update\delete\ actions
 * */
class WaybillHelper
{
    /**@var int const for mercuriy service id in all_service table */
    const MERC_SERVICE_ID = 4;
    /**@var int const for EDI service id in all_service table */
    const EDI_SERVICE_ID = 6;
    const WAYBILL_COMPARED = 'compared';
    const WAYBILL_FORMED = 'formed';
    const WAYBILL_ERROR = 'error';
    const WAYBILL_RESET = 'reset';
    const WAYBILL_UNLOADED = 'unloaded';
    const WAYBILL_UNLOADING = 'unloading';

    /**@var array $statuses */
    static $statuses = [
        self::WAYBILL_COMPARED  => 1,
        self::WAYBILL_FORMED    => 2,
        self::WAYBILL_ERROR     => 3,
        self::WAYBILL_RESET     => 4,
        self::WAYBILL_UNLOADED  => 5,
        self::WAYBILL_UNLOADING => 6,
    ];

    /**
     * Create waybill and waybill_content and binding VSD
     *
     * @param string $uuid VSD uuid
     * @return boolean
     * */
    public function createWaybillFromVsd($uuid)
    {
        $transaction = \Yii::$app->db_api->beginTransaction();
        $orgId = (\Yii::$app->user->identity)->organization_id;
        $modelWaybill = new Waybill();
        $modelWaybill->acquirer_id = $orgId;
        $modelWaybill->service_id = self::MERC_SERVICE_ID;

        $modelWaybillContent = new WaybillContent();
        $modelWaybillContent->merc_uuid = $uuid;
        try {
            $modelWaybill->save();
            $modelWaybillContent->waybill_id = $modelWaybill->id;
            $modelWaybillContent->save();
            $transaction->commit();
        } catch (\Throwable $t) {
            $transaction->rollBack();
            \Yii::error($t->getMessage(), __METHOD__);
            return false;
        }

        return true;
    }


    /**
     * @param      $order_id
     * @param null $arOrderContentForCreate
     * @param null $supplierOrgId
     * @throws \Exception
     * @return array|bool
     */
    public function createWaybill($order_id, $arOrderContentForCreate = null, $supplierOrgId = null): array
    {
        $order = Order::findOne($order_id);
        if (!$order){
            throw new BadRequestHttpException('Not found order with id' . $order_id);
        }
        if (is_null($arOrderContentForCreate)) {
            $arOrderContentForCreate = $order->orderContent;
        }
        $settingsAuto = true;
        if ($settingsAuto) {
            $waybillContents = WaybillContent::find()->andWhere(['order_content_id' => array_keys
            ($order->orderContent)])->indexBy('order_content_id')->all();
            $notInWaybillContent = array_diff_key($arOrderContentForCreate, $waybillContents);

            if ($notInWaybillContent) {
                $defaultAgent = OuterAgent::findOne(['vendor_id' => $supplierOrgId, 'org_id' => $order->client_id]);
                if ($defaultAgent && $defaultAgent->store_id) {
                    $waybillId = $this->createWaybillAndContent($notInWaybillContent, $order->client_id,
                        $defaultAgent->store_id, $defaultAgent->service_id);
                    return [$waybillId];

                }

                $hasDefaultStore = 1234;
                $hasDefaultServiceID = 1234;
                if ($hasDefaultStore) {
                    $waybillId = $this->createWaybillAndContent($notInWaybillContent, $order->client_id,
                        $hasDefaultStore, $hasDefaultServiceID);
                    return [$waybillId];
                }
                $waybillIds = [];
                $integrations = ['iiko' => 2];
                foreach ($integrations as $integration) {
                    $dbName = DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db_api->dsn);
                    $stories = OrderContent::find()->select([
                        'm.store_rid as store_id',
                        'GROUP_CONCAT(order_content.product_id) as prd_ids'])
                        ->leftJoin('`' . $dbName . '`.all_map m', 'order_content.product_id = m.product_id AND m.service_id = ' . $integration . ' AND m.org_id = ' . $order->client_id)
                        ->where(['order_content.order_id' => $order->id])
                        ->andWhere(['not', ['m.store_rid' => null]])
                        ->groupBy('m.store_rid')->indexBy('m.store_rid')->all();
                    $orderContForStore = [];
                    if (empty($waybillContents)) {
                        if (!empty($stories)) {
                            foreach ($stories as $store) {
                                $store_uuid = (OuterStore::findOne($store['store_rid']))->outer_uid;
                                $prods = explode(',', $store['prd_ids']);

                                foreach ($prods as $prod) {
                                    /**@var OrderContent $ordCont */
                                    foreach ($notInWaybillContent as $ordCont) {
                                        if ($ordCont->product_id == $prod) {
                                            $orderContForStore[$ordCont->id] = $ordCont;
                                        }
                                    }
                                }
                                $waybillIds[] = $this->createWaybillAndContent($orderContForStore, $order->client_id,
                                    $store_uuid, $store_uuid);
                            }
                        }
                    }
                }
                $notInWaybillContent = array_diff_key($notInWaybillContent, $orderContForStore);
                if (!empty($notInWaybillContent)) {
                    $waybillId = $this->createWaybillAndContent($notInWaybillContent, $order->client_id);
                    $waybillIds[] = $waybillId;
                    return $waybillIds;
                }
            }
        }
        return false;
    }

    /**
     * @param int $orgId
     * @return \common\models\Waybill
     */
    private function buildWaybill($orgId)
    {
        $model = new Waybill();
        $model->acquirer_id = $orgId;
        $model->service_id = WaybillHelper::EDI_SERVICE_ID;
        $model->bill_status_id = self::$statuses[self::WAYBILL_FORMED]; //TODO: bill_status_id ???
        $model->readytoexport = 0;
        $model->is_deleted = 0;
        $datetime = new \DateTime();
        $model->doc_date = $datetime->format('Y-m-d H:i:s');
        $model->created_at = $datetime->format('Y-m-d H:i:s');
        $model->exported_at = $datetime->format('Y-m-d H:i:s');

        return $model;
    }

    /**
     * @param      $orderContent
     * @param      $orgId
     * @param null $outerStoreUuid
     * @param null $serviceId
     * @return bool|int
     */
    private function createWaybillAndContent($orderContent, $orgId, $outerStoreUuid = null, $serviceId = null)
    {
        $model = $this->buildWaybill($orgId);
        $model->outer_store_uuid = $outerStoreUuid;
        $model->service_id = $serviceId;
        $tmp_ed_num = reset($orderContent)->edi_number;
        $existWaybill = Waybill::find()->where(['like', 'edi_number', $tmp_ed_num])->orderBy(['edi_number' => 'desc'])->limit(1);
        if ($existWaybill) {
            if (strpos('-', $existWaybill->edi_number)) {
                $ed_num = explode('-', $existWaybill->edi_number);
                $ed_num[1] = (int)$ed_num[1] + 1;
                $ed_num = implode('-', $ed_num);
            } else {
                $ed_num = $existWaybill->edi_number . '-1';
            }
        } else {
            $ed_num = $tmp_ed_num;
        }
        $model->edi_number = $ed_num;
        if (!$model->save()) {
            \yii::error('Error during saving waybill' . print_r($model->getErrors(), true));
            return false;
        }

        foreach ($orderContent as $ordCont) {
            $price = $ordCont->price;
            $quantity = $ordCont->quantity;
            $taxRate = $ordCont->vat_product;
            $priceWithVat = (float)($price + ($price * ($taxRate / 100)));

            $modelWaybillContent = new WaybillContent();
            $modelWaybillContent->order_content_id = $ordCont->id;
            $modelWaybillContent->waybill_id = $model->id;
            $modelWaybillContent->merc_uuid = $ordCont->merc_uuid;
            $modelWaybillContent->product_outer_id = $ordCont->product_id;
            $modelWaybillContent->quantity_waybill = $quantity;
            $modelWaybillContent->vat_waybill = $taxRate;
            $modelWaybillContent->sum_with_vat = $quantity * $priceWithVat;
            $modelWaybillContent->sum_without_vat = $quantity * $price;
            $modelWaybillContent->price_with_vat = $priceWithVat;
            $modelWaybillContent->price_without_vat = $price;
            $modelWaybillContent->save();
        }
        return $model->id;
    }

    /**
     * Check if exist row with $uuid
     *
     * @param string $uuid
     * @return boolean
     * */
    public function checkWaybillForVsdUuid($uuid)
    {
        return WaybillContent::find()->where(['merc_uuid' => $uuid])->exists();
    }

    /**
     * @param $request
     * @return array
     * @throws \Exception
     */
    public function createWaybillForApi($request){
        if (empty($request['order_id'])) {
            throw new BadRequestHttpException('empty_param|order_id');
        }
        $result = $this->createWaybill($request['order_id']);

        return [
            'result' => $result
        ];
    }

    /**
     * @param $request
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function moveOrderContentToWaybill($request){
        if (!isset($request['waybill_id']) && !isset($request['order_content_id'])){
            throw new BadRequestHttpException('empty_param|waybill_id|order_content_id');
        }
        $waybill = Waybill::findOne([
            'id' => $request['waybill_id'],
            'bill_status_id' => [
                self::$statuses[self::WAYBILL_COMPARED],
                self::$statuses[self::WAYBILL_ERROR],
                self::$statuses[self::WAYBILL_FORMED],
            ]]);
        if (!$waybill){
            throw new BadRequestHttpException('waybill cannot adding waybill_content with id ' . $request['waybill_id']);
        }
        $orderContent = OrderContent::findOne($request['order_content_id']);
        if (!$orderContent){
            throw new BadRequestHttpException('OrderContent dont exists with id ' . $request['order_content_id']);
        }
        $taxRate = $orderContent->vat_product ?? null;
        $quantity = $orderContent->quantity;
        $price = $orderContent->price;
        if ($taxRate){
            $priceWithVat = $price + ($price * ($taxRate / 100));
        }

        try {
            $waybillContent = new WaybillContent();
            $waybillContent->waybill_id = $request['waybill_id'];
            $waybillContent->order_content_id = $orderContent->id;
            $waybillContent->product_outer_id = $orderContent->product_id;
            $waybillContent->quantity_waybill = (float)$quantity;
            $waybillContent->vat_waybill = $taxRate;
            $waybillContent->merc_uuid = $orderContent->merc_uuid;
            $waybillContent->sum_with_vat = (int)(isset($priceWithVat) ? $priceWithVat * $quantity * 100 : null);
            $waybillContent->sum_without_vat = (int)($price * $quantity * 100);
            $waybillContent->price_with_vat = (int)(isset($priceWithVat) ? $priceWithVat * 100 : null);
            $waybillContent->price_without_vat = (int)($price * 100);
            $waybillContent->save();
            if (!$waybillContent->validate() || !$waybillContent->save()) {
                throw new ValidationException($waybillContent->getErrorSummary(true));
            }
        } catch (\Throwable $t){
            \Yii::error($t->getMessage());
            return ['result' => $t->getMessage()];
        }

        return ['result' => true];
    }
}