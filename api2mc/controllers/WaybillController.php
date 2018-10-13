<?php
/**
 * Created by PhpStorm.
 * Date: 04.10.2017
 * Time: 11:56
 */

namespace api_web\controllers;


use api_web\components\WebApiController;
use api_web\helpers\WaybillHelper;

class WaybillController extends WebApiController
{
    /**
     * @SWG\Post(path="/waybill/create-by-order",
     *     tags={"Waybill"},
     *     summary="Создание накладной по заказу",
     *     description="Создание накладной по заказу",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "order_id": 3674
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                      "result": true
     *                  }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws \Exception
     */
    public function actionCreateByOrder()
    {
        $this->response = (new WaybillHelper())->createWaybillForApi($this->request);
    }

    /**
     * @SWG\Post(path="/waybill/move-order-content-to-waybill",
     *     tags={"Waybill"},
     *     summary="Привязка order content к waybill content",
     *     description="Привязка order content к waybill content",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "waybill_id": 5,
     *                      "order_content_id": 123
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                      "result": true
     *                  }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     * @throws \Exception
     */
    public function actionMoveOrderContentToWaybill()
    {
        $this->response = (new WaybillHelper())->moveOrderContentToWaybill($this->request);
    }


}