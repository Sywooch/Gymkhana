<?php

namespace admin\controllers\competitions;

use dosamigos\editable\EditableAction;
use Yii;
use common\models\AthletesClass;
use common\models\search\AthletesClassSearch;
use admin\controllers\BaseController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ClassesController implements the CRUD actions for AthletesClass model.
 */
class ClassesController extends BaseController
{
	public function init()
	{
		parent::init();
		$this->can('globalWorkWithCompetitions');
	}
	
	public function actions()
	{
		return [
			'update' => [
				'class'       => EditableAction::className(),
				'modelClass'  => AthletesClass::className(),
				'forceCreate' => false
			]
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'verbs' => [
				'class'   => VerbFilter::className(),
				'actions' => [
					'delete' => ['POST'],
				],
			],
		];
	}
	
	/**
	 * Lists all AthletesClass models.
	 *
	 * @return mixed
	 */
	public function actionIndex()
	{
		$searchModel = new AthletesClassSearch();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
		
		return $this->render('index', [
			'searchModel'  => $searchModel,
			'dataProvider' => $dataProvider,
		]);
	}
	
	/**
	 * Creates a new AthletesClass model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 *
	 * @return mixed
	 */
	public function actionCreate()
	{
		$model = new AthletesClass();
		
		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['index']);
		} else {
			return $this->render('create', [
				'model' => $model,
			]);
		}
	}
	
	/**
	 * Deletes an existing AthletesClass model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 */
	public function actionDelete($id)
	{
		$this->findModel($id)->delete();
		
		return $this->redirect(['index']);
	}
	
	/**
	 * Finds the AthletesClass model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param integer $id
	 *
	 * @return AthletesClass the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		if (($model = AthletesClass::findOne($id)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}
	
	public function actionChangeStatus($id, $status)
	{
		$class = AthletesClass::findOne($id);
		if (!$class) {
			return 'Класс не найден';
		}
		
		$class->status = $status;
		if (!$class->save()) {
			return 'Возникла ошибка при сохранении изменений';
		}
		
		return true;
	}
}
