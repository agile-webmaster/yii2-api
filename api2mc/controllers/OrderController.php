<?php

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class OrderController
 * @package api_web\controllers
 */
class OrderController extends WebApiController
{
    /**
     * @SWG\Post(path="/order/info",
     *     tags={"Order"},
     *     summary="Информация о заказе",
     *     description="Полная информация о заказе",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"order_id":1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *         default={
     *              "id": 1,
     *              "total_price": 22,
     *              "invoice_relation": "",
     *              "created_at": "2016-09-28 15:22:20",
     *              "requested_delivery": "",
     *              "actual_delivery": "",
     *              "comment": "",
     *              "discount": 0,
     *              "completion_date": "",
     *              "order_code": 1,
     *              "currency": "RUB",
     *              "currency_id": 1,
     *              "status_id": 4,
     *              "status_text": "Завершен",
     *              "position_count": 2,
     *              "delivery_price": 0,
     *              "min_order_price": 3191,
     *              "total_price_without_discount": 22,
     *              "create_user": "User Name",
     *              "accept_user": "User Name",
     *              "items": {
     *                  {
     *                      "id": 2,
     *                      "product": "мясо",
     *                      "product_id": 9,
     *                      "catalog_id": 5,
     *                      "price": 3,
     *                      "quantity": 2.001,
     *                      "comment": "",
     *                      "total": 6,
     *                      "rating": 0,
     *                      "brand": "",
     *                      "article": "4545",
     *                      "ed": "",
     *                      "currency": "RUB",
     *                      "currency_id": 1,
     *                      "image": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAaQAAADhCAAAAACixZ6CAAAGCUlEQVRo3u3bWXabShRA0cx/hDQSnUQjRJMhvDzHjgpEdRSIYq1z"
     *                  }
     *              },
     *              "client": {
     *                  "id": 2,
     *                  "name": "j262@mail.ru",
     *                  "legal_entity": "",
     *                  "contact_name": "",
     *                  "phone": "",
     *                  "email": "",
     *                  "site": "",
     *                  "address": "",
     *                  "image": "https://s3-eu-west-1.amazonaws.com/static.<domain>.ru/restaurant-noavatar.gif",
     *                  "type_id": 1,
     *                  "type": "Ресторан",
     *                  "rating": 0,
     *                  "house": "",
     *                  "route": "",
     *                  "city": "",
     *                  "administrative_area_level_1": "",
     *                  "country": "",
     *                  "place_id": "",
     *                  "about": ""
     *              },
     *              "vendor": {
     *                  "id": 3,
     *                  "name": "bcpostavshik2@yandex.ru",
     *                  "legal_entity": "",
     *                  "contact_name": "",
     *                  "phone": "+7 (926) 499 18 89",
     *                  "email": "j262@mail.ru",
     *                  "site": "ww.ru",
     *                  "address": "Ломоносовчкий проспект 34 к 1",
     *                  "image": "https://s3-eu-west-1.amazonaws.com/static.<domain>.ru/vendor-noavatar.gif",
     *                  "type_id": 2,
     *                  "type": "Поставщик",
     *                  "rating": 0,
     *                  "house": "",
     *                  "route": "",
     *                  "city": "",
     *                  "administrative_area_level_1": "",
     *                  "country": "",
     *                  "place_id": "",
     *                  "about": "",
     *                  "allow_editing": 0
     *              }
     *          }
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
     */
    public function actionInfo()
    {
        $this->response = $this->container->get('OrderWebApi')->getInfo($this->request);
    }

