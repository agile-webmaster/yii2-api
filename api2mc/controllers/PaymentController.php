<?php

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class PaymentController
 * @package api_web\controllers
 */
class PaymentController extends WebApiController
{
    /**
     * @SWG\Post(path="/payment/currency-list",
     *     tags={"Payment"},
     *     summary="Список доступных валют",
     *     description="Получить полный список возможным валют",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/UserNoAuth"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object"
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={{"id": 1, "iso_code": "RUB"},{"id": 2, "iso_code": "USD"},{"id": 3, "iso_code": "EUR"}}
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     */
    public function actionCurrencyList()
    {
        $this->response = $this->container->get('PaymentWebApi')->currencyList();
    }

    /**
     * @SWG\Post(path="/payment/tarif",
     *     tags={"Payment"},
     *     summary="Тариф",
     *     description="Получить информацию о следующем платеже",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object"
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={"payment": {"type": 1, "title": "Подключение","price":2490}}
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     */
    public function actionTarif()
    {
        $this->response = $this->container->get('PaymentWebApi')->getTarif();
    }

    /**
     * @SWG\Post(path="/payment/create",
     *     tags={"Payment"},
     *     summary="Создание оплаты",
     *     description="Создать платеж, и получить ссылку для оплаты у провайдера",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="Тестовая карта
     *     Номер карты: 1111111111111026
     *     Действует до: 12  |  25
     *     CVC: 000",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                              "amount": 2490.50,
     *                              "currency": "RUB",
     *                              "payment_type_id": 1,
     *                              "return_url": "http://YYY.ru/"
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *            default={"redirect_url": "http://kassa.provider/payment/id/1123123123"}
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     */
    public function actionCreate()
    {
        $this->response['redirect_url'] = $this->container->get('PaymentWebApi')->create($this->request);
    }
}
