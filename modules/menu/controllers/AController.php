<?php
namespace yii\easyii\modules\menu\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\easyii\actions\DeleteAction;
use yii\easyii\modules\menu\models\Menu;
use yii\widgets\ActiveForm;
use yii\easyii\components\Controller;
use yii\easyii\actions\ChangeStatusAction;

class AController extends Controller
{
    public $modelClass = 'yii\easyii\modules\menu\models\Menu';
    public $rootActions = ['create', 'delete'];

    public function actions()
    {
        return [
            'delete' => [
                'class' => DeleteAction::className(),
                'successMessage' => Yii::t('easyii/menu', 'Menu deleted')
            ],
            'on' => ChangeStatusAction::className(),
            'off' => ChangeStatusAction::className(),
        ];
    }

    public function actionIndex()
    {
        $data = new ActiveDataProvider([
            'query' => Menu::find()
        ]);
        return $this->render('index', [
            'data' => $data
        ]);
    }

    public function actionCreate($slug = null)
    {
        $model = new Menu;

        if ($model->load(Yii::$app->request->post())) {
            if(Yii::$app->request->isAjax){
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            else{
                if($model->save()){
                    $this->flash('success', Yii::t('easyii/menu', 'Menu created'));
                    return $this->redirect(['/admin/'.$this->module->id]);
                }
                else{
                    $this->flash('error', Yii::t('easyii', 'Create error. {0}', $model->formatErrors()));
                    return $this->refresh();
                }
            }
        }
        else {
            if($slug) $model->slug = $slug;

            return $this->render('create', [
                'model' => $model
            ]);
        }
    }

    public function actionEdit($id)
    {
        $model = $this->findModel($id);

        //Сохранение информации о меню
        if ($model->load(Yii::$app->request->post())) {
            if(Yii::$app->request->isAjax){
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            else{
                if($model->save()){
                    $this->flash('success', Yii::t('easyii/menu', 'Menu updated'));
                }
                else{
                    $this->flash('error', Yii::t('easyii', 'Update error. {0}', $model->formatErrors()));
                }
                return $this->refresh();
            }
        }

        //Сохранение элементов меню
        if (Yii::$app->request->isAjax) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            switch (true) {
                case Yii::$app->request->isGet :
                    return ['success' => true, 'menu' => $model->items];
                case Yii::$app->request->post('update'):
                    $model->items = Yii::$app->request->post('menu');
                    return $model->save() ? ['success' => true] : ['success' => false];
                default:
                    return ['success' => false];
            }
        }
        else {
            return $this->render('edit', [
                'model' => $model
            ]);
        }
    }
}