    /**
     * @SWG\Post(path="/order/update",
     *     tags={"Order"},
     *     summary="Редактирование заказа",
     *     description="Редактирование заказа",
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
     *                      "order_id":1,
     *                      "comment": "Комментарий к заказу",
     *                      "discount": {
     *                          "type": "FIXED|PERCENT",
     *                          "amount": 100
     *                      },
     *                      "products": {
     *                          {"operation":"edit", "id":1, "price":200.2, "quantity":2, "comment":"Комментарий к товару!"},
     *                          {"operation":"edit", "id":2, "price":100.2},
     *                          {"operation":"add", "id":3, "quantity":2, "comment":"Комментарий к товару!"},
     *                          {"operation":"delete", "id":4}
     *                       }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *         default={
     *              "id": 1,
     *              "total_price": 22,
     *              "invoice_relation": "",
     *              "created_at": "2016-09-28 15:22:20",
     *              "requested_delivery": "",
     *              "actual_delivery": "",
     *              "comment": "",
     *              "discount": 0,
     *              "completion_date": "",
     *              "order_code": 1,
     *              "currency": "RUB",
     *              "currency_id": 1,
     *              "status_id": 4,
     *              "status_text": "Завершен",
     *              "position_count": 2,
     *              "delivery_price": 0,
     *              "min_order_price": 3191,
     *              "total_price_without_discount": 22,
     *              "items": {
     *                  {
     *                      "id": 2,
     *                      "product": "мясо",
     *                      "product_id": 9,
     *                      "catalog_id": 5,
     *                      "price": 3,
     *                      "quantity": 2.001,
     *                      "comment": "",
     *                      "total": 6,
     *                      "rating": 0,
     *                      "brand": "",
     *                      "article": "4545",
     *                      "ed": "",
     *                      "currency": "RUB",
     *                      "currency_id": 1,
     *                      "image": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAaQAAADhCAAAAACixZ6CAAAGCUlEQVRo3u3bWXabShRA0cx/hDQSnUQjRJMhvDzHjgpEdRSIYq1z"
     *                  }
     *              },
     *              "client": {
     *                  "id": 2,
     *                  "name": "j262@mail.ru",
     *                  "legal_entity": "",
     *                  "contact_name": "",
     *                  "phone": "",
     *                  "email": "",
     *                  "site": "",
     *                  "address": "",
     *                  "image": "https://s3-eu-west-1.amazonaws.com/static.<domain>.ru/restaurant-noavatar.gif",
     *                  "type_id": 1,
     *                  "type": "Ресторан",
     *                  "rating": 0,
     *                  "house": "",
     *                  "route": "",
     *                  "city": "",
     *                  "administrative_area_level_1": "",
     *                  "country": "",
     *                  "place_id": "",
     *                  "about": ""
     *              },
     *              "vendor": {
     *                  "id": 3,
     *                  "name": "bcpostavshik2@yandex.ru",
     *                  "legal_entity": "",
     *                  "contact_name": "",
     *                  "phone": "+7 (926) 499 18 89",
     *                  "email": "j262@mail.ru",
     *                  "site": "ww.ru",
     *                  "address": "Ломоносовчкий проспект 34 к 1",
     *                  "image": "https://s3-eu-west-1.amazonaws.com/static.<domain>.ru/vendor-noavatar.gif",
     *                  "type_id": 2,
     *                  "type": "Поставщик",
     *                  "rating": 0,
     *                  "house": "",
     *                  "route": "",
     *                  "city": "",
     *                  "administrative_area_level_1": "",
     *                  "country": "",
     *                  "place_id": "",
     *                  "about": "",
     *                  "allow_editing": 0
     *              }
     *          }
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
     */
    public function actionUpdate()
    {
        $this->response = $this->container->get('OrderWebApi')->update($this->request);
    }


