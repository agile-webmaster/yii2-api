<?php

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class RequestController
 * @package api_web\controllers
 */
class RequestController extends WebApiController
{
    /**
     * @SWG\Post(path="/request/get",
     *     tags={"Request"},
     *     summary="Карточка заявки",
     *     description="Карточка заявки",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"request_id": 1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                       "id": 74,
     *                       "name": "цукен",
     *                       "status": 1,
     *                       "created_at": "30.06.2017 14:17:34",
     *                       "category": "Мебель",
     *                       "category_id": 231,
     *                       "client": {
     *                       "id": 1,
     *                           "name": "Космическая пятница",
     *                           "phone": "",
     *                           "email": "investor@<domain>.ru",
     *                           "address": "Бакалейная ул., 50А, Казань, Респ. Татарстан, Россия, 420095",
     *                           "image": "https://XXX.s3.amazonaws.com/org-picture/8f060fc32d84198ec60212d7595191a0.jpg",
     *                           "type_id": 1,
     *                           "type": "Ресторан",
     *                           "rating": 0,
     *                           "city": "Казань",
     *                           "administrative_area_level_1": "Республика Татарстан",
     *                           "country": "Россия",
     *                           "about": ""
     *                       },
     *                       "vendor": {
     *                           "id": 4,
     *                           "name": "ООО Рога и Копыта",
     *                           "phone": "",
     *                           "email": "",
     *                           "address": "ул. Госпитальный Вал, Москва, Россия",
     *                           "image": "https://XXX.s3.amazonaws.com/org-picture/c49766f11fe1908675cb4c2808126ee8.jpg",
     *                           "type_id": 2,
     *                           "type": "Поставщик",
     *                           "rating": 3.7,
     *                          "city": "Москва",
     *                           "administrative_area_level_1": null,
     *                           "country": "Россия",
     *                           "about": ""
     *                       },
     *                       "hits": 0,
     *                       "count_callback": 2,
     *                       "urgent": 1
     *     })),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     */
    public function actionGet()
    {
        $this->response = $this->container->get('RequestWebApi')->getRequest($this->request);
    }

    /**
     * @SWG\Post(path="/request/list-client",
     *     tags={"Request"},
     *     summary="Список заявок",
     *     description="Список заявок",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "search":{
     *                                   "status": 1,
     *                                   "name": "продукт"
     *                               },
     *                               "pagination":{
     *                                   "page":1,
     *                                   "page_size":12
     *                               }
     *                           }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={"result":{
     *          {
     *                       "id": 74,
     *                       "name": "цукен",
     *                       "status": 1,
     *                       "created_at": "30.06.2017 14:17:34",
     *                       "category": "Мебель",
     *                       "category_id": 231,
     *                       "client": {
     *                       "id": 1,
     *                           "name": "Космическая пятница",
     *                           "phone": "",
     *                           "email": "investor@<domain>.ru",
     *                           "address": "Бакалейная ул., 50А, Казань, Респ. Татарстан, Россия, 420095",
     *                           "image": "https://XXX.s3.amazonaws.com/org-picture/8f060fc32d84198ec60212d7595191a0.jpg",
     *                           "type_id": 1,
     *                           "type": "Ресторан",
     *                           "rating": 0,
     *                           "city": "Казань",
     *                           "administrative_area_level_1": "Республика Татарстан",
     *                           "country": "Россия",
     *                           "about": ""
     *                       },
     *                       "vendor": {
     *                           "id": 4,
     *                           "name": "ООО Рога и Копыта",
     *                           "phone": "",
     *                           "email": "",
     *                           "address": "ул. Госпитальный Вал, Москва, Россия",
     *                           "image": "https://XXX.s3.amazonaws.com/org-picture/c49766f11fe1908675cb4c2808126ee8.jpg",
     *                           "type_id": 2,
     *                           "type": "Поставщик",
     *                           "rating": 3.7,
     *                          "city": "Москва",
     *                           "administrative_area_level_1": null,
     *                           "country": "Россия",
     *                           "about": ""
     *                       },
     *                       "hits": 0,
     *                       "count_callback": 2,
     *                       "urgent": 1
     *           }
     *     },
     *              "pagination": {
     *                   "page": 1,
     *                   "page_size": 12,
     *                   "total_page": 1
     *               }
     *          }),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     */
    public function actionListClient()
    {
        $this->response = $this->container->get('RequestWebApi')->getListClient($this->request);
    }

