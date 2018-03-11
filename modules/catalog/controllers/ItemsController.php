<?php
namespace yii\easyii\modules\catalog\controllers;

use Yii;
use yii\easyii\actions\ChangeStatusAction;
use yii\easyii\actions\ClearImageAction;
use yii\easyii\actions\DeleteAction;
use yii\easyii\actions\SortByDateAction;
use yii\easyii\behaviors\Fields;
use yii\easyii\components\Controller;
use yii\easyii\modules\catalog\CatalogModule;
use yii\easyii\modules\catalog\models\Category;
use yii\easyii\modules\catalog\models\Item;
use yii\widgets\ActiveForm;

use yii\easyii\actions\CopyAction;
use yii\helpers\ArrayHelper;

class ItemsController extends Controller
{
    public $modelClass = 'yii\easyii\modules\catalog\models\Item';
    public $categoryClass = 'yii\easyii\modules\catalog\models\Category';

    public function actions()
    {
        $className = Item::className();
        return [
            'delete' => [
                'class' => DeleteAction::className(),
                'model' => $className,
                'successMessage' => Yii::t('easyii/catalog', 'Item deleted')
            ],
            'clear-image' => ClearImageAction::className(),
            'up' => [
                'class' => SortByDateAction::className(),
                'addititonalEquality' => ['category_id']
            ],
            'down' => [
                'class' => SortByDateAction::className(),
                'addititonalEquality' => ['category_id']
            ],
            'on' => ChangeStatusAction::className(),
            'off' => ChangeStatusAction::className(),
            
            /* ADDED BY ROMAN DEVELOPER */
            'copy' => [
                'class' => CopyAction::className(),
                'successMessage' => Yii::t('easyii/catalog', 'Элемент скопирован')
            ],
            /* /ADDED BY ROMAN DEVELOPER */
        ];
    }

    public function behaviors()
    {
        return [
            'fields' => Fields::className()
        ];
    }

    public function actionIndex($id)
    {
        $request = Yii::$app->request;
        Yii::$app->session->set('memory_page', $request->url);
        
        $category = $this->findCategory($id);
        $items = $category->items;
        
        $memory_sort = $request->cookies->getValue('catalog_items_sort');
        $sort = ['id', 'ASC'];
        if($memory_sort){
            $sort = explode(',', $memory_sort);
            if((new Item())->hasAttribute($sort[0]) || $sort[0] == 'fulltitle'){
                ArrayHelper::multisort($items, $sort[0], (int)$sort[1]);
            }
        }
        return $this->render('index', [
            'category' => $category,
            'items' => $items,
            'sort' => $sort,
        ]);
    }
    
    
    /**
     * Устанавливает способ сортировки товаров в админке и редиректит
     * на предыдущую страницу или на начальную
     * 
     * @param string $field Поле, по которому сортировать
     * @param int $direction Направление сортировки
     * @return \yii\web\Response
     */
    public function actionSort($field = 'id', $direction = SORT_ASC){
        if($direction != SORT_ASC && $direction != SORT_DESC){
            $direction = SORT_ASC;
        }
        $cookies = Yii::$app->response->cookies;
        $cookies->add(new \yii\web\Cookie([
            'name' => 'catalog_items_sort',
            'value' => $field . ',' . $direction,
        ]));
        $url = Yii::$app->request->referrer;
        if(!$url){
            $session = Yii::$app->session;
            $session_key = 'memory_page';
            $url = $session->get($session_key);
            if(!$url){
                return $this->goBack();
            }
            $session->remove($session_key);
        }
        return $this->redirect($url);
    }
    
    
    public function actionCatalogField(){
        $post = Yii::$app->request->post();
        if(!Yii::$app->request->isAjax){
            return null;
        }
        if($post['type'] === 'categories'){
            $query = Category::find()->orderBy(['id' => SORT_ASC]);
            $view_name = '_catalogs_modal_grid';
        }
        elseif($post['type'] === 'items'){
            $query = Item::find()->where(['category_id' => $post['category_id']])->orderBy(['id' => SORT_ASC]);
            $view_name = '_items_modal_grid';
        }
        else{
            return null;
        }
        return $this->renderPartial($view_name, [
            'data_provider' => new \yii\data\ActiveDataProvider([
                'query' => $query,
                'sort' => false,
                'pagination' => [
                    'pageSize' => 0
                ]
            ])
        ]);
    }
    

    public function actionCreate($id)
    {
        $category = $this->findCategory($id);

        $model = new Item([
            'category_id' => $id,
            'time' => time(),
            'available' => 1
        ]);

        if ($model->load(Yii::$app->request->post())) {
            if(Yii::$app->request->isAjax){
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            else {
                $model->data = $this->parseData($model);

                if ($model->save()) {
                    $this->flash('success', Yii::t('easyii/catalog', 'Item created'));
                    return $this->redirect(['/admin/'.$this->module->id.'/items/edit/', 'id' => $model->primaryKey]);
                } else {
                    $this->flash('error', Yii::t('easyii', 'Create error. {0}', $model->formatErrors()));
                    return $this->refresh();
                }
            }
        }
        else {
            return $this->render('create', [
                'model' => $model,
                'category' => $category,
                'dataForm' => $this->generateForm($category->fields),
                'cats' => $this->getSameCats($category)
            ]);
        }
    }
    
    public function actionEdit($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if(Yii::$app->request->isAjax){
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            else {
                $model->data = $this->parseData($model);
                
                if ($model->save()) {
                    $this->flash('success', Yii::t('easyii/catalog', 'Item updated'));
                    return $this->redirect(['/admin/'.$this->module->id.'/items/edit', 'id' => $model->primaryKey]);
                } else {
                    $this->flash('error', Yii::t('easyii', 'Update error. {0}', $model->formatErrors()));
                    return $this->refresh();
                }
            }
        }
        else {
            return $this->render('edit', [
                'model' => $model,
                'dataForm' => $this->generateForm($model->category->fields, $model->data),
                'cats' => $this->getSameCats($model->category)
            ]);
        }
    }

    public function actionPhotos($id)
    {
        return $this->render('photos', [
            'model' => $this->findModel($id),
        ]);
    }

    public function getSameCats($cat)
    {
        $result = [];
        $fieldsHash = md5(json_encode($cat->fields));
        foreach(Category::cats() as $cat){
            if(md5(json_encode($cat->fields)) == $fieldsHash && (!count($cat->children) || CatalogModule::setting('itemsInFolder'))) {
                $result[$cat->id] = $cat->title;
            }
        }
        return $result;
    }
}