    /**
     * @SWG\Post(path="/order/update-order-by-unconfirmed-vendor",
     *     tags={"Order/UnconfirmedVendorActions"},
     *     summary="Редактирование заказа вендором с неподтвержденным e-mail'ом",
     *     description="Редактирование заказа вендором с неподтвержденным e-mail'ом",
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
     *                      "order_id":1,
     *                      "comment": "Комментарий к заказу",
     *                      "discount": {
     *                          "type": "FIXED|PERCENT",
     *                          "amount": 100
     *                      },
     *                      "delivery_price": 100,
     *                      "actual_delivery": "2016-09-28 15:22:20",
     *                      "products": {
     *                          {"operation":"edit", "id":1, "price":200.2, "quantity":2, "comment":"Комментарий к товару!"},
     *                          {"operation":"edit", "id":2, "price":100.2},
     *                          {"operation":"add", "id":3, "quantity":2, "comment":"Комментарий к товару!"},
     *                          {"operation":"delete", "id":4}
     *                       }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *         default={
     *              "id": 1,
     *              "total_price": 22,
     *              "invoice_relation": "",
     *              "created_at": "2016-09-28 15:22:20",
     *              "requested_delivery": "",
     *              "actual_delivery": "",
     *              "comment": "",
     *              "discount": 0,
     *              "completion_date": "",
     *              "order_code": 1,
     *              "currency": "RUB",
     *              "currency_id": 1,
     *              "status_id": 4,
     *              "status_text": "Завершен",
     *              "position_count": 2,
     *              "delivery_price": 0,
     *              "min_order_price": 3191,
     *              "total_price_without_discount": 22,
     *              "items": {
     *                  {
     *                      "id": 2,
     *                      "product": "мясо",
     *                      "product_id": 9,
     *                      "catalog_id": 5,
     *                      "price": 3,
     *                      "quantity": 2.001,
     *                      "comment": "",
     *                      "total": 6,
     *                      "rating": 0,
     *                      "brand": "",
     *                      "article": "4545",
     *                      "ed": "",
     *                      "currency": "RUB",
     *                      "currency_id": 1,
     *                      "image": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAaQAAADhCAAAAACixZ6CAAAGCUlEQVRo3u3bWXabShRA0cx/hDQSnUQjRJMhvDzHjgpEdRSIYq1z"
     *                  }
     *              },
     *              "client": {
     *                  "id": 2,
     *                  "name": "j262@mail.ru",
     *                  "legal_entity": "",
     *                  "contact_name": "",
     *                  "phone": "",
     *                  "email": "",
     *                  "site": "",
     *                  "address": "",
     *                  "image": "https://s3-eu-west-1.amazonaws.com/static.<domain>.ru/restaurant-noavatar.gif",
     *                  "type_id": 1,
     *                  "type": "Ресторан",
     *                  "rating": 0,
     *                  "house": "",
     *                  "route": "",
     *                  "city": "",
     *                  "administrative_area_level_1": "",
     *                  "country": "",
     *                  "place_id": "",
     *                  "about": ""
     *              },
     *              "vendor": {
     *                  "id": 3,
     *                  "name": "bcpostavshik2@yandex.ru",
     *                  "legal_entity": "",
     *                  "contact_name": "",
     *                  "phone": "+7 (926) 499 18 89",
     *                  "email": "j262@mail.ru",
     *                  "site": "ww.ru",
     *                  "address": "Ломоносовчкий проспект 34 к 1",
     *                  "image": "https://s3-eu-west-1.amazonaws.com/static.<domain>.ru/vendor-noavatar.gif",
     *                  "type_id": 2,
     *                  "type": "Поставщик",
     *                  "rating": 0,
     *                  "house": "",
     *                  "route": "",
     *                  "city": "",
     *                  "administrative_area_level_1": "",
     *                  "country": "",
     *                  "place_id": "",
     *                  "about": "",
     *                  "allow_editing": 0
     *              }
     *          }
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
     */
    public function actionUpdateOrderByUnconfirmedVendor()
    {
        $this->response = $this->container->get('OrderWebApi')->update($this->request, true);
    }


    /**
     * @SWG\Post(path="/order/products",
     *     tags={"Order"},
     *     summary="Список товаров доступных для заказа",
     *     description="Получить список товаров достпных для заказа",
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
     *                                   "product":"искомая строка",
     *                                   "category_id": {24, 17},
     *                                   "supplier_id": {3803, 4},
     *                                   "price": {"from":100, "to":300},
     *                               },
     *                               "pagination":{
     *                                   "page":1,
     *                                   "page_size":12
     *                               },
     *                               "sort":"-product"
     *                           }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
     *      {
     *          "headers":{
     *                  "id": "ID",
     *                  "product": "Название"
     *          },
     *          "products":{
     *               {
     *                   "id": "5269",
     *                   "product": "Треска горячего копчения",
     *                   "article": "457",
     *                   "supplier": "ООО Рога и Копыта",
     *                   "supp_org_id": 4,
     *                   "cat_id": "3",
     *                   "category_id": 24,
     *                   "price": 499.80,
     *                   "ed": "шт.",
     *                   "currency": "RUB",
     *                   "image":"https://YYY.ru/ZZZ/images/image-category/51.jpg",
     *                   "in_basket": 0
     *               }
     *          },
     *          "pagination":{
     *              "page":1,
     *              "total_page":17,
     *              "page_size":12
     *          },
     *          "sort":"-product"
     *     }
     *            ),
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
     */
    public function actionProducts()
    {
        $this->response = $this->container->get('OrderWebApi')->products($this->request);
    }