    /**
     * @SWG\Post(path="/request/list-vendor",
     *     tags={"Request"},
     *     summary="Список заявок для поставщика",
     *     description="Список заявок для поставщика",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "my_only": true,
     *                               "search":{
     *                                   "urgent": true,
     *                                   "product": "продукт",
     *                                   "category": 2,
     *                               },
     *                               "pagination":{
     *                                   "page":1,
     *                                   "page_size":12
     *                               }
     *                           }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={"result":{
     *          {
     *                       "id": 74,
     *                       "name": "цукен",
     *                       "status": 1,
     *                       "created_at": "30.06.2017 14:17:34",
     *                       "category": "Мебель",
     *                       "category_id": 231,
     *                       "client": {
     *                       "id": 1,
     *                           "name": "Космическая пятница",
     *                           "phone": "",
     *                           "email": "investor@<domain>.ru",
     *                           "address": "Бакалейная ул., 50А, Казань, Респ. Татарстан, Россия, 420095",
     *                           "image": "https://XXX.s3.amazonaws.com/org-picture/8f060fc32d84198ec60212d7595191a0.jpg",
     *                           "type_id": 1,
     *                           "type": "Ресторан",
     *                           "rating": 0,
     *                           "city": "Казань",
     *                           "administrative_area_level_1": "Республика Татарстан",
     *                           "country": "Россия",
     *                           "about": ""
     *                       },
     *                       "vendor": {
     *                           "id": 4,
     *                           "name": "ООО Рога и Копыта",
     *                           "phone": "",
     *                           "email": "",
     *                           "address": "ул. Госпитальный Вал, Москва, Россия",
     *                           "image": "https://XXX.s3.amazonaws.com/org-picture/c49766f11fe1908675cb4c2808126ee8.jpg",
     *                           "type_id": 2,
     *                           "type": "Поставщик",
     *                           "rating": 3.7,
     *                          "city": "Москва",
     *                           "administrative_area_level_1": null,
     *                           "country": "Россия",
     *                           "about": ""
     *                       },
     *                       "hits": 0,
     *                       "count_callback": 2,
     *                       "urgent": 1
     *           }
     *     },
     *              "pagination": {
     *                   "page": 1,
     *                   "page_size": 12,
     *                   "total_page": 1
     *               }
     *          }),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     */
    public function actionListVendor()
    {
        $this->response = $this->container->get('RequestWebApi')->getListVendor($this->request);
    }

    /**
     * @SWG\Post(path="/request/category-list",
     *     tags={"Request"},
     *     summary="Список категорий",
     *     description="Список категорий",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={{"id": 74, "name": "цукен"}}
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     */
    public function actionCategoryList()
    {
        $this->response = $this->container->get('RequestWebApi')->getCategoryList();
    }

    /**
     * @SWG\Post(path="/request/create",
     *     tags={"Request"},
     *     summary="Создание заявки",
     *     description="Создание заявки",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="
    regular
    1 - Разово (default)
    2 - Ежедневно
    3 - Каждую неделю
    4 - Каждый месяц

    payment_type
    1- Наличные (default)
    2- безнал",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                  "category_id": 74,
     *                  "product": "Огурцы",
     *                  "comment": "Комментарий",
     *                  "regular": 1,
     *                  "amount": "10 кг",
     *                  "urgent": true,
     *                  "payment_type": 1,
     *                  "deferment_payment": "Отсрочка нужна 7 дней"
     *              }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                        "id": 282,
     *                        "name": "Огурцы",
     *                        "status": 1,
     *                        "created_at": "30.06.2017 14:17:34",
     *                        "category": "Овощи и зелень",
     *                        "category_id": 83,
     *                        "amount": "10 кг",
     *                        "comment": "Комментарий",
     *                        "client": {
     *                              "id": 1,
     *                              "name": "Космическая пятница",
     *                              "phone": "",
     *                              "email": "investor@<domain>.ru",
     *                              "address": "Бакалейная улица, 50А, Казань, город Казань, Республика Татарстан, Россия",
     *                              "image": "https://XXX.s3.amazonaws.com/org-picture/8f060fc32d84198ec60212d7595191a0.jpg",
     *                              "type_id": 1,
     *                              "type": "Ресторан",
     *                              "rating": 0,
     *                              "city": "Казань",
     *                              "administrative_area_level_1": "Республика Татарстан",
     *                              "country": "Россия",
     *                              "about": ""
     *                        },
     *                        "vendor": {
     *                              "id": 1,
     *                              "name": "Космическая пятница",
     *                              "phone": "",
     *                              "email": "investor@<domain>.ru",
     *                              "address": "Бакалейная улица, 50А, Казань, город Казань, Республика Татарстан, Россия",
     *                              "image": "https://XXX.s3.amazonaws.com/org-picture/8f060fc32d84198ec60212d7595191a0.jpg",
     *                              "type_id": 1,
     *                              "type": "Ресторан",
     *                              "rating": 0,
     *                              "city": "Казань",
     *                              "administrative_area_level_1": "Республика Татарстан",
     *                              "country": "Россия",
     *                              "about": ""
     *                        },
     *                        "hits": 0,
     *                        "count_callback": 0,
     *                        "urgent": 1,
     *                        "payment_method": 1,
     *                        "deferment_payment": "Отсрочка нужна 7 дней",
     *                        "regular": "1",
     *                        "regular_name": "Разово"
     *                    }
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     */
    public function actionCreate()
    {
        $this->response = $this->container->get('RequestWebApi')->create($this->request);
    }

