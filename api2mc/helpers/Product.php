<?php

namespace api_web\helpers;


use api_web\components\WebApi;
use common\models\Catalog;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use common\models\RelationSuppRest;

class Product extends WebApi
{
    /**
     * @param $id
     * @param array $catalogs
     * @return array
     */
    public function findFromCatalogs($id, $catalogs = [])
    {
        if (empty($catalogs)) {
            $catalogs = explode(',', $this->user->organization->getCatalogs());
        }

        $model = CatalogBaseGoods::findOne(['id' => $id]);

        $individualModel = CatalogGoods::find()->where(['base_goods_id' => $id])
            ->andWhere(['IN', 'cat_id', $catalogs])
            ->one();

        return $this->prepareProduct($model, $individualModel);
    }

    /**
     * @param $id
     * @param $vendor_id
     * @param $client_id
     * @return array
     */
    public function findFromVendor($id, $vendor_id, $client_id)
    {
        if (empty($client_id)) {
            $client_id = $this->user->organization->id;
        }

        $model = CatalogBaseGoods::findOne(['id' => $id]);

        $relation = RelationSuppRest::findOne(['rest_org_id' => $client_id, 'supp_org_id' => $vendor_id]);
        if (!empty($relation) && $relation->cat_id != 0) {
            $individualModel = CatalogGoods::find()->where(['cat_id' => $relation->cat_id, 'base_goods_id' => $id])->one();
        }

        return $this->prepareProduct($model, $individualModel ?? null);
    }

    /**
     * @param CatalogBaseGoods $baseModel
     * @param CatalogGoods $individualModel
     * @return array
     */
    private function prepareProduct(CatalogBaseGoods $baseModel, CatalogGoods $individualModel = null)
    {
        $product = $baseModel->getAttributes();
        $currency = $baseModel->catalog->currency;
        $product['currency_id'] = $currency->id;
        $product['currency'] = $currency->symbol;

        if (!empty($individualModel)) {
            $product['price'] = $individualModel->price;
            $product['discount'] = $individualModel->discount;
            $product['discount_percent'] = $individualModel->discount_percent;
            $product['discount_fixed'] = $individualModel->discount_fixed;
            $product['cat_id'] = $individualModel->cat_id;
            $product['currency_id'] = $individualModel->catalog->currency_id;
            $product['currency'] = $individualModel->catalog->currency->symbol;
        }

        if (strstr($product['image'], 'data:image') !== false) {
            $product['image'] = \Yii::$app->params['web'] . 'site/image-base?id=' . $product['id'] . '&type=product';
        }

        $c = Catalog::find()->cache(3600)->where(['id' => $product['cat_id']])->one();
        $product['vendor_id'] = $c->supp_org_id;
        $product['model'] = $baseModel;

        return $product;
    }

}