    /**
     * @SWG\Post(path="/order/products-list-for-unconfirmed-vendor",
     *     tags={"Order/UnconfirmedVendorActions"},
     *     summary="Список товаров доступных для заказа у неподтвержденного вендора",
     *     description="Список товаров доступных для заказа у неподтвержденного вендора",
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
     *                                   "product":"искомая строка",
     *                                   "category_id": {24, 17},
     *                                   "order_id": 5757,
     *                                   "price": {"from":100, "to":300},
     *                               },
     *                               "pagination":{
     *                                   "page":1,
     *                                   "page_size":12
     *                               },
     *                               "sort":"-product"
     *                           }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
     *      {
     *          "headers":{
     *                  "id": "ID",
     *                  "product": "Название"
     *          },
     *          "products":{
     *               {
     *                   "id": "5269",
     *                   "product": "Треска горячего копчения",
     *                   "article": "457",
     *                   "supplier": "ООО Рога и Копыта",
     *                   "supp_org_id": 4,
     *                   "cat_id": "3",
     *                   "category_id": 24,
     *                   "price": 499.80,
     *                   "ed": "шт.",
     *                   "currency": "RUB",
     *                   "image":"https://YYY.ru/ZZZ/images/image-category/51.jpg",
     *                   "in_basket": 0
     *               }
     *          },
     *          "pagination":{
     *              "page":1,
     *              "total_page":17,
     *              "page_size":12
     *          },
     *          "sort":"-product"
     *     }
     *            ),
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
     */
    public function actionProductsListForUnconfirmedVendor()
    {
        $this->response = $this->container->get('OrderWebApi')->products($this->request, true);
    }


    /**
     * @SWG\Post(path="/order/categories",
     *     tags={"Order"},
     *     summary="Список категорий товаров",
     *     description="Получить список категорий товаров",
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
     *              default=
     *              {
     *                  {"id": 1,
     *                  "name": "МЯСО",
     *                  "image": "https://market.YYY.ru/ZZZ/images/image-category/1.jpg",
     *                  "subcategories": {
     *                      {
     *                          "id": 2,
     *                          "name": "Баранина",
     *                          "image": "https://market.YYY.ru/ZZZ/images/image-category/1.jpg"
     *                      }
     *                  }}
     *              }
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
     */
    public function actionCategories()
    {
        $this->response = $this->container->get('OrderWebApi')->categories($this->request);
    }


    /**
     * @SWG\Post(path="/order/categories-for-unconfirmed-vendor",
     *     tags={"Order/UnconfirmedVendorActions"},
     *     summary="Список категорий товаров для неподтвержденного вендора",
     *     description="Список категорий товаров для неподтвержденного вендора",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=false,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "order_id": 13574
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
     *              {
     *                  {"id": 1,
     *                  "name": "МЯСО",
     *                  "image": "https://market.YYY.ru/ZZZ/images/image-category/1.jpg",
     *                  "subcategories": {
     *                      {
     *                          "id": 2,
     *                          "name": "Баранина",
     *                          "image": "https://market.YYY.ru/ZZZ/images/image-category/1.jpg"
     *                      }
     *                  }}
     *              }
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
     */
    public function actionCategoriesForUnconfirmedVendor()
    {
        $this->response = $this->container->get('OrderWebApi')->categories($this->request, true);
    }


    /**
     * @SWG\Post(path="/order/comment",
     *     tags={"Order"},
     *     summary="Комментарий к заказу",
     *     description="Оставляем комментарий к заказу",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"order_id":1,"comment":"Тестовый комментарий"}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={"order_id":1, "comment":"Тестовый комментарий"}
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
     */
    public function actionComment()
    {
        $this->response = $this->container->get('OrderWebApi')->addComment($this->request);
    }

    /**
     * @SWG\Post(path="/order/product-comment",
     *     tags={"Order"},
     *     summary="Комментарий к продукту в заказе",
     *     description="Оставляем комментарий к конкретному продукту в заказе",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"order_id":1, "product_id":2, "comment":"Тестовый комментарий"}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={"order_id":1, "product_id":2, "comment":"Тестовый комментарий"}
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
     */
    public function actionProductComment()
    {
        $this->response = $this->container->get('OrderWebApi')->addProductComment($this->request);
    }