    /**
     * @SWG\Post(path="/request/close",
     *     tags={"Request"},
     *     summary="Снятие заявки",
     *     description="Снятие заявки",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"request_id": 74}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                        "id": 282,
     *                        "name": "Огурцы",
     *                        "status": 0,
     *                        "created_at": "30.06.2017 14:17:34",
     *                        "category": "Овощи и зелень",
     *                        "category_id": 83,
     *                        "amount": "10 кг",
     *                        "comment": "Комментарий",
     *                        "client": {
     *                              "id": 1,
     *                              "name": "Космическая пятница",
     *                              "phone": "",
     *                              "email": "investor@<domain>.ru",
     *                              "address": "Бакалейная улица, 50А, Казань, город Казань, Республика Татарстан, Россия",
     *                              "image": "https://XXX.s3.amazonaws.com/org-picture/8f060fc32d84198ec60212d7595191a0.jpg",
     *                              "type_id": 1,
     *                              "type": "Ресторан",
     *                              "rating": 0,
     *                              "city": "Казань",
     *                              "administrative_area_level_1": "Республика Татарстан",
     *                              "country": "Россия",
     *                              "about": ""
     *                        },
     *                        "vendor": {
     *                              "id": 1,
     *                              "name": "Космическая пятница",
     *                              "phone": "",
     *                              "email": "investor@<domain>.ru",
     *                              "address": "Бакалейная улица, 50А, Казань, город Казань, Республика Татарстан, Россия",
     *                              "image": "https://XXX.s3.amazonaws.com/org-picture/8f060fc32d84198ec60212d7595191a0.jpg",
     *                              "type_id": 1,
     *                              "type": "Ресторан",
     *                              "rating": 0,
     *                              "city": "Казань",
     *                              "administrative_area_level_1": "Республика Татарстан",
     *                              "country": "Россия",
     *                              "about": ""
     *                        },
     *                        "hits": 0,
     *                        "count_callback": 0,
     *                        "urgent": 1,
     *                        "payment_method": 1,
     *                        "deferment_payment": "Отсрочка нужна 7 дней",
     *                        "regular": "1",
     *                        "regular_name": "Разово"
     *                    }
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     */
    public function actionClose()
    {
        $this->response = $this->container->get('RequestWebApi')->close($this->request);
    }

    /**
     * @SWG\Post(path="/request/add-callback",
     *     tags={"Request"},
     *     summary="Оставить предложение для ресторана",
     *     description="Оставить предложение для ресторана от поставщика",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"request_id": 74, "price": 1000, "comment":"Комментарий"}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={}
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     */
    public function actionAddCallback()
    {
        $this->response = $this->container->get('RequestWebApi')->addCallback($this->request);
    }

    /**
     * @SWG\Post(path="/request/callback-list",
     *     tags={"Request"},
     *     summary="Список предложений по заявке",
     *     description="Список предложений по заявке ресторана",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "request_id": 279,
     *                               "pagination":{
     *                                   "page":1,
     *                                   "page_size":12
     *                               }
     *                           }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={}
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     */
    public function actionCallbackList()
    {
        $this->response = $this->container->get('RequestWebApi')->getCallbackList($this->request);
    }