    /**
     * @SWG\Post(path="/order/cancel",
     *     tags={"Order"},
     *     summary="Отменить заказ",
     *     description="Отменить заказ",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"order_id":1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *         default={
     *              "id": 1,
     *              "total_price": 22,
     *              "invoice_relation": "",
     *              "created_at": "2016-09-28 15:22:20",
     *              "requested_delivery": "",
     *              "actual_delivery": "",
     *              "comment": "",
     *              "discount": 0,
     *              "completion_date": "",
     *              "order_code": 1,
     *              "currency": "RUB",
     *              "currency_id": 1,
     *              "status_id": 4,
     *              "status_text": "Завершен",
     *              "position_count": 2,
     *              "delivery_price": 0,
     *              "min_order_price": 3191,
     *              "total_price_without_discount": 22,
     *              "items": {
     *                  {
     *                      "id": 2,
     *                      "product": "мясо",
     *                      "product_id": 9,
     *                      "catalog_id": 5,
     *                      "price": 3,
     *                      "quantity": 2.001,
     *                      "comment": "",
     *                      "total": 6,
     *                      "rating": 0,
     *                      "brand": "",
     *                      "article": "4545",
     *                      "ed": "",
     *                      "currency": "RUB",
     *                      "currency_id": 1,
     *                      "image": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAaQAAADhCAAAAACixZ6CAAAGCUlEQVRo3u3bWXabShRA0cx"
     *                  }
     *              },
     *              "client": {
     *                  "id": 2,
     *                  "name": "j262@mail.ru",
     *                  "legal_entity": "",
     *                  "contact_name": "",
     *                  "phone": "",
     *                  "email": "",
     *                  "site": "",
     *                  "address": "",
     *                  "image": "https://s3-eu-west-1.amazonaws.com/static.<domain>.ru/restaurant-noavatar.gif",
     *                  "type_id": 1,
     *                  "type": "Ресторан",
     *                  "rating": 0,
     *                  "house": "",
     *                  "route": "",
     *                  "city": "",
     *                  "administrative_area_level_1": "",
     *                  "country": "",
     *                  "place_id": "",
     *                  "about": ""
     *              },
     *              "vendor": {
     *                  "id": 3,
     *                  "name": "bcpostavshik2@yandex.ru",
     *                  "legal_entity": "",
     *                  "contact_name": "",
     *                  "phone": "+7 (926) 499 18 89",
     *                  "email": "j262@mail.ru",
     *                  "site": "ww.ru",
     *                  "address": "Ломоносовчкий проспект 34 к 1",
     *                  "image": "https://s3-eu-west-1.amazonaws.com/static.<domain>.ru/vendor-noavatar.gif",
     *                  "type_id": 2,
     *                  "type": "Поставщик",
     *                  "rating": 0,
     *                  "house": "",
     *                  "route": "",
     *                  "city": "",
     *                  "administrative_area_level_1": "",
     *                  "country": "",
     *                  "place_id": "",
     *                  "about": "",
     *                  "allow_editing": 0
     *              }
     *          }
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
     */
    public function actionCancel()
    {
        $this->response = $this->container->get('OrderWebApi')->cancel($this->request);
    }


    /**
     * @SWG\Post(path="/order/cancel-order-by-unconfirmed-vendor",
     *     tags={"Order/UnconfirmedVendorActions"},
     *     summary="Отменить заказ вендором с неподтвержденным емейлом",
     *     description="Отменить заказ вендором с неподтвержденным емейлом",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"order_id":1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *         default={
     *              "id": 1,
     *              "total_price": 22,
     *              "invoice_relation": "",
     *              "created_at": "2016-09-28 15:22:20",
     *              "requested_delivery": "",
     *              "actual_delivery": "",
     *              "comment": "",
     *              "discount": 0,
     *              "completion_date": "",
     *              "order_code": 1,
     *              "currency": "RUB",
     *              "currency_id": 1,
     *              "status_id": 4,
     *              "status_text": "Завершен",
     *              "position_count": 2,
     *              "delivery_price": 0,
     *              "min_order_price": 3191,
     *              "total_price_without_discount": 22,
     *              "items": {
     *                  {
     *                      "id": 2,
     *                      "product": "мясо",
     *                      "product_id": 9,
     *                      "catalog_id": 5,
     *                      "price": 3,
     *                      "quantity": 2.001,
     *                      "comment": "",
     *                      "total": 6,
     *                      "rating": 0,
     *                      "brand": "",
     *                      "article": "4545",
     *                      "ed": "",
     *                      "currency": "RUB",
     *                      "currency_id": 1,
     *                      "image": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAaQAAADh"
     *                  }
     *              },
     *              "client": {
     *                  "id": 2,
     *                  "name": "j262@mail.ru",
     *                  "legal_entity": "",
     *                  "contact_name": "",
     *                  "phone": "",
     *                  "email": "",
     *                  "site": "",
     *                  "address": "",
     *                  "image": "https://s3-eu-west-1.amazonaws.com/static.<domain>.ru/restaurant-noavatar.gif",
     *                  "type_id": 1,
     *                  "type": "Ресторан",
     *                  "rating": 0,
     *                  "house": "",
     *                  "route": "",
     *                  "city": "",
     *                  "administrative_area_level_1": "",
     *                  "country": "",
     *                  "place_id": "",
     *                  "about": ""
     *              },
     *              "vendor": {
     *                  "id": 3,
     *                  "name": "bcpostavshik2@yandex.ru",
     *                  "legal_entity": "",
     *                  "contact_name": "",
     *                  "phone": "+7 (926) 499 18 89",
     *                  "email": "j262@mail.ru",
     *                  "site": "ww.ru",
     *                  "address": "Ломоносовчкий проспект 34 к 1",
     *                  "image": "https://s3-eu-west-1.amazonaws.com/static.<domain>.ru/vendor-noavatar.gif",
     *                  "type_id": 2,
     *                  "type": "Поставщик",
     *                  "rating": 0,
     *                  "house": "",
     *                  "route": "",
     *                  "city": "",
     *                  "administrative_area_level_1": "",
     *                  "country": "",
     *                  "place_id": "",
     *                  "about": "",
     *                  "allow_editing": 0
     *              }
     *          }
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
     */
    public function actionCancelOrderByUnconfirmedVendor()
    {
        $this->response = $this->container->get('OrderWebApi')->cancel($this->request, true);
    }


    /**
     * @SWG\Post(path="/order/repeat",
     *     tags={"Order"},
     *     summary="Повторить заказ",
     *     description="Повторить заказ",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"order_id":1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/CartItems")
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
     */
    public function actionRepeat()
    {
        $this->response = $this->container->get('OrderWebApi')->repeat($this->request);
    }

    /**
     * @SWG\Post(path="/order/complete",
     *     tags={"Order"},
     *     summary="Завершить заказ",
     *     description="Завершить заказ",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"order_id":1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                   "id": 6618,
     *                   "total_price": 510.00,
     *                   "created_at": "2017-04-02 12:33:44",
     *                   "requested_delivery": "2017-04-02 19:00:00",
     *                   "actual_delivery": "2017-04-04 12:34:21",
     *                   "comment": "",
     *                   "discount": 0.00,
     *                   "completion_date": null,
     *                   "currency": "RUB",
     *                   "currency_id": 1,
     *                   "status_text": "Завершен",
     *                   "position_count": 1,
     *                   "delivery_price": 0,
     *                   "min_order_price": 0,
     *                   "total_price_without_discount": 510,
     *                   "items": {
     *                      {
     *                         "id": 18204,
     *                         "product": "post1@post.ru",
     *                         "product_id": 481059,
     *                         "catalog_id": 3026,
     *                         "price": 100,
     *                         "quantity": 5,
     *                         "comment": "",
     *                         "total": 510,
     *                         "rating": 0,
     *                         "brand": "",
     *                         "article": "1",
     *                         "ed": "in",
     *                         "currency": "RUB",
     *                         "currency_id": 1,
     *                         "image": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAaQAAADhCA"
     *                      }
     *                   },
     *                   "client": {
     *                      "id": 1,
     *                      "name": "Космическая пятница",
     *                      "phone": "",
     *                      "email": "investor@<domain>.ru",
     *                      "site": "",
     *                      "address": "Бакалейная ул., 50А, Казань, Респ. Татарстан, Россия, 420095",
     *                      "image": "https://XXX.s3.amazonaws.com/org-picture/20d9d738e5498f36654cda93a071622e.jpg",
     *                      "type_id": 1,
     *                      "type": "Ресторан",
     *                      "rating": 0,
     *                      "city": "Казань",
     *                      "administrative_area_level_1": "Республика Татарстан",
     *                      "country": "Россия",
     *                      "about": ""
     *                   },
     *                   "vendor": {
     *                      "id": 3950,
     *                      "name": "post1@post.ru",
     *                      "phone": "",
     *                      "email": null,
     *                      "site": "",
     *                      "address": "",
     *                      "image": "https://s3-eu-west-1.amazonaws.com/static.<domain>.ru/vendor-noavatar.gif",
     *                      "type_id": 2,
     *                      "type": "Поставщик",
     *                      "rating": 0,
     *                      "city": null,
     *                      "administrative_area_level_1": null,
     *                      "country": null,
     *                      "about": "",
     *                      "allow_editing": 1
     *                   }
     *     }
     *         ),
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
     */
    public function actionComplete()
    {
        $this->response = $this->container->get('OrderWebApi')->complete($this->request);
    }