    /**
     * @SWG\Post(path="/request/set-contractor",
     *     tags={"Request"},
     *     summary="Назначить исполнителя заявки",
     *     description="Назначить исполнителя заявки",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "request_id": 279,
     *                               "callback_id": 1
     *                           }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                       "id": 74,
     *                       "name": "цукен",
     *                       "status": 1,
     *                        "created_at": "30.06.2017 14:17:34",
     *                       "category": "Мебель",
     *                       "category_id": 231,
     *                       "client": {
     *                       "id": 1,
     *                           "name": "Космическая пятница",
     *                           "phone": "",
     *                           "email": "investor@<domain>.ru",
     *                           "address": "Бакалейная ул., 50А, Казань, Респ. Татарстан, Россия, 420095",
     *                           "image": "https://XXX.s3.amazonaws.com/org-picture/8f060fc32d84198ec60212d7595191a0.jpg",
     *                           "type_id": 1,
     *                           "type": "Ресторан",
     *                           "rating": 0,
     *                           "city": "Казань",
     *                           "administrative_area_level_1": "Республика Татарстан",
     *                           "country": "Россия",
     *                           "about": ""
     *                       },
     *                       "vendor": {
     *                           "id": 4,
     *                           "name": "ООО Рога и Копыта",
     *                           "phone": "",
     *                           "email": "",
     *                           "address": "ул. Госпитальный Вал, Москва, Россия",
     *                           "image": "https://XXX.s3.amazonaws.com/org-picture/c49766f11fe1908675cb4c2808126ee8.jpg",
     *                           "type_id": 2,
     *                           "type": "Поставщик",
     *                           "rating": 3.7,
     *                          "city": "Москва",
     *                           "administrative_area_level_1": null,
     *                           "country": "Россия",
     *                           "about": ""
     *                       },
     *                       "hits": 0,
     *                       "count_callback": 2,
     *                       "urgent": 1
     *     })),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     */
    public function actionSetContractor()
    {
        $this->response = $this->container->get('RequestWebApi')->setContractor($this->request);
    }

    /**
     * @SWG\Post(path="/request/unset-contractor",
     *     tags={"Request"},
     *     summary="Снять исполнителя с заявки",
     *     description="Снять исполнителя с заявки",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "request_id": 279
     *                           }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                       "id": 74,
     *                       "name": "цукен",
     *                       "status": 1,
     *                        "created_at": "30.06.2017 14:17:34",
     *                       "category": "Мебель",
     *                       "category_id": 231,
     *                       "client": {
     *                       "id": 1,
     *                           "name": "Космическая пятница",
     *                           "phone": "",
     *                           "email": "investor@<domain>.ru",
     *                           "address": "Бакалейная ул., 50А, Казань, Респ. Татарстан, Россия, 420095",
     *                           "image": "https://XXX.s3.amazonaws.com/org-picture/8f060fc32d84198ec60212d7595191a0.jpg",
     *                           "type_id": 1,
     *                           "type": "Ресторан",
     *                           "rating": 0,
     *                           "city": "Казань",
     *                           "administrative_area_level_1": "Республика Татарстан",
     *                           "country": "Россия",
     *                           "about": ""
     *                       },
     *                       "vendor": {
     *                           "id": 4,
     *                           "name": "ООО Рога и Копыта",
     *                           "phone": "",
     *                           "email": "",
     *                           "address": "ул. Госпитальный Вал, Москва, Россия",
     *                           "image": "https://XXX.s3.amazonaws.com/org-picture/c49766f11fe1908675cb4c2808126ee8.jpg",
     *                           "type_id": 2,
     *                           "type": "Поставщик",
     *                           "rating": 3.7,
     *                          "city": "Москва",
     *                           "administrative_area_level_1": null,
     *                           "country": "Россия",
     *                           "about": ""
     *                       },
     *                       "hits": 0,
     *                       "count_callback": 2,
     *                       "urgent": 1
     *     })),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     */
    public function actionUnsetContractor()
    {
        $this->response = $this->container->get('RequestWebApi')->unsetContractor($this->request);
    }
}