    /**
     * @SWG\Post(path="/order/history",
     *     tags={"Order"},
     *     summary="История заказов",
     *     description="Список заказов текущего пользователя",
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
     *                                   "vendor": {1,2},
     *                                   "status": {1,2,3},
     *                                   "create_date": {
     *                                      "start":"d.m.Y",
     *                                      "end":"d.m.Y"
     *                                   },
     *                                  "completion_date":{
     *                                      "start":"d.m.Y",
     *                                      "end":"d.m.Y"
     *                                   }
     *                               },
     *                               "pagination":{
     *                                   "page":1,
     *                                   "page_size":12
     *                               },
     *                               "sort":"id"
     *                           }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *          response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/History"),
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
     */
    public function actionHistory()
    {
        $this->response = $this->container->get('OrderWebApi')->getHistory($this->request);
    }

    /**
     * @SWG\Post(path="/order/history-count",
     *     tags={"Order"},
     *     summary="История заказов в цифрах",
     *     description="История заказов в цифрах",
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
     *          response = 200,
     *          description = "success",
     *          @SWG\Schema(
     *              default={
     *                   "waiting": 61,
     *                   "processing": 3,
     *                   "success": 21,
     *                   "canceled": 12
     *              }
     *          )
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
     */
    public function actionHistoryCount()
    {
        $this->response = $this->container->get('OrderWebApi')->getHistoryCount($this->request);
    }

    /**
     * @SWG\Post(path="/order/status-list",
     *     tags={"Order"},
     *     summary="Список статусов заказа",
     *     description="Список статусов заказа",
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
     *              default=
     *              {
     *                 {
     *                      "id": 1,
     *                      "title": "Ожидает подтверждения поставщика"
     *                 },
     *                 {
     *                      "id": 2,
     *                      "title": "Ожидает подтверждения клиента"
     *                 }
     *              }
     *         ),
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
     */
    public function actionStatusList()
    {
        $result = [];
        foreach ((new \common\models\Order)->getStatusList() as $key => $value) {
            $result[] = ['id' => (int)$key, 'title' => $value];
        }
        $this->response = $result;
    }

    /**
     * @SWG\Post(path="/order/save-to-pdf",
     *     tags={"Order"},
     *     summary="Сохранить заказ в PDF",
     *     description="Сохранить заказ в PDF",
     *     produces={"application/json", "application/pdf"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"order_id":1, "base64_encode":1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description="Если все прошло хорошо вернет файл закодированый в base64",
     *         @SWG\Schema(
     *              default="JVBERi0xLjQKJeLjz9MKMyAwIG9iago8PC9UeXBlIC9QYWdlCi9QYXJlbnQgMSAwIFIKL01lZGlhQm94IFswIDAgNTk1LjI4MCA4NDEuOD"
     *         )
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
     */
    public function actionSaveToPdf()
    {
        $result = $this->container->get('OrderWebApi')->saveToPdf($this->request, $this);
        if (is_array($result)) {
            $this->response = $result;
        } else {
            header('Access-Control-Allow-Origin:*');
            header('Access-Control-Allow-Methods:GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers:Content-Type, Authorization');
            exit($result);
        }
    }


    /**
     * @SWG\Post(path="/order/create-waybill",
     *     tags={"Order/Integration"},
     *     summary="Создание накладной к заказу или в конкретном сервисе у.с",
     *     description="Создание накладной к заказу или в конкретном сервисе у.с",
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
     *                              "order_id": 1,
     *                              "service_id": 1
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                "success": true,
     *                "waybill_id": 1,
     *              }
     *          )
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
     */
    public function actionCreateWaybill()
    {
        $this->response = $this->container->get('IntegrationWebApi')->handleWaybill($this->request);
    }


    /**
     * @SWG\Post(path="/order/reset-waybill-content",
     *     tags={"Order/Integration"},
     *     summary="Сброс данных позиции, на значения из заказа",
     *     description="Сброс данных позиции, на значения из заказа",
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
     *                              "waybill_content_id": 14822
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                "success": true
     *              }
     *          )
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
     */
    public function actionResetWaybillContent()
    {
        $this->response = $this->container->get('IntegrationWebApi')->resetWaybillContent($this->request);
    }


    /**
     * @SWG\Post(path="/order/show-waybill-content",
     *     tags={"Order/Integration"},
     *     summary="Позиция накладной - Детальная информация",
     *     description="Позиция накладной - Детальная информация",
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
     *                              "waybill_content_id": 14822
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                          "id": 1,
     *                           "waybill_id": 11,
     *                           "order_content_id": 14822,
     *                           "product_outer_id": 4822,
     *                           "quantity_waybill": 1,
     *                           "vat_waybill": 0,
     *                           "merc_uuid": "745663-6454-4657-234775",
     *                           "unload_status": 1,
     *                           "sum_with_vat": 333299999,
     *                           "sum_without_vat": 333299999,
     *                           "price_with_vat": 333299999,
     *                           "price_without_vat": 333299999,
     *                           "koef": 1,
     *                           "serviceproduct_id": 777,
     *                           "store_rid": 111,
     *                           "outer_product_name": "Редиска",
     *                           "outer_product_id": 555,
     *                           "product_id_equality": true,
     *                           "outer_store_name": "Склад 1",
     *                           "outer_store_id": 222,
     *                           "store_id_equality": true,
     *                           "outer_unit_name": "кг",
     *                           "outer_unit_id": 333
     *                       }
     *          )
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
     */
    public function actionShowWaybillContent()
    {
        $this->response = $this->container->get('IntegrationWebApi')->showWaybillContent($this->request);
    }


    /**
     * @SWG\Post(path="/order/update-waybill-content",
     *     tags={"Order/Integration"},
     *     summary="Накладные - Обновление детальной информации позиции накладной",
     *     description="Накладные - Обновление детальной информации позиции накладной",
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
     *                              "waybill_content_id": 5,
     *                              "koef": 1.55,
     *                              "quantity_waybill": 1,
     *                              "product_outer_id": 4822,
     *                              "price_without_vat": 35000,
     *                              "vat_waybill": 0.18
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                "success": true
     *              }
     *          )
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
     */
    public function actionUpdateWaybillContent()
    {
        $this->response = $this->container->get('IntegrationWebApi')->updateWaybillContent($this->request);
    }


    /**
     * @SWG\Post(path="/order/create-waybill-content",
     *     tags={"Order/Integration"},
     *     summary="Накладная (привязана к заказу) - Добавление позиции",
     *     description="Накладная (привязана к заказу) - Добавление позиции",
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
     *                              "waybill_id": 5,
     *                              "product_outer_id": 4352,
     *                              "outer_unit_id": 8,
     *                              "quantity_waybill": 1,
     *                              "product_outer_id": 4822,
     *                              "price_without_vat": 35000,
     *                              "vat_waybill": 0.18
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                "success": true,
     *                "waybill_content_id": 5
     *              }
     *          )
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
     */
    public function actionCreateWaybillContent()
    {
        $this->response = $this->container->get('IntegrationWebApi')->createWaybillContent($this->request);
    }


    /**
     * @SWG\Post(path="/order/delete-waybill-content",
     *     tags={"Order/Integration"},
     *     summary="Накладная - Удалить/Убрать позицию",
     *     description="Накладная - Удалить/Убрать позицию",
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
     *                              "waybill_content_id": 5
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                "success": true
     *              }
     *          )
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
     */
    public function actionDeleteWaybillContent()
    {
        $this->response = $this->container->get('IntegrationWebApi')->deleteWaybillContent($this->request);
    }
}